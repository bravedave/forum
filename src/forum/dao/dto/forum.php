<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\forum\dao\dto;

use dvc\dao\dto\_dto;

class forum extends _dto {
	public $id = 0;
	public $description = '';
	public $parent = 0;
	public $comment = '';
	public $thread = 0;
	public $updated = 0;
	public $closed = 0;
	public $priority = 0;
	public $user_id = 0;
	public $by_email = 0;
	public $tag = '';
	public $flag = 0;
	public $resolved = 0;

	public $property_id = 0;

	public $address_street = '';

	public $forum_idea_id = 0;

	public $forum_idea_idea = '';

	public $notify = '';

	public function subscribed($email) {
		$a = explode('|', $this->notify);
		if (in_array($email, $a)) {
			//~ \sys::logger( 'found subscriber' );
			return true;
		}

		return false;
	}

	public function subscribers() {
		return ($a = explode('|', $this->notify));
	}

	public function subscribe($email) {
		if ($this->notify == '')
			$a = array();
		else
			$a = explode('|', $this->notify);

		if ($email) {
			$found = false;
			foreach ($a as $e) {
				if ($e == $email)
					$found = true;
			}

			if (!$found)
				$a[] = $email;
		}

		$this->notify = implode('|', $a);
	}

	public function unsubscribe($email) {
		$s = array();
		$a = explode('|', $this->notify);
		foreach ($a as $e) {
			if ($e != $email)
				$s[] = $e;
		}

		$this->notify = implode('|', $s);
	}
}
