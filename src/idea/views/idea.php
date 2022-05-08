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

use dvc\forum\strings;

extract((array)$this->data);  ?>

<form id="<?= $_form = strings::rand()  ?>">
  <style>
    .resolved.complete {
      border-color: green;
    }

    .resolved {
      color: #155724;
      background: #d4edda linear-gradient(180deg, #daf0e0, #d4edda) repeat-x;
      border-color: #c3e6cb;
    }
  </style>
  <div class="form-row">
    <div class="col border p-2"><?= strings::text2html($dto->data) ?></div>
  </div>

  <div class="form-row small border-bottom">
    <div class="col">description</div>
    <div class="col-1 text-center text-truncate">resolved</div>
    <div class="col-1 text-center text-truncate">complete</div>
  </div>

  <?php foreach ($dto->forum as $forum) {
    printf(
      '<div class="form-row js-forum-item border-bottom"
        data-id="%d">
        <div class="col py-1">%s - <em class="text-muted">%s</em></div>
        <div class="col-1 text-center py-1">
          <input type="checkbox" class="js-forum-item-resolved" %s>
        </div>
        <div class="col-1 text-center py-1">
          <input type="checkbox" class="js-forum-item-complete" %s>
        </div>

      </div>',
      $forum->id,
      $forum->description,
      strings::brief($forum->comment),
      $forum->resolved ? 'checked' : '',
      $forum->complete ? 'checked' : ''
    );
  } ?>
  <script>
    (_ => {
      const _form = '#<?= $_form ?>';

      $(document).ready(() => {
        $('.js-forum-item-resolved', _form)
          .on('click', e => e.stopPropagation())
          .on('change', function(e) {
            const _$ = $(this);
            const _row = _$.closest('.js-forum-item');

            // console.log(this);

            _.post({
              url: _.url('forum'),
              data: {
                action: 'set-resolved',
                id: _row.data('id'),
                val: this.checked ? 1 : 0
              },
            }).then(d => {
              _.growl(d);
              if (this.checked) {
                _row.addClass('resolved');
              } else {
                _row.removeClass('resolved');
              }
            });
          });

        $('.js-forum-item-complete', _form)
          .on('click', e => e.stopPropagation())
          .on('change', function(e) {
            const _$ = $(this);
            const _row = _$.closest('.js-forum-item');

            // console.log(this);

            _.post({
              url: _.url('forum'),
              data: {
                action: this.checked ? 'mark-complete' : 'mark-incomplete',
                id: _row.data('id')
              },
            }).then(d => {
              _.growl(d);
              if (this.checked) {
                _row.addClass('complete');
              } else {
                _row.removeClass('complete');
              }
            });
          });

        $('.js-forum-item-resolved:checked', _form)
          .each((i, chk) => $(chk).closest('.js-forum-item').addClass('resolved'));
        $('.js-forum-item-complete:checked', _form)
          .each((i, chk) => $(chk).closest('.js-forum-item').addClass('complete'));

        $('.js-forum-item', _form)
          .addClass('pointer')
          .on('click', function(e) {
            const _$ = $(this);
            _.get.modal(_.url(`forum/edit/${_$.data('id')}`))
              .then(m => m.on('success', d => $('.js-idea-viewer').trigger('refresh')));
          });

        document.title = <?= json_encode(sprintf('%s - %s', config::label_view, strings::brief($dto->idea, 50))); ?>;

      });
    })(_brayworth_);
  </script>

</form>