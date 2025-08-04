<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\forum;

use bravedave\dvc\{json, ServerRequest, fileUploader};
use cms\currentUser;
use RuntimeException;

final class handler {

  /**
   * Handles the upload of attachments to a forum topic.
   *
   * This method processes uploaded files from a server request, validates the input,
   * and stores the file in the appropriate location if valid. It also ensures
   * that the forum topic exists and provides feedback on the success or failure
   * of the operation.
   *
   * @param ServerRequest $request The server request containing form data and uploaded files.
   *
   * @return json A JSON response indicating the result of the operation, including
   *              success or error messages.
   */
  public static function forumAttachmentUpload(ServerRequest $request): json {

    $action = $request('action');

    $uploadedFiles = $request->getUploadedFiles();
    if (!empty($uploadedFiles)) {

      $file = array_shift($uploadedFiles); // 1 file
      $id = (int)$request('id');
      if ($id > 0) {

        $dao = new dao\forum;
        if ($dto = $dao->getById($id)) {

          if ($store = $dao->store($dto->id)) {

            $uploader = new fileUploader([
              'path' => $store,
              'accept' => [
                'image/png',
                'image/x-png',
                'image/jpeg',
                'image/pjpeg',
                'application/pdf',
                'text/csv',
                'text/plain'
              ]
            ]);

            return $uploader->save($file) ? json::ack($action) : json::nak($action);
          }

          return json::nak('store not found - ' . $action);
        }

        return json::nak('forum topic not found - ' . $action);
      }

      return json::nak('missing id - ' . $action);
    }

    return json::nak('no files - ' . $action);
  }

  /**
   * Handles the posting of a comment to a forum thread.
   *
   * This method processes a comment submission, validates the input,
   * and inserts the comment into the database if valid. It also ensures
   * that the parent forum thread exists and throws exceptions for invalid
   * or missing data.
   *
   * @param ServerRequest $request The server request containing form data.
   *
   * @return object An object containing the result of the operation, including
   *                the parent forum ID and a success or error message.
   *
   * @throws RuntimeException If the parent forum thread is not found or
   *                          if the forum ID is not provided.
   */
  public static function postComment(ServerRequest $request): object {

    $action = $request('form_action');
    if ($parent = (int)$request('parent')) {

      $dao = new dao\forum;
      if ($dtoP = $dao->getById($parent)) {

        $dto = new dao\dto\forum;
        $dto->comment = $request('comment');
        if (empty($dto->comment)) {

          return (object)[
            'id' => $dtoP->id,
            'message' => 'Comment cannot be empty',
          ];
        } else {

          $dto->description = $dtoP->description;
          $dto->parent = $dtoP->id;
          $dto->thread = $request('thread');
          $dto->notify = $dtoP->notify;
          if ($dao->InsertDTO($dto)) {

            return (object)[
              'id' => $dtoP->id,
              'message' => 'Comment added successfully',
            ];
          }

          throw new RuntimeException('failed to add comment');
        }
      }

      throw new RuntimeException('Could not find Forum to comment on');
    }

    throw new RuntimeException('Forum not identified to comment on');
  }

  /**
   * Handles the action to show only the user's own forum topics.
   *
   * This method updates the user's preference for showing only their own topics
   * and returns a JSON response indicating success or failure.
   *
   * @param ServerRequest $request The server request containing the action and state.
   *
   * @return json A JSON response indicating the result of the operation.
   */
  public static function showMine(ServerRequest $request): json {

    $action = (string)$request('action');
    $state = $request('state') ?? '';

    currentUser::option('forum-showOnlyMine', $state);
    return json::ack($action);
  }
}
