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

use dvc\bs;

?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="link">
  <input type="hidden" name="forum_idea_id" value="0">
  <input type="hidden" name="action" value="post-new">

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header bg-secondary text-white py-2">
          <h5 class="modal-title" id="<?= $_modal ?>Label">New Topic</h5>
          <button type="button" class="close" <?= bs::data('dismiss', 'modal') ?> aria-label="Close">
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
                  <button class="btn btn-light dropdown-toggle" type="button" <?= bs::data('toggle', 'dropdown') ?> aria-haspopup="true" aria-expanded="false"></button>
                  <div class="dropdown-menu dropdown-menu-right" id="<?= $_tags = strings::rand() ?>"></div>

                </div>

              </div>

              <script>
                (_ => {
                  $(<?= json_encode($this->data->tags) ?>).each((i, tag) => {
                    let a = $('<a class="dropdown-item" href="#"></a');

                    a
                      .html(tag.tag)
                      .on('click', function(e) {
                        $('#<?= $_tag ?>').val(this.innerHTML);

                        if (!_brayworth_.browser.isMobileDevice) {
                          $('#<?= $_tag ?>').focus().select();
                        }
                      })
                      .appendTo('#<?= $_tags ?>');
                  });
                })(_brayworth_);
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
              <textarea class="form-control" name="comment" rows="14" id="<?= $_uidComment = strings::rand() ?>"></textarea>
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
                  (_ => {
                    _.post({
                      url: _.url('<?= $this->route ?>'),
                      data: {
                        action: 'get-active-users'

                      },

                    }).then(d => {
                      if ('ack' == d.response) {
                        $.each(d.data, (i, u) => {
                          $('#<?= $_uid ?>').append($('<option></option>').val(u.email).html(u.email));

                        });

                        $('#<?= $_uid ?>').on('change', function(e) {
                          if (this.selectedIndex > 0) {

                            let _me = $(this);

                            let col = $('<div class="col"></div>').prependTo(_me.closest('.form-row'));
                            $('<input type="hidden" name="notify[]">').val($(this).val()).appendTo(col);

                            let ig = $('<div class="input-group"></div>').appendTo(col);
                            $('<input type="text" class="form-control">').val($(this).val()).appendTo(ig);
                            $('<div class="input-group-append pointer"><button type="button" class="btn input-group-text">&times;</button></div>')
                              .appendTo(ig)
                              .on('click', e => {
                                e.stopPropagation();;
                                col.remove();

                              });

                          }

                        });

                      } else {
                        _.growl(d);

                      }

                    });

                  })(_brayworth_);
                </script>

              </div>

            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" <?= bs::data('dismiss', 'modal') ?>>close</button>
          <button type="submit" class="btn btn-primary">Save</button>

        </div>

      </div>

    </div>

  </div>

  <script>
    (_ => $(document).ready(() => {
      $('#<?= $_modal ?>')
        .on('init-tinymce', e => {
          // inline: true,
          let options = {
            browser_spellcheck: true,
            font_formats: "Andale Mono=andale mono,times;" +
              "Arial=arial,helvetica,sans-serif;" +
              "Arial Black=arial black,avant garde;" +
              "Century Gothic=century gothic,arial,helvetica,sans-serif;" +
              "Comic Sans MS=comic sans ms,sans-serif;" +
              "Courier New=courier new,courier;" +
              "Helvetica=helvetica;" +
              "Impact=impact,chicago;" +
              "Symbol=symbol;" +
              "Tahoma=tahoma,arial,helvetica,sans-serif;" +
              "Terminal=terminal,monaco;" +
              "Times New Roman=times new roman,times;" +
              "Trebuchet MS=trebuchet ms,geneva;" +
              "Verdana=verdana,geneva;" +
              "Webdings=webdings;" +
              "Wingdings=wingdings,zapf dingbats",
            branding: false,
            document_base_url: _.url('', true),
            menubar: false,
            selector: '#<?= $_uidComment ?>',
            paste_data_images: true,
            relative_urls: false,
            remove_script_host: false,
            setup: ed => {
              ed.on('keydown', e => {
                if (e.keyCode == 9) { // tab pressed
                  if (e.shiftKey)
                    ed.execCommand('Outdent');
                  else
                    ed.execCommand('Indent');

                  e.preventDefault();
                  return false;

                }

              });

              ed.on('init', e => _.hourglass.off());
              ed.on('blur', e => tinyMCE.triggerSave());

            }

          };

          options = _.extend(options, {
            plugins: [
              'paste',
              'imagetools',
              'table',
              'autolink',
              'lists',
              'link',

            ],
            statusbar: false,
            toolbar: 'undo redo | bold italic | bullist numlist outdent indent blockquote table link mybutton | styleselect fontselect fontsizeselect | forecolor backcolor',
            contextmenu: 'paste | inserttable | cell row column deletetable',

          });

          tinymce.init(options);

        })
        .on('shown.bs.modal', e => {
          _.hourglass.on();
          _.tiny().then(() => $('#<?= $_modal ?>').trigger('init-tinymce'));

        });

      $('#<?= $_form ?>')
        .on('submit', function(e) {
          tinyMCE.triggerSave();

          let _form = $(this);
          let _data = _form.serializeFormJSON();

          $('#<?= $_modal ?> .modal-footer .alert').remove();

          if ('' == _data.comment) {
            $('#<?= $_modal ?> .modal-footer').prepend('<div class="alert alert-warning mr-auto">please enter description</div>');
            return false;

          }

          // console.log( _data);
          _.post({
            url: _.url('<?= $this->route ?>'),
            data: _data,

          }).then(d => {
            if ('ack' == d.response) {
              $('#<?= $_modal ?>').trigger('success', _data);

            } else {
              _.growl(d);

            }

            $('#<?= $_modal ?>').modal('hide');

          });

          return false;

        });

    }))(_brayworth_);
  </script>

</form>