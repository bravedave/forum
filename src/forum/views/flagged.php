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

// \sys::dump( $this->data->res);
?>

<table class="table table-sm" id="<?= $_uidTbl = strings::rand() ?>">
  <thead>
    <tr>
      <td>#</td>
      <td>created</td>
      <td>description</td>
      <td>tag</td>
      <td>user</td>

    </tr>

  </thead>

  <tbody>
  <?php while ( $dto = $this->data->res->dto()) { ?>
    <tr data-id="<?= $dto->id ?>">
      <td line-number></td>
      <td><?= strings::asLocalDate($dto->created) ?></td>
      <td><?= $dto->description ?></td>
      <td><?= $dto->tag ?></td>
      <td><?= $dto->user_name ?></td>

    </tr>

  <?php } ?>

  </tbody>

</table>
<script>
( _ => {
  $('#<?= $_uidTbl ?>')
  .on('update-line-numbers', function( e) {
    $('> tbody > tr:not(.d-none) >td[line-number]', this).each( ( i, e) => {
      $(e).data('line', i+1).html( i+1);
    });
  })
  .trigger('update-line-numbers');

  $('#<?= $_uidTbl ?> >tbody >tr').each( (i, tr) => {
    $(tr)
    .addClass('pointer')
    .on( 'click', function( e) {
      e.stopPropagation();e.preventDefault();

      let _me = $(this);
      let _data = _me.data();

      window.location.href = _.url( '<?= $this->route ?>/view/' + _data.id);

    })

  });

}) (_brayworth_);
</script>
