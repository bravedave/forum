<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/
namespace dvc\idea\dao\dto;

use bravedave\dvc\dto;

class forum_idea extends dto {
  public $id = 0;

  public $created = 0;

  public $updated = 0;

  public $idea = '';

  public $data = '';

  public $tag = '';

  public $forum = []; // rich data

}