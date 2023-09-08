<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\forum;	?>

<nav class="nav flex-column">

	<a class="h5" href="<?= strings::url( $this->route) ?>">forums</a>
	<a class="nav-link" href="<?= strings::url( $this->route . '/flagged') ?>">flagged</a>
</nav>
