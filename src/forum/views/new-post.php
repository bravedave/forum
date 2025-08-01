<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 */

namespace dvc\forum;

use cms\{currentUser, theme};

$template =
  '<h5 style="margin-bottom: 0;">Steps to Reproduce</h5>
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

  <div class="js-example">
  <em>use this <strong>example</strong> template to describe your issue,
    <span style="color: red;">delete text as required</span></em>
  </div>';
?>

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
                  array_walk($tags, fn($tag) => printf(
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
              <?php
              printf(
                '<textarea class="form-control" name="comment" id="%s" rows="20">%s</textarea>',
                $_uidComment = strings::rand(),
                $template
              ); ?>
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

        /**
         * https://cmss.darcy.com.au/forum/view/14817
         */
        let ctl = modal.find('.js-message');

        ctl.html(txt);
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

          _.tiny().then(() => tinymce.init(options));
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

            const sel = form.find('.js-users');
            $.each(d.data, (i, u) => sel
              .append(`<option value="${u.email}">${u.name} &lt;${u.email}&gt;</option>`));

            sel.on('change', function(e) {

              if (this.selectedIndex > 0) {

                const col = $(`<div class="col-auto">
                    <input type="hidden" name="notify[]" value="${this.value}">
                    <div class="input-group">
                      <div class="input-group-text">${this.value}</div>
                      <button type="button" class="btn btn-light">&times;</button>
                    </div>
                  </div>`);

                $(this).closest('.row').prepend(col);

                col.find('button').on('click', e => {

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

          /**
           * _data.comment contains html with the
           * <em class=".js-example">blah blah</em> text,
           * remove the tag and the containg text
           */

          // https://cmss.darcy.com.au/forum/view/15494
          // Notes not coming through on Forum posts
          // _data.comment = _data.comment.replace(/<div class="js-example">(.+?)<\/div>/, '');
          msg('saving ...');

          // console.log( _data);
          _.fetch.post.form(_.url('<?= $this->route ?>'), this)
            .then(d => {

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