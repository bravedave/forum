<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\idea;

class config extends \config {
  const idea_db_version = 0.11;

  const label = 'iDEA';
  const label_edit = 'Edit iDEA';
  const label_view = 'iDEA';
  const label_not_found = 'iDEA not found';

  static function idea_checkdatabase() {
    $dao = new dao\dbinfo(null, method_exists(__CLASS__, 'cmsStore') ? self::cmsStore() : self::dataPath());
    // $dao->debug = true;
    $dao->checkVersion('idea', self::idea_db_version);
  }
}
