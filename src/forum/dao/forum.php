<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\forum\dao;

use bravedave\dvc\dao;
use bravedave\dvc\dto as dvcDTO;
use bravedave\dvc\dtoSet;
use bravedave\dvc\email;
use bravedave\dvc\logger;
use bravedave\dvc\sendmail;
use bravedave\dvc\template;
use cms\currentUser;
use DOMDocument;
use dvc\forum\{
	forumUtility,
	strings,
	config
};

class forum extends dao {
	protected $_db_name = 'forum';
	protected $template = dto\forum::class;

	public $debug = false;

	public function getTopLevel(
		$closed = false,
		$complete = false,
		$hidedead = false,
		$showOnlyMine = false,
		$resolved = true,
		$offset = 0,
		$limit = 20
	): object {

		$debug = false;
		$debug = true;

		$condition = array('f.parent = 0');
		if ($limit < 1) $limit = 20;

		if (!$closed) $condition[] = 'f.closed = 0';
		if (!$complete) $condition[] = 'f.complete = 0';
		if (!$resolved) $condition[] = sprintf('f.resolved != %d', config::resolved_resolved);
		if ($hidedead) {

			$condition[] = sprintf(
				'(
				CASE
				WHEN u.user_id IS NULL THEN f.user_id <> %d
				ELSE u.user_id <> %d
				END
			OR
				CASE
				WHEN u.updated IS NULL THEN f.updated > "%s"
				ELSE u.updated > "%s"
			END)',
				currentUser::id(),
				currentUser::id(),
				date('Y-m-d', strtotime('-3 days')),
				date('Y-m-d', strtotime('-3 days'))
			);
		}

		$this->Q('DROP TABLE IF EXISTS tmp');
		$this->Q('CREATE TEMPORARY TABLE tmp as
			SELECT src.parent,
				src.updated,
				fx.comment,
				fx.user_id
			FROM (
					SELECT parent,
						MAX(updated) updated
					FROM forum
					WHERE parent IN (SELECT DISTINCT parent FROM forum WHERE parent <> 0)
					GROUP BY parent
				) src
				LEFT JOIN forum fx ON 
					fx.parent = src.parent
					AND fx.updated = src.updated');
		$this->Q('CREATE INDEX idx_tmp_parent ON tmp(parent);');

		$sql = sprintf(
			'SELECT
				f.id,
				f.tag,
				f.description,
				f.updated created,
				f.comment,
				u.comment last_comment,
				f.closed,
				f.closed_date,
				f.complete,
				f.complete_date,
				f.user_id reporter,
				reporter.name reporter_name,
				CASE
					WHEN u.updated IS NULL THEN f.updated
					ELSE u.updated
				END last_updated,
				CASE
					WHEN u.user_id IS NULL THEN f.user_id
					ELSE u.user_id
				END user_id,
				properties.address_street address,
				users.name user_name,
				f.notify,
				f.priority,
				f.resolved
			FROM
				forum f
					LEFT JOIN tmp u ON u.parent = f.id
					LEFT JOIN users ON
							CASE
								WHEN u.user_id IS NULL THEN users.id = f.user_id
								ELSE users.id = u.user_id
							END
					LEFT JOIN users reporter ON reporter.id = f.user_id
					LEFT JOIN properties ON properties.id = f.property_id
			WHERE %s
			ORDER BY last_updated DESC',
			implode(' AND ', $condition)
		);

		if ($debug) logger::sql($sql, logger::caller());
		/**/

		$dtoSet = [];
		$dtoSet = (new dtoSet)($sql, function ($dto) {

			if (!$dto->priority) {
				$dto->priority = 5;
			} elseif ($dto->closed == 1) {
				$dto->priority = 1;
			} elseif ($dto->complete == 1) {
				$dto->priority = 2;
			}

			return $dto;
		});

		if ($showOnlyMine) {

			$dtoSet = array_filter($dtoSet, function ($dto) {
				/** @var dt\forum $dto */
				if ($dto->reporter == currentUser::id()) return true;
				if ($dto->user_id == currentUser::id()) return true;
				$notify = explode('|', $dto->notify);
				foreach ($notify as $email) {
					if ($email == currentUser::email()) return true;
				}
				return false;
			});
		}

		// sort dtoSet by `resolved` DESC, `priority` DESC, `last_updated` DESC
		usort($dtoSet, function ($a, $b) {

			/** @var dt\forum $a */
			/** @var dt\forum $b */
			if ($a->resolved != $b->resolved) {
				return ($b->resolved <=> $a->resolved);
			}
			if ($a->priority != $b->priority) {
				return ($b->priority <=> $a->priority);
			}
			return (strtotime($b->last_updated) <=> strtotime($a->last_updated));
		});

		$tot = count($dtoSet);
		if ($offset) $dtoSet = array_slice($dtoSet, $offset);

		// if there is a limit, we need to slice the dtoSet
		if ($limit) $dtoSet = array_slice($dtoSet, 0, $limit);

		$count = count($dtoSet);

		$res = (object)[
			'start' => (int)$offset + 1,
			'end' => (int)$offset + $count,
			'page' => ((int)$offset / (int)$limit) + 1,
			'nextpage' => ((int)$offset / (int)$limit) + 2,
			'totalpages' => (int)((int)$tot / (int)$limit) + 1,
			'total' => (int)$tot,
			'dtoSet' => $dtoSet
		];

		return $res;
	}

