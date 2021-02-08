<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
**/

use dvc\theme;
?>

<nav class="<?= theme::navbar() ?>" role="navigation" >
	<div class="container-fluid">
    <div class="navbar-brand" ><?= $this->data->title	?></div>

    <ul class="ml-auto navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="<?= strings::url('forum') ?>">
          <i class="bi bi-share"></i> Forum

        </a>

      </li>

      <li class="nav-item dropdown">
        <a class="nav-link pb-0 dropdown-toggle" href="#" id="navbarDropdown" role="button" aria-label="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <?= dvc\icon::get( dvc\icon::gear ) ?>

        </a>

        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="<?= strings::url('people') ?>">People</a>
          <a class="dropdown-item" href="<?= strings::url('properties') ?>">Properties</a>
          <a class="dropdown-item" href="<?= strings::url('sms') ?>">SMS</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="<?= strings::url('beds') ?>">Beds</a>
          <a class="dropdown-item" href="<?= strings::url('baths') ?>">Baths</a>
          <a class="dropdown-item" href="<?= strings::url('property_type') ?>">Property Type</a>
          <a class="dropdown-item" href="<?= strings::url('postcodes') ?>">Postcodes</a>

        </div>

      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?= strings::url() ?>">
          <i class="bi bi-house"></i>
          <span class="sr-only">Home</span>

        </a>

      </li>

      <li class="nav-item">
        <a class="nav-link" href="https://github.com/bravedave/">
          <i class="bi bi-github"></i>
          <span class="sr-only">GitHub</span>

        </a>

      </li>

    </ul>

  </div>

</nav>
