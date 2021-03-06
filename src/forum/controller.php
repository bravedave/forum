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

use currentUser;
use dvc\Response;
use dvc\Exceptions\GeneralException;
use green;
use Json;

class controller extends \Controller {
	var $ItemsPerPage = 20;
	protected $viewPath = __DIR__ . '/views/';

	protected function _index() {
		$this->showOnlyMine = currentUser::option( 'forum-showOnlyMine') == 'yes';
		$this->hideDead = currentUser::option( 'forum-hidedead') == 'yes';
		$this->includeComplete = currentUser::option( 'forum-complete') == 'yes';
		$this->showClosed = currentUser::option( 'forum-closed') == 'yes';

		$about = sprintf( '%s complete%s%s%s',
			( $this->includeComplete ? 'including' : 'excluding'),
			( $this->showClosed ? ', showing closed items' : ''),
			( $this->hideDead ? ', hiding dead items' : ''),
			( $this->showOnlyMine ? ', only my topics' : ''));

		$dao = new dao\forum;

		$page = (int)$this->getParam( 'page' );
		if ( $page < 1) $page = 1;
		$offset = ((int)$page - 1) * $this->ItemsPerPage;
		if ( $this->dataset = $dao->getTopLevel( $this->showClosed, $this->includeComplete, $this->hideDead, $this->showOnlyMine, $offset, $this->ItemsPerPage )) {

			$this->render([
				'title' => $this->title = sprintf( 'forum - %s', $about),
				'content' => [
					'parameters',
					'report'

				],
				'data' => [
					'pageUrl' => strings::url($this->route)

				]

			]);

		}
		else {
			$this->render([
				'title' => $this->title = sprintf( 'forum - %s', $about),
				'content' => 'blank',
				'data' => [
					'pageUrl' => strings::url($this->route)

				]

			]);

		}

	}

	protected function before() {
		config::forum_checkdatabase();

		if ( $ipp = (int)currentUser::option( 'forum-items-per-page')) {
      $this->ItemsPerPage = $ipp;

    }

		return parent::before();

	}


