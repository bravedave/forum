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
 * [x] form-row => row g-2
 */

namespace dvc\forum;

use cms\{currentUser};
use dvc\html;

extract((array)$this->data);

if ($this->comments)  ?>
<form method="post" action="<?= strings::url($this->route) ?>">

  <div class="row g-2" data-role="controls">

    <div class="col mb-2">

      <div id="forum-subject" class="h4"><?= $dto->description ?></div>
      <?php if (!$dto->closed) $this->load('view-comment-form'); ?>
    </div>
  </div>

  <div class="row g-2">

    <div class="col">
      <?php
      if (count($dto->children)) {
        for ($i = count($dto->children); $i > 0; $i--)
          print forumUtility::printThread($dto->children[$i - 1], $reversed = true);
      }
      ?></div>
  </div>
</form>

<div class="row g-2 mb-2 border-top">
  <?php
  printf(
    '<div class="col-lg-1 col-md-2 col-print-2 text-center px-0 pt-2">%s<div class="small">%s</div></div>',
    html::icon($dto->name),
    strings::asShortDate($dto->updated, true)
  );

  printf(
    '<div class="col border-left" id="initial-forum-comment">%s</div>',
    strings::AutoTextAsHTML($dto->comment)
  );
  ?>
</div>

<div>
  <?php
  if ($this->comments) {

    if ($dto->closed) {

      if (currentUser::isDavid()) {  ?>

        <div class="mt-1 text-end">
          <a class="button button-raised" href="<?= strings::url('forum/reopenTopic/' . $dto->id) ?>">
            re-open topic</a>
        </div>
  <?php
      }
    }
  }
  ?>
</div>

<script>
  (_ => {

    <?php if (currentUser::isAdmin()) {  ?>

      let fs = $('<input type="text" class="form-control" value="<?= htmlentities($dto->description) ?>">');
      fs
        .on('focus', function() {

          $(this).closest('form').on('submit', e => false);
        })
        .on('blur', function() {

          $(this).closest('form').off('submit');
        })
        .on('change', function() {

          _.fetch.post(_.url('<?= $this->route ?>'), {
              action: 'update-subject',
              id: <?= (int)$dto->id ?>,
              subject: $(this).val()
            })
            .then(_.growl)
        })

      let fsD = $('<div class="input-group"></div>').append(fs);

      $('#forum-subject')
        .html('')
        .append(fsD);

    <?php  } ?>
    _.ready(() => $('img').addClass('img-fluid'))
  })(_brayworth_);
</script>