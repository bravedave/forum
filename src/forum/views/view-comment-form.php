<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
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

extract((array)$this->data);
?>

<input type="hidden" name="form_action" value="post comment">
<div id="commentForm" class="d-print-none" data-role="comment-container" data-master="yes">
  <div class="row g-2">
    <div class="col ps-md-0">
      <i id="<?= $_uidShowCommentBox = strings::rand() ?>" class="bi bi-caret-right"></i>
      Add your Comment
    </div>
  </div>

  <div class="row g-2 d-none" data-comment-body>

    <div class="col ps-md-0">

      <input type="hidden" name="parent" value="<?= $dto->id ?>" />
      <input type="hidden" id="commentThread" name="thread" data-default="<?= $dto->id ?>"
        value="<?= $dto->id ?>" />

      <div class="mb-2">

        <textarea data-role="comment" name="comment" id="<?= $uid = strings::rand() ?>" class="form-control" rows="16"
          placeholder="add your comment"></textarea>
      </div>

      <button type="submit" class="btn btn-primary" id="<?= $uid ?>form_button">post comment</button>

      use <span class="user-select-all fw-bold">{topic:&lt;id&gt;}</span> to reference a topic
    </div>
  </div>
</div>
<script>
  (_ => {
    let editorDone = false;

    $('#<?= $_uidShowCommentBox ?>')
      .parent()
      .addClass('pointer')
      .on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        let _el = $('#<?= $_uidShowCommentBox ?>');

        if (_el.data('state') == 'open') {

          _el
            .removeClass('bi-caret-down')
            .addClass('bi-caret-right')
            .data('state', 'hidden');
          $('#commentForm [data-comment-body]').addClass('d-none');
        } else {

          _el
            .removeClass('bi-caret-right')
            .addClass('bi-caret-down')
            .data('state', 'open');
          $('#commentForm [data-comment-body]').removeClass('d-none');

          if (!editorDone) {
            editorDone = true;

            _.tiny().then(() => {
              let j = {
                browser_spellcheck: true,
                deprecation_warnings: false,
                document_base_url: _.url('', true),
                menubar: false,
                paste_data_images: true,
                plugins: 'paste imagetools table autolink lists link',
                selector: '#<?= $uid ?>',
                statusbar: false,
                toolbar: 'undo redo | done | bold italic | bullist numlist outdent indent blockquote link',
              };

              if (_.browser.isMobileDevice) {

                // https://cmss.darcy.com.au/forum/view/11879
                j.plugins = 'lists autolink';
              }

              j.setup = ed => {

                ed.ui.registry.addButton('done', {
                  tooltip: 'all done',
                  text: '&check;',
                  onAction: () => ed.insertContent('done, verify and mark complete')
                })
              };

              tinymce.init(j);
            });
          }
        }
      });
  })(_brayworth_);
</script>