	public function subscribe($id, $email = null) {

		if ($dto = $this->getByID($id)) {

			/** @var dt\forum $dto */

			if (is_null($email)) $email = currentUser::email();
			$emails = explode(',', $email);

			foreach ($emails as $em) {

				if (!($dto->subscribed($em))) {

					$dto->subscribe($em);
					$this->UpdateByID(['notify' => $dto->notify], $dto->id);

					return true;
				}
			}
		}

		return false;
	}

	public function unsubscribe($id, $email = null) {

		if ($dto = $this->getByID($id)) {

			/** @var dt\forum $dto */
			if (is_null($email)) $email = currentUser::email();

			if ($dto->subscribed($email)) {

				$dto->unsubscribe($email);
				$this->UpdateByID(['notify' => $dto->notify], $dto->id);

				return true;
			}
		}

		return false;
	}

	public function reopenTopic($id) {
		if ($dto = $this->getByID($id)) {
			$this->UpdateByID(['closed' => 0], $dto->id);
			return true;
		}

		return false;
	}

	public function closeTopic($id) {
		if ($dto = $this->getByID($id)) {
			$this->UpdateByID([
				'closed' => 1,
				'closed_date' => self::dbTimeStamp()
			], $dto->id);
			return true;
		}

		return false;
	}

	public function getFlagged() {
		$sql = 'SELECT
			f.id,
			f.tag,
			f.description,
			f.updated created,
			f.user_id,
			users.name user_name
		FROM
			forum f
			LEFT JOIN
				users ON f.user_id = users.id
			WHERE
				f.flag = 1';

		if ($res = $this->Result($sql)) {
			return $res;
		}

		return null;
	}

	public function markComplete($id) {
		if ($dto = $this->getByID($id)) {
			$this->UpdateByID([
				'complete' => 1,
				'complete_date' => \db::dbTimeStamp()
			], $dto->id);

			return true;
		}

		return false;
	}

	public function markInComplete($id) {
		if ($dto = $this->getByID($id)) {
			$this->UpdateByID([
				'complete' => 0

			], $dto->id);

			return true;
		}

		return false;
	}

	public function prioritise($id, $priority) {
		if (($id = (int)$id) && (int)$priority < 10) {
			$a = array('priority' => $priority);
			$this->UpdateByID($a, $id);
			return true;
		}

		return false;
	}