	protected function postHandler() {
		$action =$this->getPost('action');

		if ( 'save-tag' == $action) {
			if ( $id = (int)$this->getPost('id')) {
				$a = [ 'tag' => $this->getPost('tag')];
				$dao = new dao\forum;
				$dao->UpdateByID( $a, $id);

				Json::ack( sprintf( '%s : %s', $action, $a['tag']));

			}
			else { Json::nak( $action); }

		}
		elseif ( 'add-link' == $action || 'remove-link' == $action) {
			if ( $id = (int)$this->getPost('id')) {
				if ( $linkID = (int)$this->getPost('link')) {
					$dao = new dao\forum;
					if ( 'add-link' == $action) {
						if ( $dao->link( $id, $linkID)) {
							Json::ack( $action);

						} else { Json::nak( $action); }

					}
					elseif ( 'remove-link' == $action) {
						if ( $dao->linkRemove( $id, $linkID)) {
							Json::ack( $action);

						} else { Json::nak( $action); }

					} else { Json::nak( $action); }

				} else { Json::nak( $action); }

			} else { Json::nak( $action); }

		}
		elseif ( 'get-links' == $action) {
			if ( $id = (int)$this->getPost('id')) {
				$dao = new dao\forum;
				if ( $dto = $dao->getByID( $id)) {
					if ( $dto->link) {
						if ( $res = $this->db->Result( sprintf( 'SELECT id, description FROM forum WHERE id IN (%s)', $dto->link))) {
							Json::ack( $action)->add( 'data', $res->dtoSet());

						}
					} else { Json::ack( $action)->add( 'data', []); }

				} else { Json::nak( $action); }

			} else { Json::nak( $action); }

		}
		elseif ( 'get-active-users' == $action) {
			$dao = new green\users\dao\users;
			Json::ack( $action)
				->add( 'data', $dao->getActive());

		}
		elseif ( 'mark-complete' == $action || 'mark-incomplete' == $action) {
			if ( $id = (int)$this->getPost('id')) {
				$dao = new dao\forum;
				if ( 'mark-complete' == $action) {
					if ( $dao->markComplete( $id )) {
						$status = 0;	// open
						if ( $dto = $dao->getById( $id)) {
							if ( $dto->complete)
								$status = 2;	// complete
							elseif ( $dto->closed)
								$status = 3;	// closed
							elseif ( strtotime( $dao->last_updated( $dto)) > strtotime( $dto->updated))
								$status = 1;	// open and responded

						}
						/*--------------------------------------------------------------------------------------------*/

						Json::ack( $action)
							->add( 'complete', 'yes')
							->add( 'status', $status);

					} else { Json::nak( sprintf( '%s - failed to mark', $action)); }

				}
				elseif ( 'mark-incomplete' == $action) {
					if ( $dto = $dao->markInComplete( $id )) {
						$status = 0;	// open
						if ( $dto = $dao->getById( $id)) {
							if ( $dto->complete)
								$status = 2;	// complete
							elseif ( $dto->closed)
								$status = 3;	// closed
							elseif ( strtotime( $dao->last_updated( $dto)) > strtotime( $dto->updated))
								$status = 1;	// open and responded

						}
						/*--------------------------------------------------------------------------------------------*/

						Json::ack( $action)
							->add( 'complete', 'no')
							->add( 'status', $status);

					} else { Json::nak( sprintf( '%s - failed to mark', $action)); }

				} else { Json::nak( $action); }

			} else { Json::nak( $action); }

		}
		elseif ( 'notify' == $action) {
			/*
				this isn't used, it just to test the mailer
				( _ => _.post({
					url : _.url('forum'),
					data : { id : 7511, action : 'notify' },

				}).then( d => _.growl( d)))
				( _brayworth_ )

			*/
			if ( $id = (int)$this->getPost('id')) {
				$dao = new dao\forum;
				if ( $dto = $dao->getByID( $id)) {
					$dao->notify(
						sprintf( '%s : %s', ( $dto->parent > 0 ? 'Follow Up' : 'New Topic' ), $dto->description ),
						'test this',
						currentUser::email(),
						( $dto->parent > 0 ? $dto->parent : $id ));

					Json::ack( $action);

				} else { Json::nak( $action); }

			} else { Json::nak( $action); }

		}
		elseif ( 'post' == $action) {
			$dto = new dao\dto\forum;
			$dto->comment = $this->getPost( 'comment' );
			$dto->description = $this->getPost( 'description' );
			if ( empty( $dto->description) || empty( $dto->comment)) {
				Response::redirect( $this->route, 'not adding topic with empty description or comment' );

			}
			else {
				$dao = new dao\forum;
				if ( $dao->InsertDTO( $dto )) {
					Response::redirect( $this->route, 'added new topic' );

				}
				else { throw new GeneralException( 'failed to add new topic' ); }

			}

		}
		elseif ( 'post-new' == $action) {
			$dto = new dao\dto\forum;
			$dto->description = $this->getPost( 'description');
			$dto->comment = $this->getPost( 'comment');
			$dto->tag = $this->getPost( 'tag');
			$dto->priority = (int)$this->getPost( 'priority');
			if ( $dto->description && $dto->comment) {
				$notifyList = (array)$this->getPost('notify', \config::$SUPPORT_EMAIL);
				//~ sys::logger( implode( ',', $notifyList));
				//~ die;

				$dao = new dao\forum;
				$id = $dao->InsertDTO( $dto, $notifyList);
				if ( $link = $this->getPost('link')) {
					$dao->link( $id, $link);

				}

				Json::ack( $action);

			} else { Json::nak( $action); }

    }
    elseif ( 'prioritise' == $action) {

      if ( $id = (int)$this->getPost('id')) {
        if ( $priority = (int)$this->getPost('priority')) {
          $dao = new dao\forum;
          if ( $dao->prioritise( $id, $priority)) {

            $text = '?';
            if ( $priority == config::FORUM_LOW_PRIORITY)
              $text = config::FORUM_LOW_PRIORITY_TEXT;
            elseif ( $priority == config::FORUM_NORMAL_PRIORITY)
              $text = config::FORUM_NORMAL_PRIORITY_TEXT;
            elseif ( $priority == config::FORUM_MEDIUM_PRIORITY)
              $text = config::FORUM_MEDIUM_PRIORITY_TEXT;
            elseif ( $priority == config::FORUM_HIGH_PRIORITY)
              $text = config::FORUM_HIGH_PRIORITY_TEXT;
            elseif ( $priority == config::FORUM_URGENT_PRIORITY)
              $text = config::FORUM_URGENT_PRIORITY_TEXT;
            elseif ( $priority == config::FORUM_BROKEN_PRIORITY)
              $text = config::FORUM_BROKEN_PRIORITY_TEXT;

            Json::ack('prioritised')
              ->add( 'priority', $priority)
              ->add( 'text', $text);

          } else { Json::nak( $action); }

        } else { Json::nak( $action); }

      } else { Json::nak( $action); }

		}
		elseif ( 'priority' == $action) {
			$data = [
				'broken' => config::FORUM_BROKEN_PRIORITY,
				'urgent' => config::FORUM_URGENT_PRIORITY,
				'high' => config::FORUM_HIGH_PRIORITY,
				'medium' => config::FORUM_MEDIUM_PRIORITY,
				'normal' => config::FORUM_NORMAL_PRIORITY,
				'low' => config::FORUM_LOW_PRIORITY,
			];
			Json::ack( $action)
				->add( 'data', $data);

    }
		elseif ( 'priority-reset' == $action) {
      $this->db->Q(
        sprintf(
          'UPDATE forum SET priority = "%s"',
          config::FORUM_NORMAL_PRIORITY

        )

      );
      Json::ack($action);

    }
		elseif ( 'set-ipp' == $action) {
      if ( $i = (int)$this->getPost('value')) {
        currentUser::option( 'forum-items-per-page', $i );

      }
      else {
        currentUser::option( 'forum-items-per-page', '' );

      }
      Json::ack( $action);

    }
		elseif ( 'set-flag' == $action) {
			if ( $id = (int)$this->getPost('id')) {
				$dao = new dao\forum;
				$dao->UpdateByID( ['flag' => (int)$this->getPost('val')], $id);
				Json::ack( $action);

			}
			else {
				Json::nak( $action);

			}

		}
		elseif ( 'set-resolved' == $action) {
			if ( $id = (int)$this->getPost('id')) {
				$dao = new dao\forum;
				$dao->UpdateByID( ['resolved' => (int)$this->getPost('val')], $id);
				Json::ack( $action);

			}
			else {
				Json::nak( $action);

			}

		}
		elseif ( 'show-closed' == $action) {
			currentUser::option( 'forum-closed', $this->getPost('state'));
			Json::ack( $action);

		}
		elseif ( 'show-complete' == $action) {
			currentUser::option( 'forum-complete', $this->getPost('state'));
			Json::ack( $action);

		}
		elseif ( 'show-dead' == $action) {
			currentUser::option( 'forum-hidedead', $this->getPost('state'));
			Json::ack( $action);

		}
		elseif ( 'show-mine' == $action) {
			currentUser::option( 'forum-showOnlyMine', $this->getPost('state'));
			Json::ack( $action);

		}
		elseif ( 'update-subject' == $action) {
			$id = (int)$this->getPost( 'id' );
			if ( $id > 0 ) {
				$dao = new dao\forum;
				if ( $dto = $dao->getById( $id )) {
					if ( $subject = $this->getPost( 'subject' )) {

						$dao->UpdateByID( ['description' => $subject], $id);
						Json::ack( $action);

					} else { Json::nak( $action); }

				} else { Json::nak( $action); }

			} else { Json::nak(  $action); }

		}
		else {
			$action =$this->getPost('form_action');
			if ( $action == 'post comment' ) {
				if ( $parent = (int)$this->getPost( 'parent' )) {
					$dao = new dao\forum;
					if ( $dtoP = $dao->getById( $parent )) {
						$dto = new dao\dto\forum;
						$dto->comment = $this->getPost( 'comment' );
						if ( empty( $dto->comment)) {
							Response::redirect( $this->route . '/view/' . $dtoP->id, 'not adding post with empty comment' );

						}
						else {
							$dto->description = $dtoP->description;
							$dto->parent = $dtoP->id;
							$dto->thread = $this->getPost( 'thread' );
							$dto->notify = $dtoP->notify;
							if ( $dao->InsertDTO( $dto )) {
								Response::redirect( $this->route . '/view/' . $dtoP->id, 'added comment' );

							} else { throw new GeneralException( 'failed to add comment' ); }

						}

					} else { throw new GeneralException( 'Could not find Forum to comment on' ); }

				} else { throw new GeneralException( 'Forum not identified to comment on' ); }

      }
      else {
        parent::postHandler();

      }

		}

  }

