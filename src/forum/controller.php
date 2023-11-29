<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\forum;

use bravedave\dvc\{
	fileUploader,
	json,
	logger,
	Response
};
use currentUser, green, dvc\idea, RuntimeException;

class controller extends \Controller {

	protected $ItemsPerPage = 20;
	protected $showOnlyMine = false;
	protected $hideDead = false;
	protected $includeComplete = false;
	protected $showClosed = false;
	protected $comments = false;

	protected function _index() {

		$this->showOnlyMine = currentUser::option('forum-showOnlyMine') == 'yes';
		$this->hideDead = currentUser::option('forum-hidedead') == 'yes';
		$this->includeComplete = currentUser::option('forum-complete') == 'yes';
		$this->showClosed = currentUser::option('forum-closed') == 'yes';

		$about = sprintf(
			'%s complete%s%s%s',
			($this->includeComplete ? 'including' : 'excluding'),
			($this->showClosed ? ', showing closed items' : ''),
			($this->hideDead ? ', hiding dead items' : ''),
			($this->showOnlyMine ? ', only my topics' : '')
		);

		$dao = new dao\forum;

		$page = (int)$this->getParam('page');
		if ($page < 1) $page = 1;
		$offset = ((int)$page - 1) * $this->ItemsPerPage;
		if ($dataset = $dao->getTopLevel($this->showClosed, $this->includeComplete, $this->hideDead, $this->showOnlyMine, $offset, $this->ItemsPerPage)) {

			$content = [
				'parameters',
				'report'
			];

			$this->data = (object)[
				'dataset' => $dataset,
				'pageUrl' => strings::url($this->route),
				'searchFocus' => true,
				'title' => $this->title = sprintf('forum - %s', $about),
			];

			$this->renderBS5([
				'aside' => [],
				'main' => fn () => array_walk($content, fn ($p) => $this->load($p))
			]);
		} else {

			$this->data = (object)[
				'title' => $this->title = sprintf('forum - %s', $about),
				'pageUrl' => strings::url($this->route),
				'searchFocus' => true,
			];

			$this->renderBS5([
				'aside' => [],
				'main' => fn () => $this->load('blank')
			]);
		}
	}

	protected function before() {
		config::forum_checkdatabase();
		idea\config::idea_checkdatabase();

		if ($ipp = (int)currentUser::option('forum-items-per-page')) {
			$this->ItemsPerPage = $ipp;
		}

		parent::before();
		$this->viewPath[] = __DIR__ . '/views/';
	}

