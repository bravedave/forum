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

<div class="form-row">
  <div class="col border p-2"><?= strings::text2html($dto->data) ?></div>
</div>
<script>
  (_ => {
    $(document).ready(() => {

      document.title = <?= json_encode(sprintf('%s - %s', config::label_view, $dto->idea)); ?>;

    });
  })(_brayworth_);
</script>