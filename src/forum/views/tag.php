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

$dto = $this->data->dto;  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="id" value="<?= $dto->id ?>">
  <input type="hidden" name="action" value="save-tag">

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header bg-secondary text-white py-2">
          <h5 class="modal-title" id="<?= $_modal ?>Label">Add Tag</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-row mb-2">
            <div class="col">
              <label for="<?= $_uidTag = strings::rand() ?>">Tag:</label>

              <input type="text" name="tag" id="<?= $_uidTag ?>" class="form-control"
                value="<?= $this->data->dto->tag ?>" />

            </div>

          </div>

          <div class="row">
            <div class="col text-center" id="<?= $_uidTag ?>tagbox"></div>

          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save</button>

        </div>

      </div>

    </div>

  </div>
  <script>
    ( _ => $(document).ready( () => {

      $(<?= json_encode( $this->data->tags) ?>).each( (i, tag) => {
        let btn = $('<button type="button" class="btn btn-sm btn-outline-secondary m-1"></button>');

        btn
        .html( tag.tag)
        .appendTo( '#<?= $_uidTag ?>tagbox')
        .on( 'click', function( e) {
          e.stopPropagation();
          $('#<?= $_uidTag ?>').val( this.innerHTML);

          $('#<?= $_form ?>').submit();

        })

      });

      $('#<?= $_form ?>')
      .on( 'submit', function( e) {
        let _form = $(this);
        let _data = _form.serializeFormJSON();

        $('button[type="submit"]', this).html( '<div class="spinner-grow spinner-grow-sm" role="status"><span class="visually-hidden">Saving...</span></div>')

        _.post({
          url : _.url( '<?= $this->route ?>'),
          data : _data

        })
        .done( d => {
          if ( 'ack' == d.response) {
            $('#<?= $_modal ?>').trigger( 'success', _data);

          }
          else {
            _.growl(d);

          }

          $('#<?= $_modal ?>').modal( 'hide');

        });


        // console.table( _data);

        return false;
      });
    }))( _brayworth_);
  </script>
</form>
