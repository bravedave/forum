<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms;

use bravedave;

class config extends bravedave\dvc\config {
  static $FORUM_NAME = 'Example Forum';
  static $FORUM_EMAIL = 'webmaster@example.dom';

  static function initialize() {
    parent::initialize();

    self::$FORUM_NAME = self::$SUPPORT_NAME;
    self::$FORUM_EMAIL = self::$SUPPORT_EMAIL;
  }

  static function cmsStore(): string {
    return parent::dataPath();
  }
}
