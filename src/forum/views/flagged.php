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

extract((array)$this->data); ?>

<div class="table-responsive">

  <table class="table table-sm" id="<?= $_table = strings::rand() ?>">

    <thead class="small">
      <tr>
        <td class="js-line-number">#</td>
        <td>created</td>
        <td>description</td>
        <td>tag</td>
        <td>user</td>
      </tr>
    </thead>

    <tbody>

      <?php while ($dto = $res->dto()) { ?>

        <tr data-id="<?= $dto->id ?>">

          <td class="js-line-number"></td>
          <td><?= strings::asLocalDate($dto->created) ?></td>
          <td><?= $dto->description ?></td>
          <td><?= $dto->tag ?></td>
          <td><?= $dto->user_name ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
<script>
  (_ => {
    const table = $('#<?= $_table ?>');

    // implies there is a cell with class js-line-number
    table
      .on('update-line-numbers', _.table._line_numbers_)
      .trigger('update-line-numbers');

    table.find('>tbody >tr')
      .addClass('pointer')
      .on('click', function(e) {

        _.hideContexts(e);
        window.location.href = _.url('<?= $this->route ?>/view/' + this.dataset.id);
      });

  })(_brayworth_);
</script>