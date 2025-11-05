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

use bravedave\dvc\{json, ServerRequest};

final class handler {

  public static function getByID(ServerRequest $request): json {

    $action = $request('action'); // 'get-by-id';
    if ($id = (int)$request('id')) {

      return json::ack($action)
        ->add('dto', (new dao\forum_idea)->getByID($id));
    }

    return json::nak($action);
  }

  public static function getForumIdeas(ServerRequest $request): json {

    $action = $request('action'); // 'get-forum-ideas';

    $dao = new dao\forum_idea;
    return json::ack($action, $dao->getMatrix());
  }

  public static function ideaSave(ServerRequest $request): json {

    $action = $request('action'); // 'idea-save';

    $a = [
      'idea' => (string)$request('idea'),
      'data' => (string)$request('data'),
      'tag' => (string)$request('tag')
    ];

    $dao = new dao\forum_idea;
    if ($id = (int)$request('id')) {

      $dao->UpdateByID($a, $id);
    } else {

      $id = $dao->Insert($a);
    }

    return json::ack($action)->add('id', $id);
  }
}
