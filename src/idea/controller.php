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

use bravedave\dvc\ServerRequest;
use cms\strings;

class controller extends \Controller {

  protected function _index() {

    $aside = array_merge(['index'], config::index_set);
    $this->data = (object)[
      'aside' => $aside,
      'pageUrl' => strings::url($this->route),
      'searchFocus' => false,
      'title' => $this->title = config::label,
    ];

    $this->renderBS5([
      'main' => fn() => $this->load('matrix')
    ]);
  }

  protected function before() {

    config::idea_checkdatabase();
    $this->viewPath[] = __DIR__ . '/views/';
    parent::before();
  }

  protected function postHandler() {

    $request = new ServerRequest;
    $action = $request('action');

    return match ($action) {
      'get-by-id' => handler::getByID($request),
      'get-forum-ideas' => handler::getForumIdeas($request),
      'idea-save' => handler::ideaSave($request),
      default => parent::postHandler()
    };
  }

  public function edit(int $id = 0) {

    if ($id) {

      $dao = new dao\forum_idea;
      if ($dto = $dao->getByID($id)) {

        $this->data = (object)[
          'title' => $this->title = config::label_edit,
          'dto' => $dto,
        ];
        $this->load('edit');
      } else {

        $this->data = (object)[
          'title' => $this->title = config::label_not_found,
          'message' => config::label_not_found,
        ];
        $this->load('modal-error');
      }
    } else {

      $this->data = (object)[
        'title' => $this->title = config::label_edit,
        'dto' => new dao\dto\forum_idea
      ];
      $this->load('edit');
    }
  }

  public function view(int $id = 0) {

    if ($id) {

      if ($dto = (new dao\forum_idea)($id)) {

        $this->data = (object)[
          'title' => $this->title = config::label_view,
          'dto' => $dto,
        ];
        $this->load('idea');
      } else {

        print 'not found';
      }
    } else {

      print 'invalid id';
    }
  }
}
