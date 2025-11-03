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

use cms;

abstract class config extends cms\config {
	const forum_db_version = 1;

	const forum_filterKey = 'forum-filter';
	const forum_filterWho = 'forum-filter-who';

	const FORUM_PRIORITY_ALL_VALID = '456789';

	const FORUM_LOW_PRIORITY = '4';
	const FORUM_NORMAL_PRIORITY = '5';
	const FORUM_MEDIUM_PRIORITY = '6';
	const FORUM_HIGH_PRIORITY = '7';
	const FORUM_URGENT_PRIORITY = '8';
	const FORUM_BROKEN_PRIORITY = '9';

	const FORUM_LOW_PRIORITY_TEXT = 'low';
	const FORUM_NORMAL_PRIORITY_TEXT = 'normal';
	const FORUM_MEDIUM_PRIORITY_TEXT = 'medium';
	const FORUM_HIGH_PRIORITY_TEXT = 'high';
	const FORUM_URGENT_PRIORITY_TEXT = 'urgent';
	const FORUM_BROKEN_PRIORITY_TEXT = 'broken';

	const label_board = 'Forum Board';
	const label_board_item_add = 'add board item';
	const label_board_item_edit = 'edit board item';
	const label_edit = 'forum edit';
	const label_not_found = 'forum item not found';

	const board_priority_none = 0;
	const board_priority_low = 4;
	const board_priority_normal = 5;
	const board_priority_medium = 6;
	const board_priority_high = 7;
	const board_priority_urgent = 8;

	const board_priority_text = [
		self::board_priority_none => 'none',
		self::board_priority_low => 'low',
		self::board_priority_normal => 'normal',
		self::board_priority_medium => 'medium',
		self::board_priority_high => 'high',
		self::board_priority_urgent => 'urgent'
	];

	const board_status_none = 0;
	const board_status_draft = 1;
	const board_status_todo = 3;
	const board_status_inprogress = 5;
	const board_status_review = 7;
	const board_status_done = 9;

	const board_status_text = [
		self::board_status_none => 'none',
		self::board_status_draft => 'draft',
		self::board_status_todo => 'todo',
		self::board_status_inprogress => 'in progress',
		self::board_status_review => 'review',
		self::board_status_done => 'done'
	];

	const resolved_resolved = 1;
	const resolved_noaction = 2;
	const resolved_feedback = 3;

	public static function forum_checkdatabase() {
		$dao = new dao\dbinfo(null, method_exists(__CLASS__, 'cmsStore') ? self::cmsStore() : self::dataPath());
		// // $dao->debug = true;
		$dao->checkVersion('forum', self::forum_db_version);
	}

	public static function forum_store(): string {

		$_path = method_exists(__CLASS__, 'cmsStore') ? self::cmsStore() : self::dataPath();
		$path = implode(DIRECTORY_SEPARATOR, [
			rtrim($_path, " \n\r\t\v\0/"),
			'forum'
		]);

		if (!is_dir($path)) mkdir($path);
		return $path;
	}
}
