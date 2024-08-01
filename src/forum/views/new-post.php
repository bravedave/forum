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

use cms\{currentUser, theme};

extract((array)($this->data ?? [])); ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="link">
  <input type="hidden" name="forum_idea_id" value="0">
  <input type="hidden" name="action" value="post-new">

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label"
    aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-md-down modal-xl modal-dialog-centered"
      role="document">

      <div class="modal-content">

        <div class="modal-header <?= theme::modalHeader() ?>"></div>

        <div class="modal-body">

          <div class="row g-2">

            <div class="col mb-2">

              <input type="text" class="form-control" name="description" placeholder="topic"
                required>
            </div>

            <div class="col-md-3 mb-2">

              <div class="input-group">

                <input type="text" class="form-control" name="tag" id="<?= $_tag = strings::rand() ?>" placeholder="tag"
                  required>
                <button class="btn btn-light dropdown-toggle" type="button"
                  data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end" id="<?= $_tags = strings::rand() ?>">
                  <?php
                  array_walk($tags, fn ($tag) => printf(
                    '<a class="dropdown-item js-tags-suggestion" href="#">%s</a>',
                    $tag->tag
                  ));
                  ?>
                </div>
              </div>
            </div>

            <div class="col-md-3 mb-2">

              <select class="form-select" title="priority" name="priority" required>
                <option value="<?= config::FORUM_BROKEN_PRIORITY ?>">broken</option>
                <option value="<?= config::FORUM_URGENT_PRIORITY ?>">urgent</option>
                <option value="<?= config::FORUM_HIGH_PRIORITY ?>">high</option>
                <option value="<?= config::FORUM_MEDIUM_PRIORITY ?>">medium</option>
                <option value="<?= config::FORUM_NORMAL_PRIORITY ?>" selected>normal</option>
                <option value="<?= config::FORUM_LOW_PRIORITY ?>">low</option>
              </select>
            </div>
          </div>

          <div class="row g-2">
            <div class="col mb-2">
              <textarea class="form-control" name="comment" rows="20" id="<?= $_uidComment = strings::rand() ?>">
              <em>use this <strong>example</strong> template to describe your issue,
                <span style="color: red;">delete text as required</span></em>

              <h5 style="margin-bottom: 0;">Steps to Reproduce</h5>
              <ol style="margin-top: 5px;">
                <li>Sales &gt; PO6 Authority (example)</li>
                <li>Click an Authority</li>
              </ol>

              <h5 style="margin-bottom: 0;">Expected Result</h5>
              <ol style="margin-top: 5px;">
                <li>authority would open (example)</li>
              </ol>

              <h5 style="margin-bottom: 0;">Actual Result</h5>
              <ol style="margin-top: 5px;">
                <li>authority does not open (example)</li>
              </ol>

              <h5>Notes</h5>

            </textarea>
            </div>
          </div>

          <div class="row g-2">
            <div class="col mb-2">
              <div class="input-group">

                <div class="input-group-text">Notify</div>
                <select class="form-control js-users" id="<?= $_uid = strings::rand()  ?>">
                  <option value="">select person to notify</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <div class="js-message"></div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    (_ => {
      const modal = $('#<?= $_modal ?>');
      const form = $('#<?= $_form ?>');

      const msg = txt => {

        let ctl = $('.js-message').html(txt);
        ctl[0].className = 'me-auto js-message small p-2';
        return ctl;
      };

      const alert = txt => msg(txt).addClass('alert alert-warning');

      modal
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
            deprecation_warnings: false,
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

          if (_brayworth_.browser.isMobileDevice) {

            // https://cmss.darcy.com.au/forum/view/11879
            options.plugins = 'lists autolink';
          } else {

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
          }

          tinymce.init(options);
        })
        .on('shown.bs.modal', e => {

          _.hourglass.on();
          _.tiny().then(() => $('#<?= $_modal ?>').trigger('init-tinymce'));
        });

      _.fetch
        .post(_.url('<?= $this->route ?>'), {
          action: 'get-active-users'
        })
        .then(d => {

          if ('ack' == d.response) {
            let sel = form.find('.js-users');
            $.each(d.data, (i, u) => sel.append(`<option value="${u.email}">${u.name} &lt;${u.email}&gt;</option>`));

            sel.on('change', function(e) {

              if (this.selectedIndex > 0) {

                let _me = $(this);

                let col = $('<div class="col"></div>')
                  .prependTo(_me.closest('.row'));
                $('<input type="hidden" name="notify[]">')
                  .val($(this).val())
                  .appendTo(col);

                let ig = $('<div class="input-group"></div>')
                  .appendTo(col);

                $('<input type="text" class="form-control">')
                  .val($(this).val())
                  .appendTo(ig);

                $('<button type="button" class="btn btn-light">&times;</button>')
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

      form.find('.js-tags-suggestion').on('click', function(e) {
        form.find('input[name="tag"]').val(this.innerHTML);
      });

      form
        .on('submit', function(e) {
          tinyMCE.triggerSave();

          let _form = $(this);
          let _data = _form.serializeFormJSON();

          if ('' == _data.comment) {

            alert('please enter description');
            return false;
          }

          msg('saving ...');

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
    })(_brayworth_);
  </script>
</form>