  public function add() {
    $dao = new dao\forum;
    $this->data = (object)[
      'tags' => $dao->getRecentTags()

    ];

    $this->load( 'new-post');

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

	public function closeTopic( $id = 0 ) {
		if ( $this->isPost()) {
			$id = (int)$this->getPost('id');
			if ( $id > 0 ) {
				$dao = new dao\forum;
				if ( $dto = $dao->closeTopic( $id ))
					Json::ack( __METHOD__);

				else {
					Json::nak( __METHOD__);

				}

			}
			else {
				Json::nak( __METHOD__);

			}

		}
		else {
			if ( $id > 0 ) {
				$dao = new dao\forum;
				if ( $dto = $dao->closeTopic( $id )) {
					Response::redirect( $this->route, 'topic closed' );

				}
				Response::redirect( $this->route, 'could not close topic' );

			}

			Response::redirect( $this->route );

		}

	}

	public function reopenTopic( $id = 0 ) {
		if ( $this->isPost()) {
			$json = new Json();

			$id = (int)$this->getPost('id');
			if ( $id > 0 ) {
				$dao = new dao\forum;
				if ( $dto = $dao->reopenTopic( $id ))
					$json->add( 'response', 'ok');

				else
					$json->add( 'response', 'nak');

			}
			else
				$json->add( 'response', 'nak');


		}
		else {
			if ( $id > 0 ) {
				$dao = new dao\forum;
				if ( $dto = $dao->reopenTopic( $id ))
					Response::redirect( $this->route . '/view/' . $id, 'topic re-opened' );

				else
					Response::redirect( $this->route . '/view/' . $id, 'could not re-open topic' );

			}

			Response::redirect( $this->route );

		}

	}

  public function tag( $id = 0) {
		if ( $id = (int)$id) {
			$dao = new dao\forum;
			if ( $dto = $dao->getById( $id)) {
        $this->data = (object)[
          'dto' => $dto,
          'tags' => $dao->getRecentTags()

        ];

        $this->load( 'tag');

      } else { $this->load( 'not-found'); }

    } else { $this->load( 'not-found'); }

  }

	public function tagProperty( $id = 0, $property = 0 ) {
		if ( (int)$id > 0 ) {
			$dao = new dao\forum;
			if ( $dto = $dao->getById( $id )) {
				if ( (int)$property > 0 ) {
					$dao->UpdateByID( [ 'property_id' => (int)$property], $id);
					Response::redirect( $this->route . '/view/' . $id, 'tagged property for forum topic');

				}
				else {
					$dao->UpdateByID( [ 'property_id' => 0], $id);
					Response::redirect( $this->route . '/view/' . $id, 'untagged property for forum topic');

				}

			}
			else {
				Response::redirect( $this->route, 'could not find forum topic');

			}

		}

		Response::redirect( $this->route);

  }

	public function subscribe( $id = 0 ) {
		if ( $id > 0 ) {
			$email = $this->getParam( 'email', currentUser::email());

			$dao = new dao\forum;
			if ( $dao->subscribe( $id, $email)) {
				Json::ack( 'subscribe');

			} else { Json::nak( 'subscribe'); }

		} else { Json::nak( 'subscribe'); }

	}

	public function unsubscribe( $id = 0 ) {
		if ( $id > 0 ) {
			$email = $this->getParam( 'email', currentUser::email());

			$dao = new dao\forum;
			if ( $dao->unsubscribe( $id, $email)) {
				Json::ack( 'unsubscribe');

			} else { Json::nak( 'unsubscribe'); }

		} else { Json::nak( 'unsubscribe'); }

	}

	public function viewdlg( $id = 0 ) {
		$this->comments = FALSE;

		if ( $id > 0 ) {
			$dao = new dao\forum;
			if ( $dto = $dao->getById( $id )) {
				$this->data = (object)array(
					'dto' => $dto,
					'tags' => $dao->getTags());

				$this->load( 'view' );

			} else { print 'not found'; }

		} else { print 'invalid'; }

	}

	public function view( $id = 0 ) {
		$this->comments = true;

		if ( $id > 0 ) {
			$dao = new dao\forum;
			if ( $dto = $dao->getById( $id)) {
				$this->data = (object)[
					'dto' => $dto,
					'tags' => $dao->getTags( $asJson = TRUE)];

				$this->render([
					'title' => sprintf( 'forum :: %s', $dto->description),
					'primary' => 'view',
					'secondary' => 'view-options',
					'data' => [
						'pageUrl' => strings::url($this->route . '/view/' . $id)

					]

				]);

			}
			else { throw new Exceptions\ForumTopicNotFound; }

		} else { $this->_index(); }

	}

}
