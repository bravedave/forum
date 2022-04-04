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
	var $id = 0;
	var $description = '';
	var $parent = 0;
	var $comment = '';
	var $thread = 0;
	var $updated = 0;
	var $closed = 0;
	var $priority = 0;
	var $user_id = 0;
	var $by_email = 0;
	var $tag = '';
	var $flag = 0;
	var $resolved = 0;
	var $notify = '';

	public function subscribed( $email ) {
		$a = explode( '|', $this->notify );
		if ( in_array( $email, $a )) {
			//~ \sys::logger( 'found subscriber' );
			return true;

		}

		return false;

	}

	public function subscribers() {
		return ( $a = explode( '|', $this->notify ));

	}

	public function subscribe( $email ) {
		if ( $this->notify == '' )
			$a = array();
		else
			$a = explode( '|', $this->notify );

		if ( $email) {
			$found = false;
			foreach ($a as $e ) {
				if ( $e == $email )
					$found = true;

			}

			if ( !$found )
				$a[] = $email;

		}

		$this->notify = implode( '|', $a );

	}

	public function unsubscribe( $email ) {
		$s = array();
		$a = explode( '|', $this->notify );
		foreach ($a as $e ) {
			if ( $e != $email )
				$s[] = $e;

		}

		$this->notify = implode( '|', $s );

	}

}
