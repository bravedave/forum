<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

abstract class sys extends bravedave\dvc\sys {

  static function forumMailer() {
    return (self::mailer(true));
  }
}