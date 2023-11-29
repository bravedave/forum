<?php
/*
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

use currentUser, dao;

extract((array)$this->data);	?>

<form id="<?= $_form = strings::rand() ?>">

	<nav class="nav flex-column">

		<a class="h5" href="<?= strings::url($this->route) ?>">forums</a>
		<a class="nav-link" href="<?= strings::url($this->route . '/flagged') ?>">flagged</a>
		<a class="nav-link" href="#" id="new-topic">new</a>
		<a class="nav-link" href="#" id="clone-topic">clone</a>
		<?php
		if (currentUser::isAdmin()) {

			if ($dto->closed) {

				printf('<a class="nav-link" href="%s">re-open</a>', strings::url('forum/reopenTopic/' . $dto->id));
			} else {

				printf('<a class="nav-link" href="%s">close topic</a>', strings::url('forum/closeTopic/' . $dto->id));
			}
		}	?>

		<li class="nav-item mt-2">

			<a class="h6" href="<?= strings::url('forum/view/' . $dto->id) ?>"><?= sprintf('topic : #%s', $dto->id) ?></a>
		</li>
	</nav>

	<div class="mb-2">

		<!-- --[complete]-- -->
		<div class="form-check">

			<input class="form-check-input js-complete" type="checkbox" id="<?= $_uid = strings::rand() ?>" <?= $dto->complete ? 'checked' : '' ?>>
			<label class="form-check-label" for="<?= $_uid ?>">complete</label>
		</div>

		<!-- --[watch]-- -->
		<div class="form-check">

			<input class="form-check-input js-watch" type="checkbox" id="<?= $_uid = strings::rand() ?>" value="yes" <?= ($dto->subscribed(currentUser::email()) ? 'checked=checked' : '') ?>>
			<label class="form-check-label" for="<?= $_uid ?>">watch</label>
		</div>

		<div class="form-check">

			<input class="form-check-input js-status" type="radio" name="resolved" value="0" id="<?= $_uid = strings::rand() ?>" <?= 0 == $dto->resolved ? 'checked' : '' ?>>
			<label class="form-check-label" for="<?= $_uid ?>">not resolved</label>
		</div>

		<div class="form-check">

			<input class="form-check-input js-status" type="radio" name="resolved" value="<?= config::resolved_resolved ?>" id="<?= $_uid = strings::rand() ?>" <?= config::resolved_resolved == $dto->resolved ? 'checked' : '' ?>>
			<label class="form-check-label" for="<?= $_uid ?>">resolved</label>
		</div>

		<div class="form-check">

			<input class="form-check-input js-status" type="radio" name="resolved" value="<?= config::resolved_noaction ?>" id="<?= $_uid = strings::rand() ?>" <?= config::resolved_noaction == $dto->resolved ? 'checked' : '' ?>>
			<label class="form-check-label" for="<?= $_uid ?>">no action</label>
		</div>

		<div class="form-check">

			<input class="form-check-input js-status" type="radio" name="resolved" value="<?= config::resolved_feedback ?>" id="<?= $_uid = strings::rand() ?>" <?= config::resolved_feedback == $dto->resolved ? 'checked' : '' ?>>
			<label class="form-check-label" for="<?= $_uid ?>">feedback</label>
		</div>

		<div class="form-check">

			<!-- --[flag]-- -->
			<input class="form-check-input js-flag" type="checkbox" name="flag" id="<?= $_uid = strings::rand() ?>" <?= $dto->flag ? 'checked' : '' ?>>
			<label class="form-check-label" for="<?= $_uid ?>">flag</label>
		</div>
	</div>

	<div class="mb-2 js-subscriber-list">
		<?php
		$uDao = new dao\users;
		foreach ($dto->subscribers() as $s) {

			if ($s == currentUser::email()) continue;

			$uid = strings::rand();
			if ($u = $uDao->getUserByEmail($s)) {

				printf(
					'<div class="form-check">
						<input class="form-check-input js-other-watch" type="checkbox" id="%s"
							value="yes" data-email="%s" checked %s>
						<label class="form-check-label" for="%s">%s</label>
					</div>',
					$uid,
					$s,
					(currentUser::isAdmin() ? '' : 'disabled'),
					$uid,
					$u->name
				);
			} else {

				printf(
					'<div class="form-check">
						<input class="form-check-input js-other-watch" type="checkbox" id="%s"
							value="yes" data-email="%s" checked %s>
						<label class="form-check-label" for="%s">%s</label>
					</div>',
					$uid,
					$s,
					(currentUser::isAdmin() ? '' : 'disabled'),
					$uid,
					$s
				);
			}
		}	?>
	</div>

	<?php
	if (currentUser::isAdmin()) {

		if ($users = $uDao->getActive()) {

			print '<div class="mb-2">
				<select class="form-control js-add-subscriber">
					<option>Subscribe a User</option>
				';

			foreach ($users as $u) {

				if ($dto->subscribed($u->email)) continue;
				printf('<option value="%s">%s</option>', $u->email, $u->name);
			}

			print '</select>
				</div>';
		}
	} ?>

	<div class="mb-2">

		<label for="topic-priority">Priority</label>
		<select id="topic-priority" class="form-control">
			<?php
			$pri = config::FORUM_NORMAL_PRIORITY;
			if ($dto->priority &&  (strstr(config::FORUM_PRIORITY_ALL_VALID, $dto->priority) !== FALSE))
				$pri = $dto->priority;
			?>

			<option value="<?= config::FORUM_BROKEN_PRIORITY ?>" <?= $pri == config::FORUM_BROKEN_PRIORITY ? 'selected' : '' ?>><?= config::FORUM_BROKEN_PRIORITY_TEXT ?></option>
			<option value="<?= config::FORUM_URGENT_PRIORITY ?>" <?= $pri == config::FORUM_URGENT_PRIORITY ? 'selected' : '' ?>><?= config::FORUM_URGENT_PRIORITY_TEXT ?></option>
			<option value="<?= config::FORUM_HIGH_PRIORITY ?>" <?= $pri == config::FORUM_HIGH_PRIORITY ? 'selected' : '' ?>><?= config::FORUM_HIGH_PRIORITY_TEXT ?></option>
			<option value="<?= config::FORUM_MEDIUM_PRIORITY ?>" <?= $pri == config::FORUM_MEDIUM_PRIORITY ? 'selected' : '' ?>><?= config::FORUM_MEDIUM_PRIORITY_TEXT ?></option>
			<option value="<?= config::FORUM_NORMAL_PRIORITY ?>" <?= $pri == config::FORUM_NORMAL_PRIORITY ? 'selected' : '' ?>><?= config::FORUM_NORMAL_PRIORITY_TEXT ?></option>
			<option value="<?= config::FORUM_LOW_PRIORITY ?>" <?= $pri == config::FORUM_LOW_PRIORITY ? 'selected' : '' ?>><?= config::FORUM_LOW_PRIORITY_TEXT ?></option>

		</select>
	</div>

	<div class="mb-2">

		<div>tag</div>
		<div class="input-group">

			<input type="text" name="tag" class="form-control" value="<?= $dto->tag ?>" readonly>
			<button type="button" class="btn btn-light js-tag-pencil"><i class="bi bi-pencil"></i></button>
		</div>
	</div>

	<div class="mb-2 <?= ($ideas ?? false) ? '' : 'd-none' ?>">

		<div>idea</div>
		<input type="text" class="form-control js-idea-idea" value="<?= $dto->forum_idea_idea ?>">
	</div>

	<div class="mb-2 js-forum-link-cell">

		<label for="<?= $_uid = strings::rand()  ?>">link</label>
		<input class="form-control" type="text" name="link" id="<?= $_uid ?>">
	</div>

	<div class="mb-2 js-link-display"></div>

	<div class="mb-2 js-file-upload"></div>

	<div class="mb-2 js-file-list"></div>

	<script>
		(_ => {
			const form = $('#<?= $_form ?>');

			const unsubscribeOnCheck = function() {

				if (!this.checked) {

					_.fetch
						.post(_.url('<?= $this->route ?>'), {
							action: 'unsubscribe',
							id: <?= $dto->id ?>,
							email: $(this).data('email'),
						})
						.then(d => {

							_.growl(d);
							if ('ack' == d.response) {

								$(this).closest('.form-check').remove();
							}
						});
					// let em = this.dataset.email;
					// $.getJSON(_.url('<?= $this->route ?>/unsubscribe/<?= $dto->id ?>?email=' + encodeURIComponent(em)), data => {

					// 	_.growl('unsubscribed ' + em);
					// 	$(this).closest('.form-check').remove();
					// });
				}
			};

			const showLinks = id => {
				_.post({
						url: _.url('<?= $this->route ?>'),
						data: {
							action: 'get-links',
							id: id,
						}
					})
					.then(d => {
						if ('ack' == d.response) {
							form.find('.js-link-display').html('');
							if (d.data.length > 0) {
								let ul = $('<ul class="list-unstyled"></ul>');
								form.find('.js-link-display').append(ul);
								$.each(d.data, (i, el) => {

									$(`<li class="d-flex"></li>`)
										.append(`<a href="${_.url('<?= $this->route ?>/view/' + el.id)}">${el.description}</a>`)
										.append($('<a href="#" class="ms-auto"><i class="bi bi-trash"></i></a>').on('click', function(e) {
											e.stopPropagation();
											e.preventDefault();
											let _me = $(this);

											_.post({
													url: _.url('<?= $this->route ?>'),
													data: {
														action: 'remove-link',
														id: <?= $dto->id ?>,
														link: el.id,
													}
												})
												.then(d => {
													_.growl(d);
													_me.closest('li').remove();
												});
										}))
										.appendTo(ul)
								});
							}
						} else {
							_.growl(d);
						}
					});
			};

			form.find('.js-status')
				.on('change', function(e) {

					let _me = $(this);

					_.fetch
						.post(_.url('<?= $this->route ?>'), {
							action: 'set-resolved',
							id: <?= (int)$dto->id ?>,
							val: this.checked ? _me.val() : 0
						})
						.then(_.growl);
				});

			form.find('.js-complete')
				.on('change', function() {

					_.fetch
						.post(_.url('<?= $this->route ?>'), {
							action: this.checked ? 'mark-complete' : 'mark-incomplete',
							id: <?= $dto->id ?>
						})
						.then(_.growl)
				});

			form.find('.js-tag-pencil')
				.on('click', e => {

					e.stopPropagation();

					_.get.modal(_.url('<?= $this->route ?>/tag/<?= $dto->id ?>'))
						.then(m => m.on('success', (e, data) => form.find('input[name="tag"]').val(data.tag)));
				});

			form.find('.js-idea-idea')
				.on('focus', function(e) {

					$(this).select();
				})
				.autofill({
					source: (request, response) => {

						_.fetch
							.post(_.url('<?= $this->route ?>'), {
								action: 'idea-search',
								term: request.term
							})
							.then(response);
					},
					select: (event, ui) => {

						_.fetch
							.post(_.url('<?= $this->route ?>'), {
								action: 'idea-set',
								id: <?= $dto->id ?>,
								forum_idea_id: ui.item.id,
							})
							.then(_.growl);
					}
				});

			form.find('.js-watch')
				.on('change', function() {

					_.fetch
						.post(_.url('<?= $this->route ?>'), {
							action: this.checked ? 'subscribe' : 'unsubscribe',
							id: <?= (int)$dto->id ?>
						})
						.then(_.growl);

					// if (this.checked) {
					// 	$.getJSON(_.url('<?= $this->route ?>/subscribe/<?= $dto->id ?>'), _.growl);
					// } else {
					// 	$.getJSON(_.url('<?= $this->route ?>/unsubscribe/<?= $dto->id ?>'), _.growl);
					// }
				});

			form.find('.js-flag')
				.on('change', function(e) {

					_.fetch
						.post(_.url('<?= $this->route ?>'), {
							action: 'set-flag',
							id: <?= (int)$dto->id ?>,
							val: this.checked ? 1 : 0
						}).then(_.growl);
				});

			form.find('.js-other-watch').on('change', unsubscribeOnCheck);

			if (!!window._cms_) {

				form.find('.js-forum-link-cell').removeClass('d-none');

				form.find('[name="link"]')
					.autofill({
						source: _cms_.search.forum,
						select: (event, ui) => {

							_.fetch.post(_.url('<?= $this->route ?>'), {
									action: 'add-link',
									id: <?= $dto->id ?>,
									link: ui.item.id,
								})
								.then(d => {

									_.growl(d);
									form.find('[name="link"]').val('');
									showLinks(<?= $dto->id ?>);
								});
						}
					});
			}

			form.find('select.js-add-subscriber')
				.on('change', function() {

					let _me = $(this);
					let em = _me.val();
					let name = _me.find(`option[value="${em}"]`).html();

					_.fetch
						.post(_.url('<?= $this->route ?>'), {
							action: 'subscribe',
							id: <?= (int)$dto->id ?>,
							email: em,
						})
						.then(d => {

							_.growl(d);
							if ('ack' == d.response) {

								let uid = _.randomString();
								form.find('.js-subscriber-list')
									.append(
										`<div class="form-check">
											<input class="form-check-input" type="checkbox" id="${uid}" data-email="${_.encodeHTMLEntities(em)}" checked>
											<label class="form-check-label" for="${uid}">${_.encodeHTMLEntities(name)}</label>
										</div>`);

								$(`#${uid}`).on('change', unsubscribeOnCheck);
							}
						});

					// $.getJSON(_.url('<?= $this->route ?>/subscribe/<?= $dto->id ?>?email=' + encodeURIComponent(em)), data => {
					// 	_.growl(data);
					// 	if ('ack' == data.response) {

					// 		let uid = _.randomString();
					// 		let chk = $('<input class="form-check-input" type="checkbox" checked>')
					// 			.attr({
					// 				'data-email': em,
					// 				'id': uid
					// 			})
					// 			.on('change', unsubscribeOnCheck);

					// 		form.find('.js-subscriber-list')
					// 			.append(
					// 				$(`<div class="form-check">
					// 					<label class="form-check-label" for="${uid}">${_.encodeHTMLEntities(name)}</label>
					// 				</div>`)
					// 				.prepend(chk)
					// 			);
					// 	}
					// });

					_me.find('option').each((i, el) => {

						let _el = $(el);
						if (_el.prop('value') == em) _el.remove();
					});
				})

			let c = _.fileDragDropContainer({
				fileControl: true,
				accept: '.csv,text/csv,application/vnd.ms-excel,image/jpeg,image/png,application/pdf'
			});

			form.find('.js-file-upload').append(c);

			_.fileDragDropHandler.call(c, {
				url: _.url('<?= $this->route ?>'),
				queue: false,
				multiple: false,
				postData: {
					action: 'forum-attachment-upload',
					id: <?= (int)$dto->id ?>
				},
				onError: d => form.find('.js-file-upload').html(`<div class="alert alert-danger m-1">${d.description}</div>`),
				onReject: d => _.growl,
				onUpload: d => {

					_.growl(d)
					if ('ack' == d.response) form.find('.js-file-list').trigger('update');
				}
			});

			form.find('.js-file-list')
				.on('update', function(e) {

					e.stopPropagation();

					let _me = $(this);

					// add a spinner
					_me
						.addClass('text-center p-2')
						.html('<i class="bi bi-arrow-clockwise"></i>');

					_.fetch
						.post(_.url('<?= $this->route ?>'), {
							action: 'get-attachments',
							id: <?= (int)$dto->id ?>
						})
						.then(d => {

							_me
								.removeClass('text-center p-2')
								.html('');

							let ul = $('<ul class="list-unstyled"></ul>');
							if ('ack' == d.response) {

								$.each(d.data, (i, file) => {

									let li = $('<li class="d-flex"></li>');
									li.append(
										$(`<a href="${_.url('<?= $this->route ?>/download/<?= $dto->id ?>?f=' + file.file)}" target="_blank" class="me-auto">${file.file}</a>`)
									);

									<?php if (currentUser::isAdmin()) {	?>

										li.append(
											$('<a href="#" class="ms-auto"><i class="bi bi-trash"></i></a>')
											.on('click', function(e) {
												e.stopPropagation();
												e.preventDefault();

												let _me = $(this);

												_.fetch.post(_.url('<?= $this->route ?>'), {
														action: 'forum-attachment-remove',
														id: <?= $dto->id ?>,
														file: file.file,

													})
													.then(d => {
														_.growl(d);
														_me.closest('li').remove();
													});
											})
										);
									<?php }	?>

									ul.append(li);
								});

								_me.append(ul);
							} else {

								_.growl(d);
							}
						});
				})
				.trigger('update');

			_.ready(() => {

				$('#topic-priority').on('change', function() {

					_.post({
							url: _.url('<?= $this->route ?>'),
							data: {
								action: 'prioritise',
								id: <?= (int)$dto->id ?>,
								priority: $(this).val(),
							}
						})
						.done(_.growl);
				});

				showLinks(<?= $dto->id ?>);

				$('#new-topic').on('click', function(e) {
					e.stopPropagation();
					e.preventDefault();

					_.get.modal(_.url('<?= $this->route ?>/add/'))
						.then(modal => {
							console.log(modal.closest('form'));
							$('input[name="link"]', modal.closest('form')).val(<?= $dto->id ?>);
							modal.on('success', e => showLinks(<?= $dto->id ?>));

						});

				});

				$('#clone-topic').on('click', function(e) {
					e.stopPropagation();
					e.preventDefault();

					let data = {
						description: '',
						comment: $('#initial-forum-comment').text(),
					}
					let fld = $('input', '#forum-subject');
					fld.length > 0 ?
						data.description = fld.val() :
						data.description = $('#forum-subject').text();

					_.get.modal(_.url('<?= $this->route ?>/add/'))
						.then(modal => {
							$('input[name="description"]', modal).val(data.description);
							$('textarea[name="comment"]', modal).val(data.comment);

						});

				});

			});
		})(_brayworth_);
	</script>
</form>