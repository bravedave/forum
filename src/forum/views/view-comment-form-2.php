<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/	?>

<div id="commentForm" class="d-print-none" data-role="comment-container" data-master="yes">
  <div class="row">
    <div class="offset-lg-1 offset-md-2 col pl-md-0">
      <i id="show-comment-box" class="bi bi-caret-right"></i>
      Add your Comment

    </div>

  </div>

  <div class="row d-none" data-comment-body>
    <div class="offset-lg-1 offset-md-2 col pl-md-0">
      <input type="hidden" name="parent" value="<?= $this->data->dto->id ?>" />
      <input type="hidden" id="commentThread" name="thread" data-default="<?= $this->data->dto->id ?>" value="<?= $this->data->dto->id ?>" />

      <div class="form-group row">
        <div class="col">
          <textarea data-role="comment" name="comment" id="<?= $uid = uniqid('cms_') ?>"
            class="form-control"
            rows="10" placeholder="add your comment"></textarea>

        </div>

      </div>

      <div class="form-group row">
        <div class="col">
          <input class="btn btn-primary" type="submit" name="form_action" id="<?= $uid ?>form_button" value="post comment" />
          <?php if ( currentUser::isDavid()) {	?>
          <button class="btn btn-outline-primary" type="button" id="<?= $uid ?>dvmc">Done, verify and mark complete</button>

          <?php }	?>

        </div>

      </div>

    </div>

  </div>

</div>
<script>
( _ => $(document).ready( () => {
	let editorDone = false;

	$('#show-comment-box').parent().addClass('pointer').on( 'click', function( e) {
		e.stopPropagation(); e.preventDefault();

		var _el = $('#show-comment-box', this);

		if ( _el.data('state') == 'open') {
			_el.removeClass('bi-caret-down').addClass('bi-caret-right');
			_el.data('state', 'hidden');
			$( '#commentForm [data-comment-body]').addClass('d-none');

		}
		else {
			_el.removeClass('bi-caret-right').addClass('bi-caret-down');
			_el.data('state', 'open');
			$( '#commentForm [data-comment-body]').removeClass('d-none');

			if ( !editorDone) {
				editorDone = true;

				_.tiny().then( () => {
					tinymce.init({
						browser_spellcheck : true,
						document_base_url : _.url('',true),
						menubar : false,
						plugins: 'paste imagetools table autolink lists link',
						selector: '#<?= $uid ?>',
						statusbar : false,
						toolbar: 'undo redo | bold italic | bullist numlist outdent indent blockquote',

					});

				});

			}

		}

	});

	$('#<?= $uid ?>dvmc').on( 'click', function( e) {
		//~ tinymce.EditorManager.execCommand('mceRemoveEditor',true, editor_id);
		tinyMCE.execCommand('mceRemoveEditor', false, '<?= $uid ?>');

		$('#<?= $uid ?>').val('Done, verify and mark complete');
		$('#<?= $uid ?>form_button').click();

	});

}))( _brayworth_);
</script>
