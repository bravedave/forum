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

use bravedave\dvc\dto;

class forum extends dto {
	public $id = 0;
	public $description = '';
	public $parent = 0;
	public $comment = '';
	public $thread = 0;
	public $closed = 0;
	public $priority = 0;
	public $user_id = 0;
	public $by_email = 0;
	public $tag = '';
	public $flag = 0;
	public $resolved = 0;
	public $complete = 0;

	public $property_id = 0;

	public $address_street = '';

	public $forum_idea_id = 0;

	public $forum_idea_idea = '';

	public $link = '';
	public $notify = '';

	public $created = '';
	public $updated = '';


	public function subscribed($email) {

		$a = explode('|', $this->notify);
		return in_array($email, $a);
	}

	public function subscribers() {

		return ($a = explode('|', $this->notify));
	}

	public function subscribe($email) {

		$a = $this->notify ? explode('|', $this->notify) : [];

		if ($email) {

			$found = in_array($email, $a);
			if (!$found) $a[] = $email;
		}

		$this->notify = implode('|', $a);
	}

	public function unsubscribe($email) {

		$a = explode('|', $this->notify);
		$s = array_diff($a, [$email]);
		$this->notify = implode('|', $s);
	}
}
