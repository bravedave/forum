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

use function bravedave\dvc\text2html;

?>

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
    <div class="col border p-2"><?= text2html($dto->data) ?></div>
  </div>

  <div class="table-responsive">

    <table class="table table-sm" id="<?= $_table = strings::rand() ?>">

      <thead class="small">
        <tr>
          <td class="text-center js-line-number"></td>
          <td class="col">description</td>
          <td class="col-1 text-center text-truncate">resolved</td>
          <td class="col-1 text-center text-truncate">complete</td>
        </tr>
      </thead>

      <tbody>
        <?php array_walk($dto->forum, fn($forum) => printf(
          '<tr data-id="%d">
            <td class="text-center js-line-number small"></td>
            <td class="col py-1">%s - <em class="text-muted">%s</em></td>
            <td class="col-1 text-center py-1">
              <input type="checkbox" class="js-forum-item-resolved" %s>
            </div>
            <div class="col-1 text-center py-1">
              <input type="checkbox" class="js-forum-item-complete" %s>
            </div>
          </tr>',
          $forum->id,
          $forum->description,
          strings::brief($forum->comment),
          $forum->resolved ? 'checked' : '',
          $forum->complete ? 'checked' : ''
        )); ?>
      </tbody>
    </table>
  </div>

  <script>
    (_ => {
      const table = $('#<?= $_table ?>');
      const form = $('#<?= $_form ?>');

      form.find('.js-forum-item-resolved')
        .on('click', e => e.stopPropagation())
        .on('change', function(e) {
          const _$ = $(this);
          const tr = _$.closest('tr');

          // console.log(this);

          _.fetch.post(_.url('forum'), {
            action: 'set-resolved',
            id: tr[0].dataset.id,
            val: this.checked ? 1 : 0
          }).then(d => {
            _.growl(d);
            tr.toggleClass('resolved', this.checked);
          });
        });

      form.find('.js-forum-item-complete')
        .on('click', e => e.stopPropagation())
        .on('change', function(e) {
          const _$ = $(this);
          const tr = _$.closest('tr');

          // console.log(this);

          _.fetch.post(_.url('forum'), {
            action: this.checked ? 'mark-complete' : 'mark-incomplete',
            id: tr[0].dataset.id
          }).then(d => {
            _.growl(d);
            tr.toggleClass('complete', this.checked);
          });
        });

      form.find('.js-forum-item-resolved:checked')
        .each((i, chk) => $(chk).closest('.js-forum-item').addClass('resolved'));
      form.find('.js-forum-item-complete:checked')
        .each((i, chk) => $(chk).closest('.js-forum-item').addClass('complete'));

      table.find('tbody > tr')
        .addClass('pointer')
        .on('click', function(e) {
          _.get.modal(_.url(`forum/edit/${this.dataset.id}`))
            .then(m => m.on('success', d => $('.js-idea-viewer').trigger('refresh')));
        });

      document.title = <?= json_encode(sprintf('%s - %s', config::label_view, strings::brief($dto->idea, 50))); ?>;
    })(_brayworth_);
  </script>
</form>