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

use dvc\emailutility;
use dvc\forum\{forumUtility, strings};
use dvc\dao\_dao;

class forum extends _dao {
	protected $_db_name = 'forum';
	protected $template = __NAMESPACE__ . '\dto\forum';

	public $debug = false;

	public function getTopLevel($closed = false, $complete = false, $hidedead = false, $showOnlyMine = false, $offset = 0, $limit = 20) {

		$debug = false;
		// $debug = true;

		$condition = array('f.parent = 0');
		if ($limit < 1)
			$limit = 20;

		if (!$closed)
			$condition[] = 'f.closed = 0';
		if (!$complete)
			$condition[] = 'f.complete = 0';
		if ($hidedead)
			$condition[] = sprintf('(
				CASE
				WHEN u.user_id IS NULL THEN f.user_id <> %d
				ELSE u.user_id <> %d
				END
			OR
				CASE
				WHEN u.updated IS NULL THEN f.updated > "%s"
				ELSE u.updated > "%s"
			END)', \currentUser::id(), \currentUser::id(), date('Y-m-d', strtotime('-3 days')), date('Y-m-d', strtotime('-3 days')));

		$sql = sprintf(
			'CREATE TEMPORARY TABLE T AS
			SELECT
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
					LEFT JOIN
						(SELECT
							src.parent, src.updated, fx.comment, fx.user_id
						FROM
							(SELECT
								parent, MAX(updated) updated
							FROM
								forum
							WHERE
								parent IN (SELECT DISTINCT parent FROM forum WHERE parent <> 0)
							GROUP BY parent) src
								LEFT JOIN
									forum fx
									ON
										fx.parent = src.parent AND fx.updated = src.updated ) u
						ON
							u.parent = f.id
					LEFT JOIN
						users ON
							CASE
								WHEN u.user_id IS NULL THEN users.id = f.user_id
								ELSE users.id = u.user_id
							END
					LEFT JOIN
						users reporter ON reporter.id = f.user_id
					LEFT JOIN
						properties ON properties.id = f.property_id
			WHERE
				%s
			ORDER BY
				last_updated DESC',
			implode(' AND ', $condition)
		);

		if ($debug) \sys::logSQL($sql);
		//~ return ( $this->db->result( $sql));

		$this->Q($sql);

		if ($showOnlyMine) {
			$dKeys = array();
			if ($data = $this->result('SELECT id, reporter, user_id, notify FROM T')) {
				while ($dto = $data->dto()) {
					if ($dto->reporter == \currentUser::id()) continue;
					if ($dto->user_id == \currentUser::id()) continue;
					if (strpos($dto->notify, \currentUser::email()) !== false) continue;

					$dKeys[] = $dto->id;
				}
			}

			if (count($dKeys))
				$this->Q(sprintf('DELETE FROM T WHERE id IN (%s)', implode(',', $dKeys)));
		}

		//~ $this->Q( 'DROP TABLE IF EXISTS _t');
		//~ $this->Q( 'CREATE TABLE _t AS ( SELECT * FROM T)');
		/*
			set 5 as the default priority
			they may be low (4) to urgent (8),
			unprioritised records become normal
			*/
		$this->Q('UPDATE
				T
			SET
				priority = CASE
					WHEN complete = 1 THEN "2"
					WHEN closed = 1 THEN "1"
					ELSE "5"
					END
			WHERE
				priority IS NULL
				OR priority = ""
				OR priority = "0"
				OR closed = 1
				OR complete = 1
				');

		$tot = 0;
		//~ if ( $res = $this->db->Result( sprintf( 'SELECT count(*) count FROM forum f WHERE %s', implode( ' AND ', $condition )))) {
		if ($res = $this->Result('SELECT count(*) count FROM T')) {
			if ($row = $res->dto())
				$tot = (int)$row->count;
		}

		$count = 0;
		if ($data = $this->Result(sprintf('SELECT
				count(*) `count`
			FROM
				T
			ORDER BY
				priority DESC,
				last_updated DESC
			LIMIT %d,%d', $offset, $limit))) {

			if ($dto = $data->dto()) $rows = $dto->count;
		};

		$data = $this->Result(sprintf('SELECT
				*
			FROM
				T
			ORDER BY
				`resolved` DESC,
				`priority` DESC,
				`last_updated` DESC
			LIMIT %d,%d', $offset, $limit));

		$res = (object)[
			'start' => (int)$offset + 1,
			'end' => (int)$offset + $count,
			'page' => ((int)$offset / (int)$limit) + 1,
			'nextpage' => ((int)$offset / (int)$limit) + 2,
			'totalpages' => (int)((int)$tot / (int)$limit) + 1,
			'total' => (int)$tot,
			'data' => $data
		];

		return $res;
	}

	public function subscribe($id, $email = null) {
		if ($dto = $this->getByID($id)) {
			if (is_null($email))
				$email = \currentUser::email();

			$emails = explode(',', $email);

			foreach ($emails as $em) {
				if (!($dto->subscribed($em))) {
					$dto->subscribe($em);
					$this->UpdateByID(['notify' => $dto->notify], $dto->id);

					return (true);
				}
			}
		}

		return (false);
	}

	public function unsubscribe($id, $email = null) {
		if ($dto = $this->getByID($id)) {
			if (is_null($email))
				$email = \currentUser::email();

			if ($dto->subscribed($email)) {
				$dto->unsubscribe($email);
				$this->UpdateByID(['notify' => $dto->notify], $dto->id);

				return (true);
			}
		}

		return (false);
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
				'closed_date' => \db::dbTimeStamp()
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
				f.*, properties.address_street address, users.name
			FROM forum f
				LEFT JOIN
					users ON users.id = f.user_id
				LEFT JOIN
					properties on properties.id = f.property_id
			WHERE
				f.id = %d',
			$id
		);
		//~ \sys::logSQL( $sql);

		if ($res = $this->Result($sql)) {

			if ($row = $res->fetch()) {
				$dto = new dto\forum($row);
				$dto->comments = [];
				$dto->children = [];

				if ($res = $this->Result(
					sprintf('SELECT
						f.id, f.comment, f.thread, f.updated, f.user_id, users.name
					FROM forum f
						LEFT JOIN
							users on users.id = f.user_id
					WHERE
						f.parent = %d
					ORDER BY
						f.id ASC', $id)
				)) {

					while ($row = $res->fetch()) {
						$c = new dto\forum($row);
						$c->children = [];
						if ($c->thread == '') {
							$dto->children[] = $c;
						} else {
							$a = explode(':', $c->thread);
							$pid = array_pop($a);
							if ($pid == $id) {
								$dto->children[] = $c;
							} else {
								$dto->comments[$pid]->children[] = $c;
							}
						}

						$dto->comments[$row['id']] = $c;
					}
				}

				return ($dto);
			}
		}

		return FALSE;
	}

	public function notify($subject, $message, $email, $forumTop) {
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

		$DOM = new \DOMDocument;
		$DOM->loadHTML($prelude);

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

				\html::appendHTML($body, implode('', $ftext));
			}

			$html =  $DOM->saveHTML();

			// create instance
			$cssToInlineStyles = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
			$css = file_get_contents(sprintf('%s/%s', \config::$TEMPLATES_DIR_CSS, 'minimum.css'));

			// output
			$html = $cssToInlineStyles->convert($html, $css);

			$html = utf8_decode($html);	// without this you get a double encoding to UTF-8

			$mail = \sys::forumMailer();
			$mail->CharSet = 'UTF-8';
			$mail->Encoding = 'base64';
			$mail->Subject  = $subject;
			$mail->AddAddress($email);

			$msg = emailutility::image2cid($html);
			$mail->MsgHTML($msg, sys_get_temp_dir());

			if ($mail->Send()) {
				if ($this->debug) \sys::logger('OK - Forum Mail Sent');
			} else {
				if ($this->debug) \sys::logger('NOK - Forum Mailer Error: ' . $mail->ErrorInfo);
			}
		}
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
			'updated' => \db::dbTimeStamp(),
			'user_id' => \currentUser::id()
		];

		//~ \sys::logger( sprintf( 'message length: %s (%s)', strlen( $a['comment'] ), strlen( $dto->comment )));

		if ($dto->parent > 0) {
			$id = $this->Insert($a);
		} else {
			if (is_null($notifyList))
				$notifyList = [];

			/*
			 * ensure the emails are correct and
			 * not "Name Of You" <email@you.com>
			 */
			$_notifyList = [];
			foreach ($notifyList as $e) {
				$o = new \EmailAddress($e);
				if ($o->check()) $_notifyList[] = $o->email;
			}

			if (!in_array(\config::$SUPPORT_EMAIL, $_notifyList))
				$_notifyList[] = \config::$SUPPORT_EMAIL;

			/* this has to be consistent with dto\forum->subscribe() */
			$a['notify'] = implode('|', $_notifyList);

			$id = $this->Insert($a);
			$this->subscribe($id);
			$dto = $this->getById($id);
		}

		//~ \sys::logger( sprintf( 'message legnth: %s ', strlen( $dto->comment )));

		$z = explode('|', $dto->notify);
		foreach ($z as $email) {
			if ($email != '' && $email != \currentUser::email()) {
				if (\config::$SUPPORT_EMAIL == $email && \currentUser::isDavid()) {
					if ($this->debug) \sys::logger('InsertDTO // not self notifing : ' . $email);
				} else {

					$this->notify(
						sprintf('%s : %s', ($dto->parent > 0 ? 'Follow Up' : 'New Topic'), $dto->description),
						$a['comment'],
						$email,
						($dto->parent > 0 ? $dto->parent : $id)
					);

					if ($this->debug) \sys::logger('notify:' . $email);
				}
			} else {
				if ($this->debug) \sys::logger('NOT notify:' . $email);
			}
		}

		return ($id);
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
}
