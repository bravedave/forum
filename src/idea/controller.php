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

use Json;
use strings;

class controller extends \Controller {

  protected function _index() {
    $this->data = (object)[
      'title' => $this->title = config::label,
    ];

    $this->render([
      'title' => $this->title = config::label,
      'primary' => ['matrix'],
      'secondary' => ['index'],
      'data' => (object)[
        'searchFocus' => false,
        'pageUrl' => strings::url($this->route)
      ]
    ]);
  }

  protected function before() {
    config::idea_checkdatabase();

    parent::before();
    $this->viewPath[] = __DIR__ . '/views/';
  }

  protected function postHandler() {
    $action = $this->getPost('action');

    switch ($action) {
      case 'get-by-id':
        if ($id = (int)$this->getPost('id')) {
          $dao = new dao\forum_idea;
          Json::ack($action)
            ->add('dto', $dao->getByID($id));
        } else {
          Json::nak($action);
        }
        break;

      case 'get-forum-ideas':
        $dao = new dao\forum_idea;
        Json::ack($action)
          ->add('data', $dao->getMatrix());
        break;

      case 'idea-save':
        $a = [
          'idea' => $this->getPost('idea'),
          'data' => $this->getPost('data')
        ];

        $dao = new dao\forum_idea;
        if ($id = (int)$this->getPost('id')) {
          $dao->UpdateByID($a, $id);
        } else {
          $id = $dao->Insert($a);
        }

        Json::ack($action)
          ->add('id', $id);

        break;

      default:
        parent::postHandler();
        break;
    }
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
      $dao = new dao\forum_idea;
      if ($dto = $dao->getByID($id)) {
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
