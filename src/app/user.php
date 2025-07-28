<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

class user extends bravedave\dvc\user {
	public $id = 0;

	public $mobile = '';

	public $telephone = '';

	public $sms_providor = '';

	public $sms_accountid = '';

	public $sms_accountpassword = '';

	public $sms_fromnumber = '';

	public $email_stationary = '';

	function __construct() {

		if (file_exists($path = config::dataPath() . '/currentUser.json')) {

			$_u = json_decode(file_get_contents($path));
			if (isset($_u->name)) $this->name = $_u->name;
			if (isset($_u->email)) $this->email = $_u->email;
			if (isset($_u->mobile)) $this->mobile = $_u->mobile;
		}
	}
}
