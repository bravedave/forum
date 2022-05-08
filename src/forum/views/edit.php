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

extract((array)$this->data);  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="action" value="post-update">
  <input type="hidden" name="id" value="<?= $dto->id ?>">

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-2">
          <div class="container-fluid">

            <div class="form-row mb-2">
              <div class="col">
                <input type="text" class="form-control" name="description" placeholder="topic" value="<?= $dto->description ?>" required>
              </div>
            </div>

            <div class="form-row">
              <div class="col mb-2">
                <textarea class="form-control" name="comment" rows="10" required><?= $dto->comment ?></textarea>

              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {

      _.tiny().then(() => {
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
          selector: 'textarea[name="comment"]',
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

        options.plugins = [
          'paste',
          'imagetools',
          'table',
          'autolink',
          'lists',
          'link',
        ];

        options.statusbar = false;
        options.toolbar = 'undo redo | bold italic | bullist numlist outdent indent blockquote table link mybutton | styleselect fontselect fontsizeselect | forecolor backcolor';
        options.contextmenu = 'paste | inserttable | cell row column deletetable';

        tinymce.init(options);

      });

      $('#<?= $_form ?>')
        .on('submit', function(e) {
          tinyMCE.triggerSave();

          let _form = $(this);
          let _data = _form.serializeFormJSON();

          _.post({
            url: _.url('<?= $this->route ?>'),
            data: _data
          }).then(d => {
            if ('ack' == d.response) {
              $('#<?= $_modal ?>')
                .trigger('success')
                .modal('hide');
            } else {
              _.growl(d);

            }
          });

          // console.table( _data);

          return false;
        });
    }))(_brayworth_);
  </script>
</form>