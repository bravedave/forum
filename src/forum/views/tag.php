<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

/**
 * replace:
 * [x] data-dismiss => data-bs-dismiss
 * [x] data-toggle => data-bs-toggle
 * [x] data-parent => data-bs-parent
 * [x] text-right => text-end
 * [x] custom-select - form-select
 * [x] mr-* => me-*
 * [x] ml-* => ms-*
 * [x] pr-* => pe-*
 * [x] pl-* => ps-*
 * [x] input-group-prepend - remove
 * [x] input-group-append - remove
 * [x] btn input-group-text => btn btn-light
 * [x] form-row => row g-2
 */

namespace dvc\forum;

use cms\theme; ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="id" value="<?= $dto->id ?>">
  <input type="hidden" name="action" value="save-tag">

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">

      <div class="modal-content">

        <div class="modal-header <?= theme::modalHeader() ?> py-2">

          <h5 class="modal-title" id="<?= $_modal ?>Label">Add Tag</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>

        <div class="modal-body">

          <div class="mb-2">

            <label for="<?= $_uidTag = strings::rand() ?>">Tag:</label>
            <input type="text" name="tag" id="<?= $_uidTag ?>" class="form-control" value="<?= $dto->tag ?>">
          </div>

          <div class="text-center" id="<?= $_uidTag ?>tagbox"></div>
        </div>

        <div class="modal-footer">

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

        $(<?= json_encode($tags) ?>).each((i, tag) => {
          let btn = $('<button type="button" class="btn btn-sm btn-light m-1"></button>');

          btn
            .html(tag.tag)
            .appendTo('#<?= $_uidTag ?>tagbox')
            .on('click', function(e) {
              e.stopPropagation();
              $('#<?= $_uidTag ?>').val(this.innerHTML);

              $('#<?= $_form ?>').submit();

            })

        });

        form.on('submit', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();

          $('button[type="submit"]', this).html(
            '<div class="spinner-grow spinner-grow-sm" role="status"><span class="visually-hidden">Saving...</span></div>')

          _.post({
              url: _.url('<?= $this->route ?>'),
              data: _data

            })
            .done(d => {
              if ('ack' == d.response) {
                $('#<?= $_modal ?>').trigger('success', _data);

              } else {
                _.growl(d);

              }

              $('#<?= $_modal ?>').modal('hide');

            });


          // console.table( _data);

          return false;
        });

        // set focus
        $('#<?= $_uidTag ?>').focus();
      });
    })(_brayworth_);
  </script>
</form>