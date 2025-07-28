<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
**/

use cms\{strings, theme};

use dvc\bs;
?>

<nav class="<?= theme::navbar() ?>" role="navigation">
  <div class="container-fluid">

    <div class="navbar-brand"><?= $title  ?></div>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
      data-bs-target="#<?= $_uid = strings::rand()  ?>" aria-controls="<?= $_uid ?>"
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="<?= $_uid ?>">

      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item">
          <a class="nav-link" href="<?= strings::url('forum') ?>">
            <i class="bi bi-share"></i> forum
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="<?= strings::url('idea') ?>">
            <i class="bi bi-lightbulb"></i> iDEA
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="<?= $_uid ?>-dropdown" role="button"
            aria-label="dropdown" <?= bs::data('toggle', 'dropdown') ?> aria-haspopup="true" aria-expanded="false">
            <i class="bi bi-gear"></i>

          </a>

          <div class="dropdown-menu" aria-labelledby="<?= $_uid ?>-dropdown">
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

          <a class="nav-link" target="_blank" href="https://github.com/bravedave/">
            <i class="bi bi-github"></i>
            <span class="sr-only">GitHub</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>