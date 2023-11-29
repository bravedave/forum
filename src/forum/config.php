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

use Json;

abstract class config extends \config {
	const forum_db_version = 0.31;

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

	const label_edit = 'forum edit';
	const label_not_found = 'forum item not found';

	const resolved_resolved = 1;
	const resolved_noaction = 2;
	const resolved_feedback = 3;

	public static function forum_checkdatabase() {
		$dao = new dao\dbinfo(null, method_exists(__CLASS__, 'cmsStore') ? self::cmsStore() : self::dataPath());
		// // $dao->debug = true;
		$dao->checkVersion('forum', self::forum_db_version);

		if (file_exists($_file = self::forum_config())) {
			\sys::logger(sprintf('cleanup %s', $_file));
			unlink($_file);
		}
	}

	public static function forum_config() {
		return implode(DIRECTORY_SEPARATOR, [
			rtrim(self::dataPath(), '/ '),
			'forum.json'

		]);
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
