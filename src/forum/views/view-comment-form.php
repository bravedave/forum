<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/	?>

<input type="hidden" name="form_action" value="post comment">
<div id="commentForm" class="d-print-none" data-role="comment-container" data-master="yes">
	<div class="form-row">
		<div class="col pl-md-0">
			<i id="<?= $_uidShowCommentBox = strings::rand() ?>" class="bi bi-caret-right"></i>
			Add your Comment
		</div>
	</div>

	<div class="form-row d-none" data-comment-body>
		<div class="col pl-md-0">
			<input type="hidden" name="parent" value="<?= $this->data->dto->id ?>" />
			<input type="hidden" id="commentThread" name="thread" data-default="<?= $this->data->dto->id ?>" value="<?= $this->data->dto->id ?>" />

			<div class="form-row">
				<div class="col mb-2">
					<textarea data-role="comment" name="comment" id="<?= $uid = strings::rand() ?>" class="form-control" rows="16" placeholder="add your comment"></textarea>

				</div>

			</div>

			<div class="form-row">
				<div class="col">
					<button type="submit" class="btn btn-primary" id="<?= $uid ?>form_button">post comment</button>

				</div>

			</div>

		</div>

	</div>

</div>
<script>
	(_ => $(document).ready(() => {
		let editorDone = false;

		$('#<?= $_uidShowCommentBox ?>').parent().addClass('pointer').on('click', function(e) {
			e.stopPropagation();
			e.preventDefault();

			let _el = $('#<?= $_uidShowCommentBox ?>');

			if (_el.data('state') == 'open') {
				_el
					.removeClass('bi-caret-down')
					.addClass('bi-caret-right')
					.data('state', 'hidden');
				$('#commentForm [data-comment-body]').addClass('d-none');

			} else {
				_el
					.removeClass('bi-caret-right')
					.addClass('bi-caret-down')
					.data('state', 'open');
				$('#commentForm [data-comment-body]').removeClass('d-none');

				if (!editorDone) {
					editorDone = true;

					_.tiny().then(() => {
						let j = {
							browser_spellcheck: true,
							document_base_url: _.url('', true),
							menubar: false,
							paste_data_images: true,
							plugins: 'paste imagetools table autolink lists',
							selector: '#<?= $uid ?>',
							statusbar: false,
							toolbar: 'undo redo | done | bold italic | bullist numlist outdent indent blockquote',

						};

						j.setup = ed => {
							ed.ui.registry.addButton('done', {
								tooltip: 'all done',
								text: '&check;',
								onAction: () => ed.insertContent('done, verify and mark complete')

							})
						};

						tinymce.init(j);

					});

				}

			}

		});

		$('#<?= $uid ?>dvmc')
			.on('click', function(e) {
				//~ tinymce.EditorManager.execCommand('mceRemoveEditor',true, editor_id);
				tinyMCE.execCommand('mceRemoveEditor', false, '<?= $uid ?>');

				// $('#<?= $uid ?>').val('Done, verify and mark complete');
				$('#<?= $uid ?>form_button').click();

			});

	}))(_brayworth_);
</script>