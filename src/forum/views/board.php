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

use dvc\forum\strings;

extract((array)($this->data ?? []));  ?>

<div class="row g-2">
  <div class="col mb-2">
    <input type="search" accesskey="/" class="form-control" id="<?= $_search = strings::rand() ?>" autofocus>
  </div>

  <div class="col-auto pt-2 mb-2">

    <div class="form-check">
      <input type="checkbox" class="form-check-input" id="<?= $_uidArchived = strings::rand() ?>">

      <label class="form-check-label" for="<?= $_uidArchived ?>">
        archived
      </label>
    </div>
  </div>

  <div class="col-auto mb-2">

    <button type="button" class="btn btn-outline-primary" id="<?= $_add = strings::rand() ?>"><i
        class="bi bi-plus"></i></button>
  </div>
</div>

<div class="table-responsive">

  <table class="table" id="<?= $_table = strings::rand() ?>">

    <thead class="small">
      <tr>
        <td class="text-center js-line-number"></td>
        <td>name</td>
        <td class="text-center">idea</td>
        <td class="text-center">link</td>
        <td class="text-center">status</td>
        <td class="text-center">priority</td>
      </tr>
    </thead>

    <tbody></tbody>
  </table>
</div>
<script>
  (_ => {
    const addButton = $('#<?= $_add ?>');
    const archived = $('#<?= $_uidArchived ?>');
    const table = $(`#<?= $_table ?>`);
    const search = $('#<?= $_search ?>');

    const priority_text = <?= json_encode(config::board_priority_text) ?>;
    const status_text = <?= json_encode(config::board_status_text) ?>;

    const contextmenu = function(e) {

      if (e.shiftKey) return;
      let _ctx = _.context(e); // hides any open contexts and stops bubbling

      let dto = $(this).data('dto');

      _ctx.append.a({
        html: '<strong>edit</strong>',
        click: e => $(this).trigger('edit')
      });

      _ctx.append.a({
        html: `<i class="bi bi-archive${'yes' == this.dataset.archived ? '-fill' : ''}"></i>archived`,
        click: e => {
          _.fetch
            .post(_.url('<?= $this->route ?>'), {
              action: 'board-archive',
              id: this.dataset.id,
              value: 'yes' == this.dataset.archived ? 0 : 1,
            })
            .then(d => {
              if ('ack' == d.response) {

                if ('yes' == this.dataset.archived) {

                  this.dataset.archived = 'no';
                  $(this).removeClass('table-light');
                } else {

                  this.dataset.archived = 'yes';
                  $(this).addClass('table-light');
                }

                _.growl(d);
              }
            });

        }
      });

      if (/http[s]{0,1}:\/\//.test(dto.link)) {

        _ctx.append.a({
          html: 'open link in new tab',
          click: e => window.open(dto.link)
        });
      }

      _ctx.append.a({
        html: 'dump',
        click: e => console.log(dto)
      });

      _ctx.open(e);
    };

    const getMatrix = () => new Promise((resolve, reject) => {

      table.placeholders();

      _.fetch.post(_.url('<?= $this->route ?>'), {
        action: 'board-getMatrix',
        archived: archived.prop('checked') ? '1' : '0',
      }).then(d => ('ack' == d.response) ? resolve(d.data) : reject(d));
    });

    const matrix = data => {

      table.clearPlaceholders();

      const tbody = table.find('tbody');
      tbody.html('');

      $.each(data, (i, dto) => {

        let bgPriority = '';
        if ('<?= config::board_priority_urgent ?>' == dto.priority) bgPriority = 'badge rounded-pill text-bg-danger';
        else if ('<?= config::board_priority_high ?>' == dto.priority) bgPriority = 'badge rounded-pill text-bg-warning';
        else if ('<?= config::board_priority_medium ?>' == dto.priority) bgPriority = 'badge rounded-pill text-bg-info';

        let bgStatus = '';
        if ('<?= config::board_status_done ?>' == dto.status) bgStatus = 'badge rounded-pill text-bg-success';
        else if ('<?= config::board_status_review ?>' == dto.status) bgStatus = 'badge rounded-pill text-bg-info';
        else if ('<?= config::board_status_inprogress ?>' == dto.status) bgStatus = 'badge rounded-pill text-bg-primary';

        let tm = '';
        if ('1' == dto.archived) tm = 'table-light';

        $(`<tr class="pointer ${tm}" data-id="${dto.id}" data-archived="${dto.archived ? 'yes' : 'no'}">
            <td class="text-center js-line-number"></td>
            <td>${dto.name}</td>
            <td class="text-center">${'1' == dto.idea ? '<i class="bi bi-check"></i>' : '&nbsp;'}</td>
            <td class="text-center">${'' == dto.link ? '' : '<i class="bi bi-link"></i>'}</td>
            <td class="text-center"><div class="${bgStatus}">${status_text[dto.status]}</div></td>
            <td class="text-center"><div class="${bgPriority}">${priority_text[dto.priority]}</div></td>
          </tr>`)
          .on('click', function(e) {

            _.hideContexts(e);
            $(this).trigger('edit');
          })
          .on('contextmenu', contextmenu)
          .on('edit', trEdit)
          .data('dto', dto)
          .appendTo(tbody);
      });

      if (search.val() == '') {

        table.trigger('update-line-numbers');
      } else {

        search.trigger('search');
      }

    };

    const trEdit = function(e) {

      _.hideContexts(e);
      _.get.modal(_.url('<?= $this->route ?>/boardEdit/' + this.dataset.id))
        .then(m => m.on('success', e => window.location.reload()));
    };

    addButton.on('click', e => {
      _.hideContexts(e);

      _.get.modal(_.url('<?= $this->route ?>/boardEdit'))
        .then(m => m.on('success', e => window.location.reload()));
    });

    archived.on('change', function(e) {

      _.hideContexts(e);
      getMatrix().then(matrix);
    });

    _.table.search(search, table);
    table.on('update-line-numbers', _.table._line_numbers_);

    _.ready(() => getMatrix().then(matrix));

  })(_brayworth_);
</script>