<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\idea\dao;

use db;
use dvc\dao\_dao;

class forum_idea extends _dao {
  protected $_db_name = 'forum_idea';
  protected $template = __NAMESPACE__ . '\dto\forum_idea';

  public function getMatrix(): array {
    if ($res = $this->getAll('`id`, `idea`, `data`, `tag`, `updated`', 'ORDER BY idea ASC')) {
      return $res->dtoSet();
    }

    return [];
  }

  public function getRichData(dto\forum_idea $dto): dto\forum_idea {

    $sql = sprintf(
      'SELECT
        `id`,
        `description`,
        `comment`,
        `resolved`,
        `complete`
      FROM forum
      WHERE `forum_idea_id` = %d',
      $dto->id
    );

    if ($res = $this->Result($sql)) {
      $dto->forum = $res->dtoSet();
    }

    return $dto;
  }

  public function Insert($a) {
    $a['created'] = $a['updated'] = db::dbTimeStamp();
    return parent::Insert($a);
  }

  public function UpdateByID($a, $id) {
    $a['updated'] = db::dbTimeStamp();
    return parent::UpdateByID($a, $id);
  }
}