	protected function postHandler() {
		$action = $this->getPost('action');

		if ('save-tag' == $action) {
			if ($id = (int)$this->getPost('id')) {
				$a = ['tag' => $this->getPost('tag')];
				$dao = new dao\forum;
				$dao->UpdateByID($a, $id);

				json::ack(sprintf('%s : %s', $action, $a['tag']));
			} else {
				json::nak($action);
			}
		} elseif ('add-link' == $action || 'remove-link' == $action) {
			if ($id = (int)$this->getPost('id')) {
				if ($linkID = (int)$this->getPost('link')) {
					$dao = new dao\forum;
					if ('add-link' == $action) {
						if ($dao->link($id, $linkID)) {
							json::ack($action);
						} else {
							json::nak($action);
						}
					} elseif ('remove-link' == $action) {
						if ($dao->linkRemove($id, $linkID)) {
							json::ack($action);
						} else {
							json::nak($action);
						}
					} else {
						json::nak($action);
					}
				} else {
					json::nak($action);
				}
			} else {
				json::nak($action);
			}
		} elseif ('get-links' == $action) {
			if ($id = (int)$this->getPost('id')) {
				$dao = new dao\forum;
				if ($dto = $dao->getByID($id)) {
					if ($dto->link) {
						if ($res = $this->db->Result(sprintf('SELECT id, description FROM forum WHERE id IN (%s)', $dto->link))) {
							json::ack($action)->add('data', $res->dtoSet());
						}
					} else {
						json::ack($action)->add('data', []);
					}
				} else {
					json::nak($action);
				}
			} else {
				json::nak($action);
			}
		} elseif ('get-active-users' == $action) {
			$dao = new green\users\dao\users;
			json::ack($action)
				->add('data', $dao->getActive());
		} elseif ('get-attachments' == $action) {

			$id = (int)$this->getPost('id');
			if ($id > 0) {

				$dao = new dao\forum;
				if ($dto = $dao->getById($id)) {

					if ($store = $dao->store($dto->id)) {

						// use filesystemiterator to get the files
						$files = [];
						$fi = new \FilesystemIterator($store, \FilesystemIterator::SKIP_DOTS);
						foreach ($fi as $file) {
							$files[] = [
								'file' => $file->getFilename(),
								'size' => $file->getSize(),
								'updated' => $file->getMTime()
							];
						}

						json::ack($action)
							->add('data', $files);
					} else {

						json::nak('store not found - ' . $action);
					}
				} else {

					json::nak('forum topic not found - ' . $action);
				}
			} else {

				json::nak('missing id - ' . $action);
			}
		} elseif ('idea-search' == $action) {
			$ret = [];
			if ($qry = $this->getParam('term')) {
				$where = [
					sprintf('idea like "%%%s%%"', $this->db->escape($qry))
				];
				$limit = 10;

				$sql = sprintf(
					'SELECT
						id,
						idea value,
						updated
					FROM forum_idea
					WHERE %s
					ORDER BY updated DESC
					LIMIT %d',
					implode(' AND ', $where),
					$limit
				);
				if ($res = $this->db->Result($sql)) $ret = $res->dtoSet();
			}

			new Json($ret);
		} elseif ('idea-set' == $action) {
			if ($id = (int)$this->getPost('id')) {
				if ($forum_idea_id = (int)$this->getPost('forum_idea_id')) {
					$dao = new dao\forum;
					$dao->UpdateByID([
						'forum_idea_id' => $forum_idea_id
					], $id);

					json::ack($action);
				} else {
					json::nak($action);
				}
			} else {
				json::nak($action);
			}
		} elseif ('mark-complete' == $action || 'mark-incomplete' == $action) {
			if ($id = (int)$this->getPost('id')) {
				$dao = new dao\forum;
				if ('mark-complete' == $action) {
					if ($dao->markComplete($id)) {
						$status = 0;	// open
						if ($dto = $dao->getById($id)) {
							if ($dto->complete)
								$status = 2;	// complete
							elseif ($dto->closed)
								$status = 3;	// closed
							elseif (strtotime($dao->last_updated($dto)) > strtotime($dto->updated))
								$status = 1;	// open and responded

						}
						/*--------------------------------------------------------------------------------------------*/

						json::ack($action)
							->add('complete', 'yes')
							->add('status', $status);
					} else {
						json::nak(sprintf('%s - failed to mark', $action));
					}
				} elseif ('mark-incomplete' == $action) {
					if ($dto = $dao->markInComplete($id)) {
						$status = 0;	// open
						if ($dto = $dao->getById($id)) {
							if ($dto->complete)
								$status = 2;	// complete
							elseif ($dto->closed)
								$status = 3;	// closed
							elseif (strtotime($dao->last_updated($dto)) > strtotime($dto->updated))
								$status = 1;	// open and responded

						}
						/*--------------------------------------------------------------------------------------------*/

						json::ack($action)
							->add('complete', 'no')
							->add('status', $status);
					} else {
						json::nak(sprintf('%s - failed to mark', $action));
					}
				} else {
					json::nak($action);
				}
			} else {
				json::nak($action);
			}
		} elseif ('notify' == $action) {
			/*
				this isn't used, it just to test the mailer
				( _ => _.post({
						url : _.url('forum', true),
						data : {
							action : 'notify',
							id : 3
						},
					}).then( _.growl)
				)( _brayworth_ )

			*/
			if ($id = (int)$this->getPost('id')) {
				$dao = new dao\forum;
				if ($dto = $dao->getByID($id)) {
					$dao->notify(
						sprintf('%s : %s', ($dto->parent > 0 ? 'Follow Up' : 'New Topic'), $dto->description),
						currentUser::email(),
						($dto->parent > 0 ? $dto->parent : $id)
					);

					json::ack($action);
				} else {
					json::nak($action);
				}
			} else {
				json::nak($action);
			}
		} elseif ('post' == $action) {
			$dto = new dao\dto\forum;
			$dto->comment = $this->getPost('comment');
			$dto->description = $this->getPost('description');
			if (empty($dto->description) || empty($dto->comment)) {
				Response::redirect($this->route, 'not adding topic with empty description or comment');
			} else {
				$dao = new dao\forum;
				if ($dao->InsertDTO($dto)) {
					Response::redirect($this->route, 'added new topic');
				} else {
					throw new RuntimeException('failed to add new topic');
				}
			}
		} elseif ('post-new' == $action) {
			$dto = new dao\dto\forum;
			$dto->description = $this->getPost('description');
			$dto->comment = $this->getPost('comment');
			$dto->tag = $this->getPost('tag');
			$dto->priority = (int)$this->getPost('priority');
			$dto->forum_idea_id = (int)$this->getPost('forum_idea_id');

			if ($dto->description && $dto->comment) {
				$notifyList = (array)$this->getPost('notify', \config::$SUPPORT_EMAIL);
				//~ sys::logger( implode( ',', $notifyList));
				//~ die;

				$dao = new dao\forum;
				$id = $dao->InsertDTO($dto, $notifyList);
				if ($link = $this->getPost('link')) {
					$dao->link($id, $link);
				}

				json::ack($action);
			} else {
				json::nak($action);
			}
		} elseif ('post-update' == $action) {
			if ($id = $this->getPost('id')) {
				$a = [
					'description' => (string)$this->getPost('description'),
					'comment' => (string)$this->getPost('comment'),
				];
				$dao = new dao\forum;
				$dao->UpdateByID($a, $id);
				json::ack($action);
			} else {
				json::nak($action);
			}
		} elseif ('prioritise' == $action) {

			if ($id = (int)$this->getPost('id')) {
				if ($priority = (int)$this->getPost('priority')) {
					$dao = new dao\forum;
					if ($dao->prioritise($id, $priority)) {

						$text = '?';
						if ($priority == config::FORUM_LOW_PRIORITY)
							$text = config::FORUM_LOW_PRIORITY_TEXT;
						elseif ($priority == config::FORUM_NORMAL_PRIORITY)
							$text = config::FORUM_NORMAL_PRIORITY_TEXT;
						elseif ($priority == config::FORUM_MEDIUM_PRIORITY)
							$text = config::FORUM_MEDIUM_PRIORITY_TEXT;
						elseif ($priority == config::FORUM_HIGH_PRIORITY)
							$text = config::FORUM_HIGH_PRIORITY_TEXT;
						elseif ($priority == config::FORUM_URGENT_PRIORITY)
							$text = config::FORUM_URGENT_PRIORITY_TEXT;
						elseif ($priority == config::FORUM_BROKEN_PRIORITY)
							$text = config::FORUM_BROKEN_PRIORITY_TEXT;

						json::ack('prioritised')
							->add('priority', $priority)
							->add('text', $text);
					} else {
						json::nak($action);
					}
				} else {
					json::nak($action);
				}
			} else {
				json::nak($action);
			}
		} elseif ('priority' == $action) {
			$data = [
				'broken' => config::FORUM_BROKEN_PRIORITY,
				'urgent' => config::FORUM_URGENT_PRIORITY,
				'high' => config::FORUM_HIGH_PRIORITY,
				'medium' => config::FORUM_MEDIUM_PRIORITY,
				'normal' => config::FORUM_NORMAL_PRIORITY,
				'low' => config::FORUM_LOW_PRIORITY,
			];
			json::ack($action)
				->add('data', $data);
		} elseif ('priority-reset' == $action) {
			$this->db->Q(
				sprintf(
					'UPDATE forum SET priority = "%s"',
					config::FORUM_NORMAL_PRIORITY

				)

			);
			json::ack($action);
		} elseif ('set-ipp' == $action) {
			if ($i = (int)$this->getPost('value')) {
				currentUser::option('forum-items-per-page', $i);
			} else {
				currentUser::option('forum-items-per-page', '');
			}
			json::ack($action);
		} elseif ('set-flag' == $action) {
			if ($id = (int)$this->getPost('id')) {
				$dao = new dao\forum;
				$dao->UpdateByID(['flag' => (int)$this->getPost('val')], $id);
				json::ack($action);
			} else {
				json::nak($action);
			}
		} elseif ('set-resolved' == $action) {
			if ($id = (int)$this->getPost('id')) {
				$dao = new dao\forum;
				$dao->UpdateByID(['resolved' => (int)$this->getPost('val')], $id);
				json::ack($action);
			} else {
				json::nak($action);
			}
		} elseif ('search-forums' == $action) {

			// logger::info(sprintf('<%s> %s', $action, logger::caller()));

			if ($term = $this->getPost('term')) {

				json::ack($action)
					->add('data', (new dao\forum)->search($term));
			} else {

				json::ack($action)
					->add('data', [$term]);
			}
		} elseif ('show-closed' == $action) {
			currentUser::option('forum-closed', $this->getPost('state'));
			json::ack($action);
		} elseif ('show-complete' == $action) {
			currentUser::option('forum-complete', $this->getPost('state'));
			json::ack($action);
		} elseif ('show-dead' == $action) {
			currentUser::option('forum-hidedead', $this->getPost('state'));
			json::ack($action);
		} elseif ('show-mine' == $action) {

			currentUser::option('forum-showOnlyMine', $this->getPost('state'));
			json::ack($action);
		} elseif ('subscribe' == $action) {

			$id = (int)$this->getPost('id');
			if ($id > 0) {

				$email = $this->getPost('email', currentUser::email());

				$dao = new dao\forum;
				if ($dao->subscribe($id, $email)) {

					json::ack('subscribe');
				} else {

					json::nak('subscribe');
				}
			} else {

				json::nak('subscribe');
			}
		} elseif ('unsubscribe' == $action) {

			$id = (int)$this->getPost('id');
			if ($id > 0) {

				$email = $this->getPost('email', currentUser::email());
				if ((new dao\forum)->unsubscribe($id, $email)) {

					json::ack('unsubscribe');
				} else {

					json::nak('unsubscribe');
				}
			} else {

				json::nak('unsubscribe');
			}
		} elseif ('update-subject' == $action) {

			$id = (int)$this->getPost('id');
			if ($id > 0) {
				$dao = new dao\forum;
				if ($dto = $dao->getById($id)) {
					if ($subject = $this->getPost('subject')) {

						$dao->UpdateByID(['description' => $subject], $id);
						json::ack($action);
					} else {

						json::nak($action);
					}
				} else {

					json::nak($action);
				}
			} else {
				json::nak($action);
			}
		} elseif ('forum-attachment-remove' == $action) {

			$id = (int)$this->getPost('id');
			if ($id > 0) {

				if ($file = $this->getPost('file')) {

					$file = strings::safe_file_name(strtolower((string)$file));
					$dao = new dao\forum;
					if ($dto = $dao->getById($id)) {

						if ($store = $dao->store($dto->id)) {

							$path = $store . DIRECTORY_SEPARATOR . $file;
							if (file_exists($path)) unlink($path);
							json::ack($action);
						} else {

							json::nak('store not found - ' . $action);
						}
					} else {

						json::nak('forum topic not found - ' . $action);
					}
				} else {

					json::nak($action);
				}
			} else {

				json::nak('missing id - ' . $action);
			}
		} elseif ('forum-attachment-upload' == $action) {

			if ($_FILES) {

				$file = array_shift($_FILES); // 1 file
				$id = (int)$this->getPost('id');
				if ($id > 0) {

					$dao = new dao\forum;
					if ($dto = $dao->getById($id)) {

						if ($store = $dao->store($dto->id)) {

							$uploader = new fileUploader([
								'path' => $store,
								'accept' => [
									'image/png',
									'image/x-png',
									'image/jpeg',
									'image/pjpeg',
									'application/pdf',
									'text/csv',
									'text/plain'
								]
							]);

							$uploader->save($file)
								? json::ack($action) : json::nak($action);
						} else {

							json::nak('store not found - ' . $action);
						}
					} else {

						json::nak('forum topic not found - ' . $action);
					}
				} else {

					json::nak('missing id - ' . $action);
				}
			} else {

				json::nak('no files - ' . $action);
			}
		} else {
			$action = $this->getPost('form_action');
			if ($action == 'post comment') {
				if ($parent = (int)$this->getPost('parent')) {
					$dao = new dao\forum;
					if ($dtoP = $dao->getById($parent)) {
						$dto = new dao\dto\forum;
						$dto->comment = $this->getPost('comment');
						if (empty($dto->comment)) {
							Response::redirect($this->route . '/view/' . $dtoP->id, 'not adding post with empty comment');
						} else {
							$dto->description = $dtoP->description;
							$dto->parent = $dtoP->id;
							$dto->thread = $this->getPost('thread');
							$dto->notify = $dtoP->notify;
							if ($dao->InsertDTO($dto)) {
								Response::redirect($this->route . '/view/' . $dtoP->id, 'added comment');
							} else {

								throw new RuntimeException('failed to add comment');
							}
						}
					} else {

						throw new RuntimeException('Could not find Forum to comment on');
					}
				} else {

					throw new RuntimeException('Forum not identified to comment on');
				}
			} else {
				parent::postHandler();
			}
		}
	}

