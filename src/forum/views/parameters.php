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
  <div class="col-md-5 col-lg-4">
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

  <div class="col text-right pt-2">
    [<a href="<?= strings::url( $this->route . '/flagged') ?>">Flagged</a>]

    <div class="form-check-inline d-none d-lg-inline">
      <input type="checkbox" class="form-check-input" name="chk-show-only-mine" id="chk-show-only-mine" <?php if ( $this->showOnlyMine) print 'checked'; ?> />
      <label class="form-check-label" for="chk-show-only-mine">
        only mine

      </label>

    </div>

    <div class="form-check-inline ml-md-2">
      <input type="checkbox" class="form-check-input" name="chk-show-complete" id="chk-show-complete" <?php if ( $this->includeComplete) print 'checked'; ?> />
      <label class="form-check-label" for="chk-show-complete">
        complete

      </label>

    </div>

    <div class="form-check-inline ml-md-2">
      <input type="checkbox" class="form-check-input" name="chk-show-closed" id="chk-show-closed" <?php if ( isset( $this->showClosed) && $this->showClosed) print 'checked'; ?> />
      <label class="form-check-label" for="chk-show-closed">
        closed

      </label>

    </div>

    <div class="form-check-inline d-none d-lg-inline ml-md-2">
      <input type="checkbox" class="form-check-input" name="chk-show-dead" id="chk-show-dead" <?php if ( isset( $this->hideDead) && $this->hideDead) print 'checked'; ?> />
      <label class="form-check-label" for="chk-show-dead">
        hide defunct

      </label>

    </div>

  </div>

  <div class="col-auto">
    <button type="button" class="btn btn-outline-primary rounded-circle" data-role="new-forum-item"><i class="bi bi-plus"></i></button>

  </div>

</div>
<script>
( _ => $(document).ready( () => {
  $('button[data-role="new-forum-item"]').on( 'click', e => {
    _.get.modal( _.url('forum/add/'))
    .then( modal => modal.on( 'success', e => window.location.reload()));

  });

	$('#chk-show-only-mine').on( 'change', function() {
    window.location.href = _.url('<?= $this->route ?>/showOnlyMine/' + ($(this).prop('checked') ? 'on' : 'off'));

	});

	$('#chk-show-complete').on( 'change', function() {
    // console.log( _.url('<?= $this->route ?>/showComplete/' + ($(this).prop('checked') ? 'on' : 'off')));
    window.location.href = _.url('<?= $this->route ?>/showComplete/' + ($(this).prop('checked') ? 'on' : 'off'));

	});

	$('#chk-show-closed').on( 'change', function() {
    window.location.href = _.url('<?= $this->route ?>/showClosed/' + ($(this).prop('checked') ? 'on' : 'off'));

	});

	$('#chk-show-dead').on( 'change', function() {
    window.location.href = _.url('<?= $this->route ?>/hideDead/' + ($(this).prop('checked') ? 'on' : 'off'));

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
