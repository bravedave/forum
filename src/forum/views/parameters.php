<?php

/**
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
 * [x] form-row => row
 */

namespace dvc\forum;

extract((array)($this->data ?? []));
?>

<div class="row g-2 d-print-none">

  <div class="col-lg-auto pt-lg-3 mb-2">
    <div class="input-group">
      <?php
      if ($dataset->page > 1) {
        printf('<a class="btn btn-light" href="%s"><i class="bi bi-chevron-double-left" title="start of forum"></i></a>', strings::url('forum'));
        printf('<a class="btn btn-light" href="%s"><i class="bi bi-chevron-left" title="previous page"></i></a>', strings::url('forum/?page=' . $dataset->page - 1));
      }  ?>
      <div class="input-group-text">
        <?= sprintf(
          '%s - %s of %s at ',
          $dataset->start,
          $dataset->end,
          $dataset->total
        ); ?></div>


      <input type="text" class="form-control text-center" role="forum-items-per-page" value="<?= $this->ItemsPerPage ?>">
      <div class="input-group-text">/page</div>

      <?php
      if ($dataset->totalpages > $dataset->page) {
        printf('<a class="btn btn-light" href="%s"><i class="bi bi-chevron-right" title="next page"></i></a>', strings::url('forum/?page=' . ((int)$dataset->page + 1)));
        printf('<a class="btn btn-light" href="%s"><i class="bi bi-chevron-double-right" title="end of forum"></i></a>', strings::url('forum/?page=' . $dataset->totalpages));
      }  ?>
    </div>
  </div>

  <div class="col-lg pt-lg-3 mb-2">
    <input type="search" class="form-control" id="<?= $_searchForum = strings::rand() ?>" placeholder="search forums">
  </div>

  <div class="col-auto mb-2">

    <div class="row g-2">

      <div class="col small">&nbsp;</div>

      <div class="col-auto small">
        [<a href="<?= strings::url($this->route . '/board') ?>">board</a>]
      </div>

      <div class="col-auto small">
        [<a href="<?= strings::url($this->route . '/flagged') ?>">Flagged</a>]
      </div>
    </div>

    <div class="row g-2">

      <div class="col-auto">

        <div class="input-group input-group-sm">

          <div class="input-group-text">
            <input type="checkbox" id="<?= $_uid = strings::rand() ?>" <?= $this->showOnlyMine ? 'checked' : '' ?>>
          </div>
          <label class="input-group-text" for="<?= $_uid ?>">only mine</label>
        </div>
        <script>
          (_ => {
            $('#<?= $_uid ?>').on('change', function() {

              let _me = $(this);
              _.hourglass.on();

              _.fetch
                .post(_.url('<?= $this->route ?>'), {
                  action: 'show-mine',
                  state: _me.prop('checked') ? 'yes' : ''
                })
                .then(d => {
                  if ('ack' == d.response) {

                    window.location.reload();
                  } else {

                    _.growl(d);
                    _.hourglass.on();
                  }
                });
            });
          })(_brayworth_);
        </script>
      </div>

      <div class="col-auto">

        <div class="input-group input-group-sm">

          <div class="input-group-text">
            <input type="checkbox" id="<?= $_uid = strings::rand() ?>" <?= $this->includeComplete ? 'checked' : '' ?>>
          </div>
          <label class="input-group-text" for="<?= $_uid ?>">complete</label>
        </div>
        <script>
          (_ => {

            $('#<?= $_uid ?>').on('change', function() {
              let _me = $(this);
              _.hourglass.on();

              _.fetch
                .post(_.url('<?= $this->route ?>'), {
                  action: 'show-complete',
                  state: _me.prop('checked') ? 'yes' : ''
                }).then(d => {

                  if ('ack' == d.response) {

                    window.location.reload();

                  } else {

                    _.growl(d);
                    _.hourglass.on();
                  }
                });
            });
          })(_brayworth_);
        </script>
      </div>

      <div class="col-auto">

        <div class="input-group input-group-sm">

          <div class="input-group-text">
            <input type="checkbox" id="<?= $_uid = strings::rand() ?>" <?= ($this->showClosed ?? false) ? 'checked' : '' ?>>
          </div>
          <label class="input-group-text" for="<?= $_uid ?>">closed</label>
        </div>
        <script>
          (_ => {

            $('#<?= $_uid ?>').on('change', function() {

              let _me = $(this);
              _.hourglass.on();

              _.fetch
                .post(_.url('<?= $this->route ?>'), {
                  action: 'show-closed',
                  state: _me.prop('checked') ? 'yes' : ''
                }).then(d => {

                  if ('ack' == d.response) {

                    window.location.reload();
                  } else {

                    _.growl(d);
                    _.hourglass.on();
                  }
                });
            });
          })(_brayworth_);
        </script>
      </div>

      <div class="col-auto">

        <div class="input-group input-group-sm">

          <div class="input-group-text">
            <input type="checkbox" id="<?= $_uid = strings::rand() ?>" <?= ($this->hideDead ?? false) ? 'checked' : '' ?>>
          </div>
          <label class="input-group-text" for="<?= $_uid ?>">hide defunct</label>
        </div>
        <script>
          (_ => {

            $('#<?= $_uid ?>').on('change', function() {

              let _me = $(this);
              _.hourglass.on();

              _.fetch
                .post(_.url('<?= $this->route ?>'), {
                  action: 'show-dead',
                  state: _me.prop('checked') ? 'yes' : ''
                })
                .then(d => {

                  if ('ack' == d.response) {

                    window.location.reload();
                  } else {

                    _.growl(d);
                    _.hourglass.on();
                  }
                });
            });
          })(_brayworth_);
        </script>
      </div>
    </div>
  </div>

  <div class="col-auto pt-1">
    <button type="button" class="btn btn-outline-primary rounded-circle" data-role="new-forum-item">
      <i class="bi bi-plus"></i>
    </button>
  </div>
