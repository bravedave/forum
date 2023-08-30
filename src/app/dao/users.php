<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dao;

use green;

class users extends green\users\dao\users {

  public function getActive($fields = '', $order = '') : array {

    return parent::getActive();
  }

  public function getUserByEmail($email) {

    return parent::getUserByEmail($email);
  }
}