	public function add() {

		$this->data = (object)[
			'tags' => (new dao\forum)->getRecentTags()
		];

		$this->load('new-post');
	}

	public function download($id = 0) {

		$id = (int)$id;
		if ($id > 0) {

			if ($file = $this->getParam('f')) {

				if ($file = strings::safe_file_name(strtolower((string)$file))) {

					$dao = new dao\forum;
					if ($dto = $dao->getById($id)) {

						if ($store = $dao->store($dto->id)) {

							$path = $store . DIRECTORY_SEPARATOR . $file;
							if (file_exists($path)) {

								Response::serve($path);
							} else {

								print 'not found';
							}
						} else {

							print 'store not found';
						}
					} else {

						print 'topic not found';
					}
				} else {

					print 'invalid file';
				}
			} else {

				print 'missing file';
			}
		}
	}

	public function flagged() {
		$dao = new dao\forum;
		$this->data = (object)[
			'res' => $dao->getFlagged()

		];

		$this->render([
			'title' => $this->title = 'forum - flagged',
			'primary' => 'flagged',
			'secondary' => 'index',
			'data' => [
				'pageUrl' => strings::url($this->route)

			]

		]);
	}

	public function closeTopic($id = 0) {
		if ($this->isPost()) {
			$id = (int)$this->getPost('id');
			if ($id > 0) {
				$dao = new dao\forum;
				if ($dto = $dao->closeTopic($id))
					json::ack(__METHOD__);

				else {
					json::nak(__METHOD__);
				}
			} else {
				json::nak(__METHOD__);
			}
		} else {
			if ($id > 0) {
				$dao = new dao\forum;
				if ($dto = $dao->closeTopic($id)) {
					Response::redirect($this->route, 'topic closed');
				}
				Response::redirect($this->route, 'could not close topic');
			}

			Response::redirect($this->route);
		}
	}

