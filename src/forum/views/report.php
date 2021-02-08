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

use currentUser;
use html;
use strings;  ?>

<style>
@media print{@page {size: landscape; margin: 40px 20px 20px 20px}}

.line-clamp {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;

}

@media (min-width : 1200px) {
  .text-xl-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap

  }

}
</style>

<h1 class="m-0 d-none d-print-block"><?= $this->title ?></h1>

<div class="form-row d-print-none" id="<?= $_tagBox = strings::rand() ?>"></div>

<div class="form-row text-muted small border-top border-bottom mb-1">
  <div class="col-2 col-xl-4">
    <div class="form-row">
      <div class="col-xl-5 d-none d-xl-block">
        <div class="form-row">
          <div class="col-3 d-print-none">#</div>
          <div class="col-3"></div>
          <div class="col">tag</div>
        </div>
      </div>

      <div class="col-md-5 col-lg-6 col-xl-3 text-center text-truncate">created</div>
      <div class="col text-center d-none d-lg-block text-truncate" id="<?= $_uidWho = strings::rand() ?>">who</div>
      <div class="col text-center d-none d-lg-block text-truncate">priority</div>

    </div>

  </div>

  <div class="col-8 col-md-7 col-lg-8 col-xl-6">description</div>
  <div class="col-2 col-md-3 col-lg-2">
    <div class="row">
      <div class="col text-center">updated</div>
      <div class="col d-none d-md-block text-center">last</div>

    </div>

  </div>

</div>

<div class="d-none" id="<?= $_env = strings::rand() ?>">
  <?php	while ( $dto = $this->dataset->data->dto()) {
    $status = 0;	// open
    if ( $dto->closed || $dto->complete ) {
      if ( $dto->complete) {
        $status = 2;	// complete

      }
      elseif ( ( isset( $this->showClosed) && $this->showClosed) && $dto->closed) {
        $status = 3;	// closed

      }

    }
    elseif ( strtotime( $dto->last_updated) > strtotime( $dto->created)) {
      $status = 1;	// open and responded

    }

    $priority = 'normal';
    if ( $dto->priority == config::FORUM_BROKEN_PRIORITY ) $priority = config::FORUM_BROKEN_PRIORITY_TEXT;
    elseif ( $dto->priority == config::FORUM_URGENT_PRIORITY) $priority = config::FORUM_URGENT_PRIORITY_TEXT;
    elseif ( $dto->priority == config::FORUM_HIGH_PRIORITY) $priority = config::FORUM_HIGH_PRIORITY_TEXT;
    elseif ( $dto->priority == config::FORUM_MEDIUM_PRIORITY) $priority = config::FORUM_MEDIUM_PRIORITY_TEXT;
    elseif ( $dto->priority == config::FORUM_NORMAL_PRIORITY) $priority = config::FORUM_NORMAL_PRIORITY_TEXT;
    elseif ( $dto->priority == config::FORUM_LOW_PRIORITY) $priority = config::FORUM_LOW_PRIORITY_TEXT;
    ?>

    <div class="form-row mb-2 border-bottom <?= config::FORUM_BROKEN_PRIORITY == $dto->priority ? 'bg-danger text-white' : '' ?>"
      data-role="item"
      data-id="<?= $dto->id ?>"
      data-created="<?= date( 'Y-m-d H:i', strtotime( $dto->created)) ?>"
      data-updated="<?= date( 'Y-m-d H:i', strtotime( $dto->created)) ?>"
      data-reporter="<?= strings::initials( $dto->reporter_name); ?>"
      data-updater="<?= strings::initials( $dto->user_name); ?>"
      data-complete="<?= ( $dto->complete == 1 ? 'yes' : 'no' ) ?>"
      data-closed="<?= ( $dto->closed == 1 ? 'yes' : 'no' ) ?>"
      data-tag="<?= ( $dto->tag ? $dto->tag : 'ZZ' ) ?>"
      data-status="<?= $status ?>"
      data-priority="<?php printf( '%s-%s', $dto->priority, date( 'Y-m-d-H-i', strtotime( $dto->created ))) ?>"
      data-href="<?= strings::url( 'forum/view/' . $dto->id) ?>">

      <div class="col-2 col-xl-4">
        <div class="form-row">
          <div class="col-xl-5 d-none d-xl-block small pt-1">
            <div class="form-row">
              <div class="col-3 d-print-none"><?= $dto->id ?></div>
              <div class="col-3"><?php
                if ( $dto->complete)
                  print '<i class="bi bi-check" role="status-icon" aria-hidden="true" title="complete"></i>';
                elseif ( $status == 1)
                  print '<i class="bi bi-hand-thumbs-up" role="status-icon" aria-hidden="true" title="topic is open - responded"></i>';
                elseif ( $status == 2)
                  print '<i class="bi bi-hand-thumbs-up" role="status-icon" aria-hidden="true" title="topic is complete"></i>';
                elseif ( $status == 3)
                  print '<i class="bi bi-folder" role="status-icon" aria-hidden="true" title="topic is closed"></i>';
                else
                  print '<i class="bi bi-hand-thumbs-down" role="status-icon" aria-hidden="true" title="topic is open"></i>';

              ?></div>

              <div class="col text-truncate" data-role="tag"><?= $dto->tag ?></div>

            </div>

          </div>
          <div class="col-md-5 col-lg-6 col-xl-3 text-center small pt-1"><?= strings::asShortDate( $dto->created, true); ?></div>
          <div class="col text-center"><?= html::icon( $dto->reporter_name); ?></div>
          <div class="col text-center small pt-1" role="priority"><?= $priority ?></div>

        </div>

      </div>

      <div class="col-8 col-md-7 col-lg-8 col-xl-6 line-clamp text-xl-truncate">
        <strong><?php
        $discards = [
          '/(^Email:\s?)/',
          '@ \(1\) at realeastate.com.au on [0-9][0-9]/[0-9][0-9]/2[0-9][0-9][0-9] and 1 day\(s\) previous$@'

        ];

        print preg_replace( $discards, '', $dto->description)
        ?></strong>
        -
        <span class="small"><?php
        if ( $dto->closed)
          print 'closed';

        else
          print strings::brief( ( empty( $dto->last_comment) ? $dto->comment : $dto->last_comment));

        ?></span>

      </div>

      <div class="col-2 col-md-3 col-lg-2">
        <div class="row">
          <div class="col-md-6 text-center small">
            <?= strings::asShortDate( $dto->last_updated, true); ?>

          </div>

          <div class="col-md-6 text-center"><?= html::icon( $dto->user_name); ?></div>

        </div>

      </div>

    </div>

  <?php	} // while ( $row = $this->dataset->fetch()) {?>