	public function getById($id) {

		$sql = sprintf(
			'SELECT
				f.*,
				p.address_street address,
				idea.idea forum_idea_idea,
				users.name
			FROM forum f
				LEFT JOIN users ON users.id = f.user_id
				LEFT JOIN properties p on p.id = f.property_id
				LEFT JOIN forum_idea idea on idea.id = f.forum_idea_id
			WHERE f.id = %d',
			$id
		);

		if ($dto = (new dto\forum)($sql)) {

			$dto->comments = [];
			$dto->children = [];

			$sql = sprintf(
				'SELECT
					f.id,
					f.comment,
					f.thread,
					f.updated,
					f.user_id,
					users.name
				FROM forum f
					LEFT JOIN users on users.id = f.user_id
				WHERE f.parent = %d
				ORDER BY f.id ASC',
				$id
			);

			(new dtoSet)($sql, function ($c) use ($dto) {

				$c->children = [];
				if ($c->thread == '') {

					$dto->children[] = $c;
				} else {

					$a = explode(':', $c->thread);
					$pid = array_pop($a);
					if ($pid == $dto->id) {

						$dto->children[] = $c;
					} else {

						$dto->comments[$pid]->children[] = $c;
					}
				}

				$dto->comments[$c->id] = $c;
			}, dto\forum::class);

			return $dto;
		}

		return null;
	}

	public function notify(string $subject, string $email, int $forumTop): void {
		if ($this->debug) \sys::logger(sprintf('add forum email ::%s:', $email));

		$url = strings::url('forum/view/' . $forumTop, $protocol = true);

		$prelude = sprintf(
			'<html><body><div>
			<p>Do NOT reply to this email</p>
			This forum topic can be viewed at: <a href="%s">%s</a>
			<p>&nbsp;</p>

			</div></body></html>',
			$url,
			$url
		);

		$DOM = new DOMDocument;
		$DOM->loadHTML(mb_convert_encoding($prelude, 'HTML-ENTITIES', 'UTF-8'));

		$body = $DOM->getElementsByTagName('body');
		if ($body && 0 < $body->length) {
			$body = $body->item(0);

			if ($dto = $this->getById($forumTop)) {
				$ftext = array('<table class="table table-striped"><tbody>');

				if (count($dto->children)) {
					$ftext[] = '<tr><td colspan="3" style="padding: 10px;"><strong>forum thread</strong>' . PHP_EOL;
					$ftext[] = sprintf('<tr><td colspan="3" style="padding: 0;">%s', PHP_EOL);
					for ($i = count($dto->children); $i > 0; $i--)
						$ftext[] = forumUtility::printThread($dto->children[$i - 1], NULL, TRUE);

					$ftext[] = sprintf('</td></tr>%s', PHP_EOL);
				}	// if ( count( $dto->children ))

				$ftext[] = '<tr><td colspan="3" style="padding: 10px;"><strong>original post</strong>' . PHP_EOL;
				$ftext[] = sprintf(
					'<tr>
						<td>%s</td>
						<td style="width: 60px;">%s<br />%s</td>
						<td style="width: 60px;">%s</td>
					</tr>',
					strings::AutoTextAsHTML($dto->comment),
					date('d/m/Y', strtotime($dto->updated)),
					date('h:ia', strtotime($dto->updated)),
					strings::initials($dto->name)
				);

				$ftext[] = '</tbody></table>';

				$tmpDoc = new DOMDocument;
				libxml_use_internal_errors(true);
				$tmpDoc->loadHTML(mb_convert_encoding(implode('', $ftext), 'HTML-ENTITIES', 'UTF-8'));
				libxml_clear_errors();

				foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {
					$node = $body->ownerDocument->importNode($node, true);
					$body->appendChild($node);
				}

				// \html::appendHTML($body, implode('', $ftext));
			}

			$html =  $DOM->saveHTML();

			// create instance
			$cssToInlineStyles = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
			if (file_exists($path = sprintf('%s/minimum.css', config::$TEMPLATES_DIR_CSS))) {
				$css = file_get_contents($path);
			} else {
				$css = file_get_contents(template::pdf_css);
			}

			// output
			$html = $cssToInlineStyles->convert($html, $css);
			// $html = utf8_decode($html);	// without this you get a double encoding to UTF-8
			// $html = mb_convert_encoding($html, 'ISO-8859-1', 'UTF-8');	// without this you get a double encoding to UTF-8
			$html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');	// without this you get a double encoding to UTF-8

			$symfony = true;
			// $symfony = false;
			if ($symfony) {

				$mail = sendmail::email([
					'from' => sendmail::address(config::$FORUM_EMAIL, config::$FORUM_NAME)
				]);

				$mail
					->to($email)
					->subject($subject)
					->html(sendmail::image2cid($html, $mail));

				try {
					sendmail::send($mail);
					if ($this->debug) \sys::logger('OK - Forum Mail Sent');
				} catch (\Throwable $th) {
					//throw $th;
					if ($this->debug) \sys::logger('NOK - Forum Mailer Error: ' . $th->getMessage());
				}
			} else {

				$msg = email::image2cid($html);
				if (file_exists($_htmlOut = sprintf('%s/html.html', config::dataPath()))) {
					unlink($_htmlOut);
				}
				// file_put_contents($_htmlOut, $html);

				$mail = \sys::forumMailer();
				$mail->CharSet = 'UTF-8';
				$mail->Encoding = 'base64';
				$mail->Subject  = $subject;
				$mail->AddAddress($email);
				$mail->MsgHTML($msg, sys_get_temp_dir());

				if ($mail->Send()) {
					if ($this->debug) \sys::logger('OK - Forum Mail Sent');
				} else {
					if ($this->debug) \sys::logger('NOK - Forum Mailer Error: ' . $mail->ErrorInfo);
				}
			}
		}
	}