	public function reopenTopic($id = 0) {
		if ($this->isPost()) {
			$json = new Json();

			$id = (int)$this->getPost('id');
			if ($id > 0) {
				$dao = new dao\forum;
				if ($dto = $dao->reopenTopic($id))
					$json->add('response', 'ok');

				else
					$json->add('response', 'nak');
			} else
				$json->add('response', 'nak');
		} else {
			if ($id > 0) {
				$dao = new dao\forum;
				if ($dto = $dao->reopenTopic($id))
					Response::redirect($this->route . '/view/' . $id, 'topic re-opened');

				else
					Response::redirect($this->route . '/view/' . $id, 'could not re-open topic');
			}

			Response::redirect($this->route);
		}
	}

	public function tag($id = 0) {
		if ($id = (int)$id) {
			$dao = new dao\forum;
			if ($dto = $dao->getById($id)) {
				$this->data = (object)[
					'dto' => $dto,
					'tags' => $dao->getRecentTags()

				];

				$this->load('tag');
			} else {
				$this->load('not-found');
			}
		} else {
			$this->load('not-found');
		}
	}

	public function tagProperty($id = 0, $property = 0) {
		if ((int)$id > 0) {
			$dao = new dao\forum;
			if ($dto = $dao->getById($id)) {
				if ((int)$property > 0) {
					$dao->UpdateByID(['property_id' => (int)$property], $id);
					Response::redirect($this->route . '/view/' . $id, 'tagged property for forum topic');
				} else {
					$dao->UpdateByID(['property_id' => 0], $id);
					Response::redirect($this->route . '/view/' . $id, 'untagged property for forum topic');
				}
			} else {
				Response::redirect($this->route, 'could not find forum topic');
			}
		}

		Response::redirect($this->route);
	}

	public function view($id = 0) {

		$this->comments = true;

		if ($id > 0) {

			$dao = new idea\dao\forum_idea;
			$ideas = $dao->count() > 0;

			$dao = new dao\forum;
			if ($dto = $dao->getById($id)) {


				$this->data = (object)[
					'dto' => $dto,
					'tags' => $dao->getTags($asJson = true),
					'ideas' => $ideas,
					'title' => $this->title = sprintf('forum :: %s', $dto->description),
					'pageUrl' => strings::url($this->route . '/view/' . $id),
					'searchFocus' => true,
					'aside' => ['view-options']
				];

				$this->renderBS5([
					'main' => fn () => $this->load('view')
				]);
			} else {

				throw new Exceptions\ForumTopicNotFound;
			}
		} else {

			$this->_index();
		}
	}
}
