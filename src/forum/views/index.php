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

use currentUser;
use green;	?>

<ul class="nav flex-column">
	<li class="nav-item"><a class="h5" href="<?= strings::url( $this->route) ?>">forums</a></li>
	<li class="nav-item"><a class="nav-link" href="<?= strings::url( $this->route . '/flagged') ?>">flagged</a></li>

</ul>