	public function Insert($a) {
		$a['created'] = $a['updated'] = self::dbTimeStamp();
		return parent::Insert($a);
	}

	public function InsertDTO(dto\forum $dto, $notifyList = null) {

		$a = [
			'comment' => $dto->comment,
			'description' => $dto->description,
			'parent' => $dto->parent,
			'thread' => $dto->thread,
			'priority' => $dto->priority,
			'by_email' => $dto->by_email,
			'tag' => $dto->tag,
			'forum_idea_id' => $dto->forum_idea_id,
			'user_id' => currentUser::id()
		];

		if ($dto->parent > 0) {

			$id = $this->Insert($a);
		} else {

			if (is_null($notifyList)) $notifyList = [];

			/*
			 * ensure the emails are correct and
			 * not "Name Of You" <email@you.com>
			 */
			$_notifyList = [];
			foreach ($notifyList as $e) {
				$o = new \EmailAddress($e);
				if ($o->check()) $_notifyList[] = $o->email;
			}

			if (!in_array(config::$SUPPORT_EMAIL, $_notifyList))
				$_notifyList[] = config::$SUPPORT_EMAIL;

			/* this has to be consistent with dto\forum->subscribe() */
			$a['notify'] = implode('|', $_notifyList);

			$id = $this->Insert($a);
			$this->subscribe($id);
			$dto = $this->getById($id);
		}

		$z = explode('|', $dto->notify);
		foreach ($z as $email) {

			if ($email != '' && $email != currentUser::email()) {

				if (config::$SUPPORT_EMAIL == $email && currentUser::isDavid()) {

					if ($this->debug) logger::debug(sprintf('<InsertDTO // not self notifing : %s> %s', $email, logger::caller()));
				} else {

					$this->notify(
						sprintf('%s: %s', ($dto->parent > 0 ? 'Update' : 'New'), $dto->description),
						$email,
						($dto->parent > 0 ? $dto->parent : $id)
					);

					if ($this->debug) logger::debug(sprintf('<notify : %s> %s', $email, logger::caller()));
				}
			} else {

				if ($this->debug) logger::debug(sprintf('<NOT notify : %s> %s', $email, logger::caller()));
			}
		}

		return $id;
	}

