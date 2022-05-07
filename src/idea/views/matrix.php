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

<div class="accordion" id="<?= $_uidAccordion = strings::rand() ?>">
  <div id="<?= $_uidAccordion ?>-feed" class="collapse fade show" data-parent="#<?= $_uidAccordion ?>">
    <div class="form-row">
      <div class="col mb-2">
        <div class="input-group">
          <input type="search" accesskey="/" class="form-control" autofocus id="<?= $srch = strings::rand()  ?>">

          <div class="input-group-append">
            <button type="button" class="btn input-group-text js-<?= $srch ?>-clear">&times;</button>
          </div>
        </div>
      </div>
      <div class="col-auto">
        <button type="button" class="btn btn-light js-new-idea"><i class="bi bi-plus"></i></button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-sm table-hover" id="<?= $_table = strings::rand(); ?>">
        <thead class="small">
          <tr>
            <td line-number style="width: 50px;">#</td>
            <td>idea</td>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

    </div>

  </div>
  <div id="<?= $_uidAccordion ?>-workbench" class="collapse fade" data-parent="#<?= $_uidAccordion ?>">
    workbench
  </div>
</div>

<script>
  (_ => {
    const accordionNav = () => {
      let ul = $('<ul class="nav border-bottom"></ul>');
      let close = $('<a class="nav-link" data-toggle="collapse" href="#">x</a>')
        .on('click', function(e) {
          e.stopPropagation();
          e.preventDefault();

          $('#<?= $_uidAccordion ?>-feed').collapse('show');

        });

      $('<li class="nav-item" data-role="close"></li>')
        .append(close)
        .appendTo(ul);

      ul.appendTo('#<?= $_uidAccordion ?>-workbench');

      return ul;
    };

    const matrix = data => {
      let _table = $('#<?= $_table ?>');
      let _tbody = $('tbody', _table);

      _tbody.html('');
      // console.table(data);
      $.each(data, (i, dto) => {
        $(`<tr class="pointer">
            <td class="small align-middle" line-number></td>
            <td class="js-idea">${dto.idea}</td>
          </tr>`)
          .on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            $(this).trigger('view');

          })
          .on('contextmenu', tr_contextmenu)
          .on('edit', tr_edit)
          .on('refresh', tr_refresh)
          .on('view', tr_view)
          .data('dto', dto)
          .appendTo(_tbody);
      });

      _table.trigger('update-line-numbers');

    };

    const tr_contextmenu = function(e) {
      if (e.shiftKey)
        return;

      e.stopPropagation();
      e.preventDefault();

      _.hideContexts();

      let _context = _.context();

      _context.append($('<a href="#"><strong>view</strong></a>').on('click', e => {
        e.stopPropagation();
        e.preventDefault();

        $(this).trigger('view'); // row

      }));

      _context.append($('<a href="#"><i class="bi bi-pencil"></i>edit</a>').on('click', e => {
        e.stopPropagation();
        e.preventDefault();

        $(this).trigger('edit'); // row

      }));

      _context.open(e);
    };

    const tr_edit = function(e, success) {
      e.stopPropagation();

      let _tr = $(this);
      let _dto = _tr.data('dto');

      if (!success) success = e => _tr.trigger('refresh');

      _.get.modal(_.url(`<?= $this->route ?>/edit/${_dto.id}`))
        .then(m => m.on('success', success));

    };

    const tr_refresh = function(e) {
      e.stopPropagation();

      let _tr = $(this);
      let _dto = _tr.data('dto');

      _.post({
        url: _.url('<?= $this->route ?>'),
        data: {
          action: 'get-by-id',
          id: _dto.id
        },
      }).then(d => {
        if ('ack' == d.response) {
          // console.log(d.dto);
          $('.js-idea', _tr).html(d.dto.idea);
          _tr
            .data('dto', d.dto)
            .trigger('refreshed');
        } else {
          _.growl(d);
        }
      });
    };

    const tr_view = function(e) {
      e.stopPropagation();

      let _tr = $(this);
      let _dto = _tr.data('dto');

      _.hideContexts();
      _tr.addClass('bg-info');

      _workbench
        .html('')
        .collapse('show');

      let nav = accordionNav();
      const navItem = () => $('<li class="nav-item"></li>').prependTo(nav);

      let viewer = $('<div></div>')
        .on('refresh', function(e) {
          e.stopPropagation();

          $('.bi', icon)
            .removeClass('bi-arrow-clockwise')
            .addClass('spinner-border spinner-border-sm');

          $(this).load(_.url(`<?= $this->route ?>/view/${_dto.id}`), html => {
            $('.bi', icon)
              .removeClass('spinner-border spinner-border-sm')
              .addClass('bi-arrow-clockwise');

          });

        })
        .appendTo(_workbench);

      const icon = $('<a class="nav-link" href="#"><i class="bi bi-arrow-clockwise"></i></a>')
        .on('click', e => {
          e.stopPropagation();
          e.preventDefault();

          _.hideContexts();

          viewer.trigger('refresh');
        })
        .appendTo(navItem());

      $('<a class="nav-link" href="#"><i class="bi bi-pencil"></i></a>')
        .on('click', e => {
          e.stopPropagation();
          e.preventDefault();

          _.hideContexts();

          _tr.trigger('edit', e => {
            viewer.trigger('refresh');
            _tr
              .one('refreshed', function(e) {
                let _tr = $(this);
                let _dto = _tr.data('dto');
                brand.html(_dto.idea);
              })
              .trigger('refresh');
          });
        })
        .appendTo(navItem());

      let brand = $('<li class="navbar-brand mr-auto"></li>')
        .html(_dto.idea)
        .prependTo(nav);

      viewer.trigger('refresh');

    };

    const _feed = $('#<?= $_uidAccordion ?>-feed');
    const _workbench = $('#<?= $_uidAccordion ?>-workbench');
    const _table = $('#<?= $_table ?>');

    _feed
      .on('show.bs.collapse', e => e.stopPropagation())
      .on('shown.bs.collapse', e => {
        e.stopPropagation();

        _.hideContexts();

        let tr = $('#<?= $_table ?> > tbody tr.bg-info');

        if (tr.length > 0) {
          tr[0].scrollIntoView({
            block: "center"
          });
          // console.log(_tr[0]);
          setTimeout(() => tr.removeClass('bg-info'), 800)
        }

        document.title = <?= json_encode($title) ?>;
      })
      .on('hide.bs.collapse', e => e.stopPropagation())
      .on('hidden.bs.collapse', e => e.stopPropagation());
    _workbench
      .on('show.bs.collapse', e => e.stopPropagation())
      .on('shown.bs.collapse', e => e.stopPropagation())
      .on('hide.bs.collapse', e => e.stopPropagation())
      .on('hidden.bs.collapse', e => e.stopPropagation());

    $('button.js-new-idea').on('click', function(e) {
      e.preventDefault();

      _.get.modal('<?= $this->route ?>/edit')
        .then(m => m.on('success', e => $('#<?= $_table ?>').trigger('refresh')));

    });

    $('button.js-<?= $srch ?>-clear')
      .on('click', function(e) {
        e.stopPropagation();
        $('#<?= $srch ?>').trigger('clear');
        $(this).blur();

      });

    let _srchidx = 0;
    _table
      .on('refresh', function(e) {
        e.stopPropagation();

        _.post({
          url: _.url('<?= $this->route ?>'),
          data: {
            action: 'get-forum-ideas'
          },
        }).then(d => {
          if ('ack' == d.response) {
            matrix(d.data);
          } else {
            _.growl(d);
          }
        });
      })
      .on('search', function(e) {
        let idx = ++_srchidx;
        let txt = $('#<?= $srch ?>').val();

        $('> tbody > tr', this)
          .each((i, tr) => {
            if (idx != _srchidx) return false;

            let _tr = $(tr);
            let str = _tr.text()
            if (str.match(new RegExp(txt, 'gi'))) {
              _tr.removeClass('d-none');
            } else {
              _tr.addClass('d-none');
            }
          });

        if (idx == _srchidx) $(this).trigger('update-line-numbers');

      })
      .on('update-line-numbers', function(e) {
        let t = 0;
        $('> tbody > tr:not(.d-none) >td[line-number]', this).each((i, e) => {
          $(e).data('line', i + 1).html(i + 1);
          t++;
        });
        $('> thead > tr > td[line-number]', this).data('count', t).html(t);
      });

    $('#<?= $srch ?>')
      .on('blur', function(e) {
        e.stopPropagation();
        if (!_.browser.isMobileDevice) {
          $(this).attr('placeholder', 'alt + / to focus');
        }
      })
      .on('clear', function(e) {
        e.stopPropagation();
        $(this)
          .val('')
          .trigger('search')
          .focus();
      })
      .on('focus', function(e) {
        if (_.browser.isMobileDevice) {
          $(this).attr('placeholder', 'search ..');
        } else {
          $(this).attr('placeholder', 'type to search ..');
        }
      })
      .on('keyup', function(e) {
        e.stopPropagation();
        $(this).trigger('search');
      })
      .on('search', e => {
        e.stopPropagation();
        _table.trigger('search');
      });;

    $(document).ready(() => {

      $('#<?= $_table ?>').trigger('refresh');

    });
  })(_brayworth_);
</script>