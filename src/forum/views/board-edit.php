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

use theme;

/** @var dao\dto\forum_board $dto */
extract((array)($this->data ?? []));  ?>
<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="action" value="board-save">
  <input type="hidden" name="id" value="<?= $dto->id ?>">

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $title ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <div class="row g-2">
            <div class="col-2 col-form-label">
              name
            </div>
            <div class="col mb-2">
              <input type="text" name="name" class="form-control" placeholder="name"
                value="<?= $dto->name ?>" required>
            </div>
          </div>

          <div class="row g-2">
            <div class="col-2 col-form-label">
              status
            </div>
            <div class="col mb-2">
              <select name="status" class="form-select">
                <?php
                foreach (config::board_status_text as $key => $value) {

                  printf(
                    '<option value="%s" %s>%s</option>',
                    $key,
                    ($key == $dto->status) ? 'selected' : '',
                    $value
                  );
                } ?>
              </select>
            </div>
          </div>

          <div class="row g-2">
            <div class="col-2 col-form-label">
              priority
            </div>
            <div class="col mb-2">
              <select name="priority" class="form-select">
                <?php
                foreach (config::board_priority_text as $key => $value) {

                  printf(
                    '<option value="%s" %s>%s</option>',
                    $key,
                    ($key == $dto->priority) ? 'selected' : '',
                    $value
                  );
                } ?>
              </select>
            </div>
          </div>

          <div class="row g-2">
            <div class="col-2 col-form-label">
              assigned
            </div>
            <div class="col mb-2">
              <select name="assigned_user_id" class="form-select">
                <option value="0">unassigned</option>
                <?php
                if ($users) {
                  foreach ($users as $user) {
                    printf(
                      '<option value="%s" %s>%s</option>',
                      $user->id,
                      ($user->id == $dto->assigned_user_id) ? 'selected' : '',
                      $user->name
                    );
                  }
                } ?>
              </select>
            </div>
          </div>

          <div class="row g-2">

            <div class="offset-2 col pt-2 mb-2">
              <div class="form-check">

                <input type="checkbox" class="form-check-input" name="idea" id="<?= $_uid = strings::rand() ?>" value="1" <?= $dto->idea ? 'checked' : '' ?>>
                <label class="form-check-label" for="<?= $_uid ?>">
                  idea created
                </label>

              </div>
            </div>
          </div>

          <div class="row g-2">
            <div class="col-2 col-form-label">
              link
            </div>
            <div class="col mb-2">
              <input type="text" name="link" class="form-control" placeholder="link"
                value="<?= $dto->link ?>">
            </div>
          </div>

          <div class="row g-2">

            <div class="col mb-2">
              <textarea name="description" class="form-control"
                placeholder="description"><?= $dto->description ?></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">

          <div class="form-check form-check-inline">

            <input type="checkbox" class="form-check-input" name="archived" id="<?= $_uid = strings::rand() ?>" value="1" <?= $dto->archived ? 'checked' : '' ?>>
            <label class="form-check-label" for="<?= $_uid ?>">
              archived
            </label>
          </div>

          <div class="js-message"></div>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (_ => {
      const form = $('#<?= $_form ?>');
      const modal = $('#<?= $_modal ?>');

      const msg = txt => {

        let ctl = $('.js-message').html(txt);
        ctl[0].className = 'me-auto js-message small p-2';
        return ctl;
      };

      const alert = txt => msg(txt).addClass('alert alert-warning');

      modal.on('shown.bs.modal', () => {

        modal.find('textarea').autoResize();

        form
          .on('submit', function(e) {
            let _form = $(this);
            let _data = _form.serializeFormJSON();

            // console.table( _data);
            _.fetch
              .post.form(_.url('<?= $this->route ?>'), this)
              .then(d => {

                if ('ack' == d.response) {

                  modal.trigger('success');
                  modal.modal('hide');
                } else {

                  _.growl(d);
                }
              });

            return false;
          });
      });
    })(_brayworth_);
  </script>
</form>