	public function last_updated(dto\forum $dto) {
		if ($res = $this->Result(sprintf('SELECT MAX(updated) updated FROM forum WHERE parent = %d', $dto->id))) {
			if ($_dto = $res->dto())
				return ($_dto->updated);
		}

		return ($dto->updated);
	}

	public function getRecentTags(): array {
		$_sql =
			'SELECT DISTINCT tag
      FROM forum
      WHERE tag <> ""
      ORDER BY id DESC
      LIMIT 10';

		if ($res = $this->Result($_sql)) {
			return ($res->dtoSet());
		}

		return [];
	}

	public function getTags($asJson = false) {
		$ret = [];
		$res = $this->Result('SELECT DISTINCT tag FROM forum WHERE tag <> ""');
		if ($asJson) {
			while ($dto = $res->dto())
				$ret[] = $dto->tag;

			return (json_encode($ret));
		} elseif ($res)
			return ($res->dtoSet());

		return ($ret);
	}

	protected function _link($a, $b) {
		if ($dto = $this->getByID($a)) {
			$data = ['link' => $b];
			if ($dto->link) {
				$links = explode(',', $dto->link);
				if (!(in_array($b, $links))) {
					$links[] = $b;
				}
				$data['link'] = implode(',', $links);
			}
			$this->UpdateByID($data, $dto->id);
		}
	}

	public function link($a, $b) {
		if ($a & $b && $a != $b) {
			$this->_link($a, $b);
			$this->_link($b, $a);
			return true;
		}

		return false;
	}

	protected function _linkRemove($a, $b) {
		if ($dto = $this->getByID($a)) {
			if ($dto->link) {
				$links = explode(',', $dto->link);
				if (in_array($b, $links)) {
					$_links = [];
					foreach ($links as $link) {
						if ($link != $b) $_links[] = $link;
					}

					$data = ['link' => implode(',', $_links)];	// could be ''
					$this->UpdateByID($data, $dto->id);

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	public function linkRemove($a, $b) {
		if ($a & $b && $a != $b) {
			$this->_linkRemove($a, $b);
			$this->_linkRemove($b, $a);
			return true;
		}

		return false;
	}

	public function search(string $term): array {

		$results = [];

		$sql = 'SELECT `id`, `updated`, `parent`, `description`, `comment` FROM `forum`';
		if ($res = $this->Result($sql)) {

			while ($dto = $res->dto()) {

				if (false !== stripos($dto->description, $term) || false !== stripos($dto->comment, $term)) {

					$result = (object)[
						'id' => $dto->parent ? $dto->parent : $dto->id,
						'date' => $dto->updated,
						'description' => $dto->description,
						'instance' => $dto->description
					];

					if (false === stripos($dto->description, $term)) {

						// the instance must be in the description
						$comment = strings::html2text($dto->comment);
						if ($pos = stripos($comment, $term)) {

							// logger::info(sprintf('<%s> %s', $pos, logger::caller()));

							if ($pos > 0) $pos = max(0, ($pos - 30));
							$result->instance = sprintf(
								'%s%s...',
								$pos > 0 ? '...' : '',
								substr($comment, $pos, 120)
							);
						}
					}

					$results[] = $result;
				}
			}
		}

		return $results;
	}

	public function store(int $id): string {

		$path = implode(DIRECTORY_SEPARATOR, [
			config::forum_store(),
			$id
		]);

		if (!is_dir($path)) mkdir($path);
		return $path;
	}

	public function UpdateByID($a, $id) {
		$a['updated'] = self::dbTimeStamp();
		return parent::UpdateByID($a, $id);
	}
}
