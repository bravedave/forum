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

class forum_board extends dto {

  public $id = 0;
  public $name = '';
  public $status = 0;
  public $assigned_user_id = 0;
  public $priority = '';
  public $description = '';
  public $idea = 0;
  public $link = '';
  public $user_id = 0;
  public $archived = 0;

  public $created = '';
  public $updated = '';
}
