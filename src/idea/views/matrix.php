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

use dvc\forum\strings; ?>

<div class="accordion" id="<?= $_uidAccordion = strings::rand() ?>">
  <div class="accordion-item border-0">
    <div id="<?= $_uidAccordion ?>-feed" class="accordion-collapse collapse show" data-bs-parent="#<?= $_uidAccordion ?>">
      <div class="row g-2 mb-2">

        <div class="col">

          <input type="search" accesskey="/" class="form-control" autofocus id="<?= $_search = strings::rand()  ?>">
        </div>

        <div class="col-auto">
          <button type="button" class="btn btn-light js-new-idea">
            <i class="bi bi-plus"></i>
          </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-hover" id="<?= $_table = strings::rand(); ?>">
          <thead class="small">
            <tr>
              <td class="js-line-number" style="width: 50px;">#</td>
              <td>idea</td>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="accordion-item border-0">

    <div id="<?= $_uidAccordion ?>-workbench" class="accordion-collapse collapse" data-bs-parent="#<?= $_uidAccordion ?>">

      <nav class="navbar navbar-expand d-print-none">
        <div class="navbar-brand">Workbench</div>
        <nav class="navbar-nav ms-auto">
          <button type="button" class="btn-close ms-2" data-bs-toggle="collapse"
            data-bs-target="#<?= $_uidAccordion ?>-feed"></button>
        </nav>
      </nav>

      <div class="js-idea-viewer"></div>
    </div>
  </div>

  <script>
    (_ => {
      const feed = $('#<?= $_uidAccordion ?>-feed');
      const workbench = $('#<?= $_uidAccordion ?>-workbench');
      const table = $('#<?= $_table ?>');
      const search = $('#<?= $_search ?>');

      const contextmenu = function(e) {

        if (e.shiftKey) return;
        let _ctx = _.context(e); // hides any open contexts and stops bubbling

        _ctx.append.a({
          html: '<strong>view</strong>',
          click: e => $(this).trigger('view')
        });

        _ctx.append.a({
          html: '<i class="bi bi-pencil"></i>edit',
          click: e => $(this).trigger('edit')
        });

        _ctx.open(e);
      };

      const edit = function(e, then) {

        const tr = $(this);
        const dto = tr.data('dto');

        _.get.modal(_.url(`<?= $this->route ?>/edit/${dto.id}`))
          .then(m => m.on('success', e => tr.trigger('refresh', then)));
      };

      const getMatrix = () => {

        _.fetch.post(_.url('<?= $this->route ?>'), {
          action: 'get-forum-ideas'
        }).then(d => 'ack' == d.response ? matrix(d.data) : _.growl(d));
      };

      const matrix = data => {

        const tbody = table.find('tbody').empty();

        // console.table(data);
        $.each(data, (i, dto) => {
          $(`<tr class="pointer">
            <td class="small align-middle" line-number></td>
            <td class="js-idea">${dto.idea}</td>
          </tr>`)
            .on('click', function(e) {

              e.stopPropagation();
              $(this).trigger('view');
            })
            .on('contextmenu', contextmenu)
            .on('edit', edit)
            .on('refresh', trRefresh)
            .on('view', viewer)
            .data('dto', dto)
            .appendTo(tbody);
        });

        table.trigger('update-line-numbers');
      };

      const trRefresh = function(e, then) {

        const _tr = $(this);
        const _dto = _tr.data('dto');

        _.fetch.post(_.url('<?= $this->route ?>'), {
          action: 'get-by-id',
          id: _dto.id
        }).then(d => {

          if ('ack' == d.response) {

            $('.js-idea', _tr).html(d.dto.idea);
            _tr
              .data('dto', d.dto)
              .trigger('refreshed');

            if ('function' === typeof then) then(d.dto);
          } else {

            _.growl(d);
          }
        });
      };

      const viewer = function(e) {

        const tr = $(this);
        const dto = tr.data('dto');

        const tabs = _.tabs(workbench);
        const view = tabs.newTab('view');
        const addLink = $('<button class="btn btn-light ms-2"><i class="bi bi-plus-circle"></i></button>');
        const editLink = $('<button class="btn btn-light ms-2"><i class="bi bi-pencil"></i></button>');
        const h5 = $(`<h5 class="me-auto mt-2">${dto.idea}</h5>`);

        view.tab.on('show.bs.tab', e => view.pane.html('<h1>Loading...</h1>'));

        tabs.nav.prepend(h5);
        tabs.nav.append(editLink);
        tabs.nav.append(addLink);

        tabs.nav.append(`<button type="button" class="btn-close mt-2 ms-2" data-bs-toggle="collapse"
          data-bs-target="#<?= $_uidAccordion ?>-feed"
          aria-expanded="false" aria-controls="<?= $_uidAccordion ?>-feed"></button>`);

        view.pane
          .addClass('pt-2')
          .on('refresh', e => {

            e.stopPropagation();
            view.pane.load(_.url(`<?= $this->route ?>/view/${dto.id}`));
          });

        workbench.collapse('show');
        view.tab
          .on('shown.bs.tab', e => view.pane.trigger('refresh'))
          .tab('show');

        editLink.on('click', function(e) {
          _.hideContexts(e); // stopPropagation and hide all contexts

          _.get.modal(_.url(`<?= $this->route ?>/edit/${dto.id}`))
            .then(m => m.on('success', e => {

              view.pane.trigger('refresh');
              tr.trigger('refresh', dto => h5.html(dto.idea));
            }));
        });

        addLink.on('click', e => {
          _.hideContexts(e);

          _.get.modal(_.url('forum/add/'))
            .then(modal => {
              $('input[name="forum_idea_id"]', modal.closest('form')).val(_dto.id);
              $('input[name="tag"]', modal.closest('form')).val(_dto.tag);
              modal.on('success', e => viewer.trigger('refresh'));
              return modal;
            });

        });
      };

      feed.find('button.js-new-idea').on('click', e => {

        e.stopPropagation();
        _.hideContexts(e);

        _.get.modal('<?= $this->route ?>/edit')
          .then(m => m.on('success', getMatrix));
      });

      [
        feed,
        workbench,
      ].forEach(el => {
        el
          .on('hide.bs.collapse', e => e.stopPropagation())
          .on('hidden.bs.collapse', e => e.stopPropagation())
          .on('show.bs.collapse', e => e.stopPropagation())
          .on('shown.bs.collapse', e => e.stopPropagation());
      });

      feed.on('show.bs.collapse', e => $('body').toggleClass('hide-nav-bar', false));
      workbench.on('show.bs.collapse', e => $('body').toggleClass('hide-nav-bar', true));

      feed.on('shown.bs.collapse', e => {

        _.hideContexts();

        const tr = table.find('> tbody tr.bg-info');

        if (tr.length > 0) {
          tr[0].scrollIntoView({
            block: "center"
          });
          // console.log(_tr[0]);
          setTimeout(() => tr.removeClass('bg-info'), 800)
        }

        document.title = <?= json_encode($title) ?>;
      });

      // return true from the prefilter to show the row
      _.table.search(search, table, /* prefilter tr => true */ );

      _.ready(getMatrix);
    })(_brayworth_);
  </script>
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

    const tr_view = function(e) {
      e.stopPropagation();


      _.hideContexts();
      _tr.addClass('bg-info');

      workbench.empty().collapse('show');

      let nav = accordionNav();
      const navItem = () => $('<li class="nav-item"></li>').prependTo(nav);


      const icon = $('<a class="nav-link" href="#"><i class="bi bi-arrow-clockwise"></i></a>')
        .on('click', e => {
          e.stopPropagation();
          e.preventDefault();

          _.hideContexts();

          viewer.trigger('refresh');
        })
        .appendTo(navItem());
    };

  })(_brayworth_);
</script>