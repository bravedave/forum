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

use bravedave\dvc\{dao, dtoSet};

class forum_board extends dao {
	protected $_db_name = 'forum_board';
	protected $template = __NAMESPACE__ . '\dto\forum_board';

	public function getMatrix(bool $archived = false) {

		$sql = sprintf(
			'SELECT
				fb.`id`,
				fb.`name`,
				fb.`status`,
				fb.`assigned_user_id`,
				ua.`name` assigned_name,
				fb.`priority`,
				fb.`description`,
				fb.`idea`,
				fb.`link`,
				fb.`user_id`,
				users.`name` user_name,
				fb.`archived`,
				fb.`created`,
				fb.`updated`
			FROM
				`%s` fb
					LEFT JOIN `users` ua ON `assigned_user_id` = `ua`.`id`
					LEFT JOIN `users` ON `assigned_user_id` = `users`.`id`',
			$this->_db_name
		);
		if (!$archived) $sql .= ' WHERE fb.`archived` = 0';

		$sql .= ' ORDER BY fb.`priority` DESC, fb.`id` ASC';

		return (new dtoSet)($sql);
	}

	public function Insert($a) {
		$a['created'] = $a['updated'] = self::dbTimeStamp();
		return parent::Insert($a);
	}

	public function UpdateByID($a, $id) {
		$a['updated'] = self::dbTimeStamp();
		return parent::UpdateByID($a, $id);
	}
}
