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
	const forum_db_version = 0.3;

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

	const resolved_resolved = 1;
	const resolved_noaction = 2;
	const resolved_feedback = 3;

  static protected $_FORUM_VERSION = 0;

	static function forum_checkdatabase() {
		if ( self::forum_version() < self::forum_db_version) {
      $dao = new dao\dbinfo;
			$dao->dump( $verbose = false);

			config::forum_version( self::forum_db_version);

		}

	}

	static function forum_config() {
		return implode( DIRECTORY_SEPARATOR, [
      rtrim( self::dataPath(), '/ '),
      'forum.json'

    ]);

	}

  static function forum_init() {
    $_a = [
      'forum_version' => self::$_FORUM_VERSION,

    ];

		if ( file_exists( $config = self::forum_config())) {

      $j = (object)array_merge( $_a, (array)Json::read( $config));

      self::$_FORUM_VERSION = (float)$j->forum_version;

		}

	}

	static protected function forum_version( $set = null) {
		$ret = self::$_FORUM_VERSION;

		if ( (float)$set) {
			$j = Json::read( $config = self::forum_config());

			self::$_FORUM_VERSION = $j->forum_version = $set;

			Json::write( $config, $j);

		}

		return $ret;

	}

}

config::forum_init();
