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

use strings;
use theme;

extract((array)$this->data);  ?>
<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="id" value="<?= $dto->id ?>">
  <input type="hidden" name="action" value="idea-save">

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-1">

          <div class="container-fluid">
            <div class="form-row">
              <div class="col mb-2">
                <label for="<?= $_uid = strings::rand() ?>">idea</label>
                <textarea class="form-control" id="<?= $_uid ?>" name="idea"><?= $dto->idea ?></textarea>
                <em class="text-muted">what is the general idea, what value does this have ?</em>
              </div>
            </div>

            <div class="form-row">
              <div class="col mb-2">
                <label for="<?= $_uid = strings::rand() ?>">data</label>
                <textarea class="form-control" id="<?= $_uid ?>" name="data"><?= $dto->data ?></textarea>
                <em class="text-muted">where will the data come from ?</em>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer p-1">
          <input type="text" class="form-control w-auto mr-auto" name="tag" value="<?= $dto->tag ?>" placeholder="tag" required>
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
      $('#<?= $_form ?> textarea').autoResize();

      $('#<?= $_form ?>')
        .on('submit', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();

          _.post({
            url: _.url('<?= $this->route ?>'),
            data: _data,

          }).then(d => {
            if ('ack' == d.response) {
              $('#<?= $_modal ?>')
                .trigger('success', d)
                .modal('hide');
            } else {
              _.growl(d);
            }
          });

          return false;
        });
    }))(_brayworth_);
  </script>
</form>