</div>

<script>
  ( _ => {
    let prioritise = function( priority) {
      let _row = this;	// jQuery object
      let _data = _row.data();

      _.post({
        url : _.url( '<?= $this->route ?>'),
        data : {
          id : _data.id,
          action : 'prioritise',
          priority : priority,
        }

      })
      .done( d => {
        _.growl( d);
        if ( 'ack' == d.response) {
          _row.data('priority', String( d.priority));	// has to be text
          $('div[role="priority"]', _row).html( d.text);

          if ( '<?= config::FORUM_BROKEN_PRIORITY ?>' == d.priority ) {
            _row.addClass( 'bg-danger text-white');

          }
          else {
            _row.removeClass( 'bg-danger text-white');

          }

        }

      });

    }

    let mark = function( action) {
      let _row = this;	// jQuery object
      let _data = _row.data();

      _.post({
        url : _.url( '<?= $this->route ?>'),
        data : {
          action : action,
          id : _data.id

        }

      }).then( d => {
        _.growl(d);

        _row.data('complete', d.complete);

        $('i[role="status-icon"]', _row).removeClass('bi-hand-thumbs-up bi-folder bi-hand-thumbs-down bi-check');

        //~ console.log( d );
        if ( 1 == d.status)
          $('i[role="status-icon"]', _row).addClass('bi-hand-thumbs-up').attr( 'title', 'topic is open - responded');

        else if ( 2 == d.status)
          $('i[role="status-icon"]', _row).addClass('bi-check').attr( 'title', 'topic is complete');

        else if ( 3 == d.status)
          $('i[role="status-icon"]', _row).addClass('bi-folder').attr( 'title', 'topic is closed');

        else
          $('i[role="status-icon"]', _row).addClass('bi-hand-thumbs-down').attr( 'title', 'topic is open');

      });

    };

    let tags = [];
    let reporter = [];
    $('#<?= $_env ?> [data-role="item"]').each( ( i, el ) => {

      let _el = $(el);
      let _data = _el.data();
      // console.log( _data);

      if ( '' != _data.tag && 'ZZ' != _data.tag) {
        if ( tags.indexOf( _data.tag) < 0) {
          tags.push( _data.tag);

        }

      }

      if ( '' != _data.reporter) {
        if ( reporter.indexOf( _data.reporter) < 0) {
          reporter.push( _data.reporter);

        }

      }

      _el
      .on( 'contextmenu', function( e) {
        if ( e.shiftKey)
          return;

        let _row = $(this);
        let _data = _row.data();
        let complete = 'yes' == _data.complete;

        e.stopPropagation();e.preventDefault();

        _.hideContexts();

        let _context = _.context();

        _context.append( $('<a href="#">tag</a>').on('click', function(e) {
          e.stopPropagation();e.preventDefault();

          _context.close()

          let url = _.url( '<?= $this->route ?>/tag/' + _data.id);
          _.get.modal( url)
          .then( modal => modal.on( 'success', (e, data) => {
            _row.data('tag', data.tag);
            $('div[data-role="tag"]', _row).html(data.tag);
            // console.log( data);

          }));

        }));

        _context.append( $('<a href="#">' + ( complete ? 'Mark Incomplete' : 'Mark Complete') + '</a>').on('click', function(e) {
          e.stopPropagation();e.preventDefault();

          _context.close()
          mark.call( _row, complete ? 'mark-incomplete' :'mark-complete');

        }));

        _context.append( '<hr />');

        _context.append( $('<a href="#"><?= config::FORUM_LOW_PRIORITY_TEXT ?></a>').on('click', function(e) {
          e.stopPropagation();e.preventDefault();

          _context.close()
          prioritise.call( _row, '<?= config::FORUM_LOW_PRIORITY ?>' );

        }));

        _context.append( $('<a href="#"><?= config::FORUM_NORMAL_PRIORITY_TEXT ?></a>').on('click', function(e) {
          e.stopPropagation();e.preventDefault();

          _context.close()
          prioritise.call( _row, '<?= config::FORUM_NORMAL_PRIORITY ?>' );

        }));

        _context.append( $('<a href="#"><?= config::FORUM_MEDIUM_PRIORITY_TEXT ?></a>').on('click', function(e) {
          e.stopPropagation();e.preventDefault();

          _context.close()
          prioritise.call( _row, '<?= config::FORUM_MEDIUM_PRIORITY ?>' );

        }));

        _context.append( $('<a href="#"><?= config::FORUM_HIGH_PRIORITY_TEXT ?></a>').on('click', function(e) {
          e.stopPropagation();e.preventDefault();

          _context.close()
          prioritise.call( _row, '<?= config::FORUM_HIGH_PRIORITY ?>' );

        }));

        _context.append( $('<a href="#"><?= config::FORUM_URGENT_PRIORITY_TEXT ?></a>').on('click', function(e) {
          e.stopPropagation();e.preventDefault();

          _context.close()
          prioritise.call( _row, '<?= config::FORUM_URGENT_PRIORITY ?>' );

        }));

        _context.append( $('<a href="#"><?= config::FORUM_BROKEN_PRIORITY_TEXT ?></a>').on('click', function(e) {
          e.stopPropagation();e.preventDefault();

          _context.close()
          prioritise.call( _row, '<?= config::FORUM_BROKEN_PRIORITY ?>' );

        }));

        _context.append( '<hr>');

        _context.append( $('<a href="#">Reset Priority</a>').on('click', function(e) {
          e.stopPropagation();e.preventDefault();

          _context.close()
          _.post({
            url : _.url('<?= $this->route ?>'),
            data : {
              action : 'priority-reset'

            },

          }).then( d => {
            _.growl( d);
            window.location.reload();

          });

        }));

        _context.open( e);

      })

    });

    $('#<?= $_env ?>')
    .on( 'filter-apply', function( e) {
      let _me = $(this);
      let filter = localStorage.getItem('<?= config::forum_filterKey ?>');
      let filterWho = localStorage.getItem('<?= config::forum_filterWho ?>');

      if ( !!filterWho) {
        $('#<?= $_uidWho ?>').html(filterWho);

      }
      else {
        $('#<?= $_uidWho ?>').html('who');

      }

      $('#<?= $_env ?> [data-role="item"]').each( (i,el) => {
        let _el = $(el);
        let _data = _el.data();

        if ( !!filter) {
          if ( filter == _data.tag) {
            if ( !filterWho || filterWho == _data.reporter) {
              _el.removeClass( 'd-none');

            }
            else {
              _el.addClass( 'd-none');

            }

          }
          else {
            _el.addClass( 'd-none');

          }

        }
        else if ( !filterWho || filterWho == _data.reporter) {
          _el.removeClass( 'd-none');

        }
        else {
          _el.addClass( 'd-none');

        }

      });

      $('#<?= $_env ?>').removeClass( 'd-none');
      _me.trigger( 'filter-review');

    })
    .on( 'filter-clear', function( e) {
      localStorage.removeItem('<?= config::forum_filterKey ?>');

      let _me = $(this);
      _me.trigger( 'filter-apply');

    })
    .on( 'filter-init', function( e) {
      let _me = $(this);
      let filter = localStorage.getItem('<?= config::forum_filterKey ?>');
      let filterWho = localStorage.getItem('<?= config::forum_filterWho ?>');
      if ( !!filter || !!filterWho) {
        _me.trigger( 'filter-apply');

      }
      else {
        $('#<?= $_env ?>').removeClass( 'd-none');
        _me.trigger( 'filter-review');

      }

    })
    .on( 'filter-review', function( e, filter) {
      ( tagBox => {
        tagBox.html('');

        let filter = localStorage.getItem('<?= config::forum_filterKey ?>');

        $.each( tags, (i, tag) => {
          $('<button class="col-xl-1 col-md-2 col-3 btn btn-sm mb-2"></button>')
          .addClass( tag == filter ? 'btn-secondary' : 'btn-light')
          .html( tag)
          .on( 'click', function( e) {
            e.stopPropagation();
            $('#<?= $_env ?>').trigger( 'filter-set', tag);

          })
          .appendTo( tagBox);

        });

        if ( tags.length > 0) {
          $('<button class="col-auto btn btn-sm btn-light mb-2">&times;</button>')
          .on( 'click', function( e) {
            e.stopPropagation();
            $('#<?= $_env ?>').trigger( 'filter-clear');

          })
          .appendTo( tagBox);

        }

      })($('#<?= $_tagBox ?>'));

    })
    .on( 'filter-set', function( e, filter) {
      if ( !filter || '' == String( filter)) {
        localStorage.removeItem('<?= config::forum_filterKey ?>');

      }
      else {
        localStorage.setItem('<?= config::forum_filterKey ?>', filter);

      }

      let _me = $(this);
      _me.trigger( 'filter-apply');

    });

    if ( reporter.length > 0) {
      // console.table( reporter);
      $('#<?= $_uidWho ?>').on( 'contextmenu', function( e) {
        if ( e.shiftKey)
          return;

        e.stopPropagation();e.preventDefault();

        _.hideContexts();

        let _context = _.context();
        $.each( reporter, ( i, r) => {
          _context.append( $('<a href="#"></a>').html( r).on( 'click', function( e) {
            e.stopPropagation();e.preventDefault();

            let _me = $(this);
            localStorage.setItem('<?= config::forum_filterWho ?>', _me.html());
            $('#<?= $_env ?>').trigger( 'filter-apply');

            _context.close();

          }));

        });

        _context.append( '<hr>');
        _context.append( $('<a href="#">clear</a>').on( 'click', function( e) {
          e.stopPropagation();e.preventDefault();

          localStorage.removeItem('<?= config::forum_filterWho ?>');
          $('#<?= $_env ?>').trigger( 'filter-apply');

          _context.close();

        }));

        _context.open( e);

      });

    }

    $(document).ready( () => $('#<?= $_env ?>').trigger( 'filter-init'));

  })( _brayworth_);
</script>
