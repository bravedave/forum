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

extract((array)($this->data ?? []));  ?>

<div class="row g-2 d-print-none">

  <div class="col-md-5 col-lg-4 pt-md-1 mb-2">
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

  <div class="col">

    <div class="row g-2">

      <div class="col text-end small">
        [<a href="<?= strings::url($this->route . '/flagged') ?>">Flagged</a>]
      </div>
    </div>

    <div class="row g-2">

      <div class="col text-end mb-2">

        <div class="form-check form-check-inline d-none d-lg-inline">

          <input type="checkbox" class="form-check-input" id="<?= $_uid = strings::rand() ?>" <?php if ($this->showOnlyMine) print 'checked'; ?>>
          <label class="form-check-label" for="<?= $_uid ?>">only mine</label>

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

        <div class="form-check form-check-inline ms-md-1">

          <input type="checkbox" class="form-check-input" id="<?= $_uid = strings::rand() ?>" <?php if ($this->includeComplete) print 'checked'; ?>>
          <label class="form-check-label" for="<?= $_uid ?>">complete</label>
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

        <div class="form-check form-check-inline ms-md-1">

          <input type="checkbox" class="form-check-input" id="<?= $_uid = strings::rand() ?>" <?php if (isset($this->showClosed) && $this->showClosed) print 'checked'; ?>>
          <label class="form-check-label" for="<?= $_uid ?>">closed</label>
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

        <div class="form-form-check-inline d-none d-lg-inline ms-md-1 me-0">

          <input type="checkbox" class="form-check-input" id="<?= $_uid = strings::rand() ?>" <?php if (isset($this->hideDead) && $this->hideDead) print 'checked'; ?>>
          <label class="form-check-label" for="<?= $_uid ?>">hide defunct</label>

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
  </div>

  <div class="col-auto pt-1">
    <button type="button" class="btn btn-outline-primary rounded-circle" data-role="new-forum-item">
      <i class="bi bi-plus"></i>
    </button>
  </div>
</div>
<script>
  (_ => {

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
  })(_brayworth_);
</script>