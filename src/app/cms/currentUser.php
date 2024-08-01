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

class currentUser extends bravedave\dvc\currentUser {

	static public function id() {
		return self::user()->id;
	}

	static function name() {
		return (self::user()->name);
	}

	static public function email() {
		return self::user()->email;
	}

	static function isDavid() {
		return true;
	}
}