</div>

<div class="row g-2">
  <div class="col" id="<?= $_searchForum ?>-results"></div>
</div>

<script>
  (_ => {
    const searchForum = $('#<?= $_searchForum ?>');
    const searchForumResults = $('#<?= $_searchForum ?>-results');

    $('button[data-role="new-forum-item"]').on('click', e => {

      _.get.modal(_.url('forum/add/'))
        .then(modal => modal.on('success', e => window.location.reload()));
    });

    $('[role="forum-items-per-page"]')
      .on('keyup', function(e) {

        if (!e.shiftKey && e.keyCode == 13) {

          let _me = $(this);
          _.fetch
            .post(_.url('<?= $this->route ?>'), {
              action: 'set-ipp',
              value: _me.val()
            })
            .then(d => {

              if ('ack' == d.response) {

                window.location.reload();
              } else {

                _.growl(d);
              }
            });
        }
      });

    let searchForumIDX = 0;
    searchForum.on('input', function(e) {

      searchForumIDX++;

      if ('' == this.value.trim()) {

        searchForumResults.html('');
        return;
      }

      let idx = searchForumIDX;
      setTimeout(() => {

        if (idx != searchForumIDX) return;

        searchForumResults.html(`<div class="text-center py-3"><div class="spinner-border" role="status"></div></div>`);

        _.fetch
          .post(_.url('<?= $this->route ?>'), {
            action: 'search-forums',
            term: this.value
          })
          .then(d => {

            if ('ack' == d.response) {

              if (idx == searchForumIDX) {

                searchForumResults.html('');

                $.each(d.data, (i, dto) => {

                  let col = $(`<div class="col">
                    <a target="_blank" href="${_.url('<?= $this->route ?>/view/' + dto.id)}">${_.encodeHTMLEntities(dto.description)}</a>
                  </div>`);

                  if (dto.description != dto.instance) {

                    col.append(`<p class="small text-muted">${_.encodeHTMLEntities(dto.instance)}</p>`);
                  }

                  $(`<div class="row g-2"><div class="col-md-2">${_.asLocaleDate(dto.date)}</div></div>`)
                    .append(col)
                    .appendTo(searchForumResults);
                });

                console.log(d);
              }
            } else {

              _.growl(d);
            }
          });
      }, 800);
    });
  })(_brayworth_);
</script>