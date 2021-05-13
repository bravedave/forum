<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/  ?>

<div class="form-row mb-2 d-print-none">
  <div class="col-md-5 col-lg-4 pt-md-1">
    <div class="input-group">
      <?php
      if ( $this->dataset->page > 1 ) {
        printf( '<div class="input-group-prepend"><div class="input-group-text"><a href="%sforum"><i class="bi bi-chevron-double-left" title="start of forum"></i></a></div></div>', url::$URL );
        printf( '<div class="input-group-prepend"><div class="input-group-text"><a href="%sforum/?page=%s"><i class="bi bi-chevron-left" title="previous page"></i></a></div></div>', url::$URL, (int)$this->dataset->page - 1 );

      }	?>
        <div class="input-group-prepend">
          <div class="input-group-text"><?=
            sprintf(
              '%s - %s of %s at ',
              $this->dataset->start,
              $this->dataset->end,
              $this->dataset->total
            );

            ?></div>

        </div>

        <input type="text" class="form-control text-center"
          role="forum-items-per-page"
          value="<?= $this->ItemsPerPage ?>">

        <div class="input-group-append">
          <div class="input-group-text">/page</div>

        </div>

      <?php
      if ( $this->dataset->totalpages > $this->dataset->page ) {
        printf( '<div class="input-group-prepend"><div class="input-group-text"><a href="%sforum/?page=%s"><i class="bi bi-chevron-right" title="next page"></i></a></div></div>', url::$URL, (int)$this->dataset->page + 1 );
        printf( '<div class="input-group-prepend"><div class="input-group-text"><a href="%sforum/?page=%s"><i class="bi bi-chevron-double-right" title="end of forum"></i></a></div></div>', url::$URL, $this->dataset->totalpages );

      }	?>
    </div>

  </div>

  <div class="col">
    <div class="row">
      <div class="col text-right small">
        [<a href="<?= strings::url( $this->route . '/flagged') ?>">Flagged</a>]


      </div>

    </div>

    <div class="row">
      <div class="col text-right">
        <div class="form-check-inline d-none d-lg-inline">
          <input type="checkbox" class="form-check-input" id="<?= $_uid = strings::rand() ?>"
            <?php if ( $this->showOnlyMine) print 'checked'; ?>>
          <label class="form-check-label" for="<?= $_uid ?>">only mine</label>

          <script>
          ( _ => {
            $('#<?= $_uid ?>').on( 'change', function() {
              let _me = $(this);
              _.hourglass.on();

              _.post({
                url : _.url('<?= $this->route ?>'),
                data : {
                  action : 'show-mine',
                  state : _me.prop('checked') ? 'yes' : ''

                },

              }).then( d => {
                if ( 'ack' == d.response) {
                  window.location.reload();

                }
                else {
                  _.growl( d);
                  _.hourglass.on();

                }

              });

            });

          }) (_brayworth_);
          </script>

        </div>

        <div class="form-check-inline ml-md-1">
          <input type="checkbox" class="form-check-input" id="<?= $_uid = strings::rand() ?>"
            <?php if ( $this->includeComplete) print 'checked'; ?>>
          <label class="form-check-label" for="<?= $_uid ?>">complete</label>
          <script>
          ( _ => {
            $('#<?= $_uid ?>').on( 'change', function() {
              let _me = $(this);
              _.hourglass.on();

              _.post({
                url : _.url('<?= $this->route ?>'),
                data : {
                  action : 'show-complete',
                  state : _me.prop('checked') ? 'yes' : ''

                },

              }).then( d => {
                if ( 'ack' == d.response) {
                  window.location.reload();

                }
                else {
                  _.growl( d);
                  _.hourglass.on();

                }

              });

            });

          }) (_brayworth_);
          </script>

        </div>

        <div class="form-check-inline ml-md-1">
          <input type="checkbox" class="form-check-input" id="<?= $_uid = strings::rand() ?>"
            <?php if ( isset( $this->showClosed) && $this->showClosed) print 'checked'; ?>>
          <label class="form-check-label" for="<?= $_uid ?>">closed</label>
          <script>
          ( _ => {
            $('#<?= $_uid ?>').on( 'change', function() {
              let _me = $(this);
              _.hourglass.on();

              _.post({
                url : _.url('<?= $this->route ?>'),
                data : {
                  action : 'show-closed',
                  state : _me.prop('checked') ? 'yes' : ''

                },

              }).then( d => {
                if ( 'ack' == d.response) {
                  window.location.reload();

                }
                else {
                  _.growl( d);
                  _.hourglass.on();

                }

              });

            });

          }) (_brayworth_);
          </script>

        </div>

        <div class="form-check-inline d-none d-lg-inline ml-md-1 mr-0">
          <input type="checkbox" class="form-check-input" id="<?= $_uid = strings::rand() ?>"
            <?php if ( isset( $this->hideDead) && $this->hideDead) print 'checked'; ?>>
          <label class="form-check-label" for="<?= $_uid ?>">hide defunct</label>

          <script>
          ( _ => {
            $('#<?= $_uid ?>').on( 'change', function() {
              let _me = $(this);
              _.hourglass.on();

              _.post({
                url : _.url('<?= $this->route ?>'),
                data : {
                  action : 'show-dead',
                  state : _me.prop('checked') ? 'yes' : ''

                },

              }).then( d => {
                if ( 'ack' == d.response) {
                  window.location.reload();

                }
                else {
                  _.growl( d);
                  _.hourglass.on();

                }

              });

            });

          }) (_brayworth_);
          </script>


        </div>

      </div>

    </div>

  </div>

  <div class="col-auto pt-1">
    <button type="button" class="btn btn-outline-primary rounded-circle" data-role="new-forum-item"><i class="bi bi-plus"></i></button>

  </div>

</div>
<script>
( _ => $(document).ready( () => {
  $('button[data-role="new-forum-item"]').on( 'click', e => {
    _.get.modal( _.url('forum/add/'))
    .then( modal => modal.on( 'success', e => window.location.reload()));

  });

	$('[role="forum-items-per-page"]').on( 'keyup', function(e) {
		if ( !e.shiftKey && e.keyCode == 13) {
      let _me = $(this);
      _.post({
        url : _.url('<?= $this->route ?>'),
        data : {
          action : 'set-ipp',
          value : _me.val()

        },

      }).then( d => {
        if ( 'ack' == d.response) {
          window.location.reload();

        }
        else {
          _.growl( d);

        }

      });

		}

	});

}))( _brayworth_);
</script>
