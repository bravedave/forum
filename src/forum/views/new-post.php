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

use strings;  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="link">
  <input type="hidden" name="action" value="post-new">

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header bg-secondary text-white py-2">
          <h5 class="modal-title" id="<?= $_modal ?>Label">New Topic</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="form-row mb-2">
            <div class="col">
              <input type="text" class="form-control" name="description" placeholder="topic" required>

            </div>

            <div class="col-md-3">
              <div class="input-group">
                <input type="text" class="form-control" name="tag" id="<?= $_tag = strings::rand() ?>" placeholder="tag" required>

                <div class="input-group-append">
                  <button class="btn btn-light dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                  <div class="dropdown-menu dropdown-menu-right" id="<?= $_tags = strings::rand() ?>"></div>

                </div>

              </div>

              <script>
                ( _ => {
                  $(<?= json_encode( $this->data->tags) ?>).each( (i, tag) => {
                    let a = $('<a class="dropdown-item" href="#"></a');

                    a
                    .html( tag.tag)
                    .on( 'click', function( e) {
                      $('#<?= $_tag ?>').val( this.innerHTML);

                      if ( !_brayworth_.browser.isMobileDevice) {
                        $('#<?= $_tag ?>').focus().select();

                      }

                    })
                    .appendTo( '#<?= $_tags ?>');

                  });

                }) (_brayworth_);

              </script>

            </div>

            <div class="col-md-3">
              <select class="form-control" title="priority" name="priority" required>
                <option value="<?= config::FORUM_BROKEN_PRIORITY ?>">broken</option>
                <option value="<?= config::FORUM_URGENT_PRIORITY ?>">urgent</option>
                <option value="<?= config::FORUM_HIGH_PRIORITY ?>">high</option>
                <option value="<?= config::FORUM_MEDIUM_PRIORITY ?>">medium</option>
                <option value="<?= config::FORUM_NORMAL_PRIORITY ?>" selected>normal</option>
                <option value="<?= config::FORUM_LOW_PRIORITY ?>">low</option>

              </select>

            </div>

          </div>

          <div class="form-row mb-2">
            <div class="col">
              <textarea class="form-control" name="comment" rows="14"></textarea>

            </div>

          </div>

          <div class="form-row">
            <div class="col">
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">Notify</div>
                </div>

                <select class="form-control" id="<?= $_uid = strings::rand()  ?>">
                  <option value="">select person to notify</option>
                </select>
                <script>
                ( _ => {
                  _cms_.getActiveUsers().then( d => {
                    $.each( d, (i,u) => {
                      $('#<?= $_uid ?>').append(  $('<option></option>').val( u.email).html( u.email));

                    });

                    $('#<?= $_uid ?>').on( 'change', function( e) {
                      if ( this.selectedIndex > 0) {

                        let _me = $(this);

                        let col = $('<div class="col"></div>').prependTo( _me.closest('.form-row'));
                        $('<input type="hidden" name="notify[]" />').val( $(this).val()).appendTo( col);

                        let ig = $( '<div class="input-group"></div>').appendTo( col);
                        $('<input type="text" class="form-control">').val( $(this).val()).appendTo( ig);
                        $('<div class="input-group-append pointer"><button type="button" class="btn input-group-text">&times;</button></div>')
                        .appendTo( ig)
                        .on( 'click', e => {
                          e.stopPropagation();;
                          col.remove();

                        });

                      }

                    })

                  });

                }) (_brayworth_);
                </script>

              </div>

            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">close</button>
          <button type="submit" class="btn btn-primary">Save</button>

        </div>

      </div>

    </div>

  </div>

  <script>
  ( _ => $(document).ready( () => {
    $('#<?= $_modal ?>').on( 'shown.bs.modal', e => {
      _cms_.editor.init( $('textarea[name="comment"]', '#<?= $_form ?>'), {
        statusbar : false,

      })
      .then( () => {
        if ( '' == $('input[name="description"]', '#<?= $_modal ?>').val()) {
          $('input[name="description"]', '#<?= $_modal ?>').focus();

        }
        else {
          tinyMCE.execCommand('mceFocus', false, $('textarea[name="comment"]', '#<?= $_modal ?>').prop('id'));

        }

      });

    });

    $('#<?= $_form ?>')
    .on( 'submit', function( e) {
      tinyMCE.triggerSave();

      let _form = $(this);
      let _data = _form.serializeFormJSON();

      $('#<?= $_modal ?> .modal-footer .alert').remove();

      if ( '' == _data.comment) {
        $('#<?= $_modal ?> .modal-footer').prepend( '<div class="alert alert-warning mr-auto">please enter description</div>');
        return false;

      }

      // console.log( _data);
      _.post({
        url : _.url('<?= $this->route ?>'),
        data : _data,

      }).then( d => {
          if ( 'ack' == d.response) {
            $('#<?= $_modal ?>').trigger( 'success', _data);

          }
          else {
            _.growl(d);

          }

          $('#<?= $_modal ?>').modal( 'hide');

      });

      return false;

    });

  }))( _brayworth_);
  </script>

</form>
