<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\idea;

use strings;  ?>

<ul class="nav flex-column">
  <li class="nav-item">
    <a class="h5" href="<?= strings::url($this->route) ?>"><?= config::label ?></a>
  </li>

</ul>