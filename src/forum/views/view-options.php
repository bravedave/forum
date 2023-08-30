<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

/**
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
	<ul class="nav flex-column">
		<li class="nav-item"><a class="h5" href="<?= strings::url($this->route) ?>">forums</a></li>
		<li class="nav-item"><a class="nav-link" href="<?= strings::url($this->route . '/flagged') ?>">flagged</a></li>
		<li class="nav-item"><a class="nav-link" href="#" id="new-topic">new</a></li>
		<li class="nav-item"><a class="nav-link" href="#" id="clone-topic">clone</a></li>
		<?php
		if (currentUser::isAdmin()) {
			if ($dto->closed) {
				printf('<li class="nav-item"><a class="nav-link" href="%s">re-open</a></li>', strings::url('forum/reopenTopic/' . $dto->id));
			} else {
				printf('<li class="nav-item"><a class="nav-link" href="%s">close topic</a></li>', strings::url('forum/closeTopic/' . $dto->id));
			}
		}	?>

		<li class="nav-item mt-2">
			<a class="h6" href="<?= strings::url('forum/view/' . $dto->id) ?>"><?= sprintf('topic : #%s', $dto->id) ?></a>

		</li>

	</ul>

	<div class="form-group row">
		<div class="col">
			<div class="form-check">
				<!-- --[complete]-- -->
				<input class="form-check-input" type="checkbox" id="topic-is-complete" <?php if ($dto->complete) print "checked"; ?> />

				<label class="form-check-label" for="topic-is-complete">complete</label>

			</div>

			<div class="form-check">
				<!-- --[watch]-- -->
				<input class="form-check-input" type="checkbox" name="watch" id="watch" value="yes" data-role="watch-topic" <?= ($dto->subscribed(currentUser::email()) ? 'checked=checked' : '') ?>>
				<label class="form-check-label" for="watch">watch</label>

			</div>

			<!-- --[not resolved]-- -->
			<div class="form-check">
				<input class="form-check-input" type="radio" name="resolved" value="0" id="<?= $_uid = strings::rand() ?>-not" <?= 0 == $dto->resolved ? 'checked' : '' ?>>
				<label class="form-check-label" for="<?= $_uid ?>-not">not resolved</label>

			</div>

			<!-- --[resolved]-- -->
			<div class="form-check">
				<input class="form-check-input" type="radio" name="resolved" value="<?= config::resolved_resolved ?>" id="<?= $_uid ?>" <?= config::resolved_resolved == $dto->resolved ? 'checked' : '' ?>>
				<label class="form-check-label" for="<?= $_uid ?>">resolved</label>

			</div>

			<!-- --[resolved - no action]-- -->
			<div class="form-check">
				<input class="form-check-input" type="radio" name="resolved" value="<?= config::resolved_noaction ?>" id="<?= $_uid ?>-na" <?= config::resolved_noaction == $dto->resolved ? 'checked' : '' ?>>
				<label class="form-check-label" for="<?= $_uid ?>-na">no action</label>

			</div>

			<!-- --[resolved - feedback]-- -->
			<div class="form-check">
				<input class="form-check-input" type="radio" name="resolved" value="<?= config::resolved_feedback ?>" id="<?= $_uid ?>-feedback" <?= config::resolved_feedback == $dto->resolved ? 'checked' : '' ?>>
				<label class="form-check-label" for="<?= $_uid ?>-feedback">feedback</label>

			</div>
			<script>
				(_ =>
					$('#<?= $_uid ?>, #<?= $_uid ?>-na, #<?= $_uid ?>-not, #<?= $_uid ?>-feedback')
					.on('change', function(e) {
						let _me = $(this);
						_.post({
							url: _.url('<?= $this->route ?>'),
							data: {
								action: 'set-resolved',
								id: <?= (int)$dto->id ?>,
								val: _me.prop('checked') ? _me.val() : 0

							},

						}).then(d => _.growl(d));

					}))(_brayworth_);
			</script>

			<div class="form-check">
				<!-- --[flag]-- -->
				<input class="form-check-input" type="checkbox" name="flag" id="<?= $_uid = strings::rand() ?>" <?= $dto->flag ? 'checked' : '' ?>>
				<label class="form-check-label" for="<?= $_uid ?>">flag</label>

			</div>
			<script>
				(_ => $('#<?= $_uid ?>').on('change', function(e) {
					let _me = $(this);
					_.post({
						url: _.url('<?= $this->route ?>'),
						data: {
							action: 'set-flag',
							id: <?= (int)$dto->id ?>,
							val: _me.prop('checked') ? 1 : 0

						},

					}).then(d => _.growl(d));

				}))(_brayworth_);
			</script>

		</div>

	</div>

	<?php
	$uDao = new dao\users;
	foreach ($dto->subscribers() as $s) {
		if ($s == currentUser::email()) continue;	?>

		<div class="form-group row">
			<div class="col">
				<?php
				$uid = strings::rand();
				if ($u = $uDao->getUserByEmail($s)) {
					printf(
						'<div class="form-check">
				<input class="form-check-input" type="checkbox" name="watch" id="%s"
					value="yes" data-role="other-watch-topic" data-email="%s" checked %s>
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
				<input class="form-check-input" type="checkbox" name="watch" id="%s"
					value="yes" data-role="other-watch-topic" data-email="%s" checked %s>
				<label class="form-check-label" for="%s">
					%s

				</label>

			</div>',
						$uid,
						$s,
						(currentUser::isAdmin() ? '' : 'disabled'),
						$uid,
						$s
					);
				}

				?>
			</div>

		</div>
	<?php

	}	// foreach ( $dto->subscribers() as $s) {

	if (currentUser::isAdmin()) {
		if ($users = $uDao->getActive()) {
			$subs = array('<option>Subscribe a User</option>');
			foreach ($users as $u) {
				if (!$dto->subscribed($u->email)) {
					$subs[] = sprintf('<option value="%s">%s</option>', $u->email, $u->name);
				}
			}
			printf('<div class="form-group"><select data-role="add-subscriber" class="form-control">%s</select></div>', implode('', $subs));
		}
	}
	?>

	<div class="form-group">
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

	<div class="form-group">
		<label for="forum-tag">Tag:</label>

		<div class="input-group">

			<input type="text" name="tag" id="<?= $_uid = strings::rand()  ?>" class="form-control" value="<?= $dto->tag ?>" readonly />
			<button type="button" class="btn btn-light" id="<?= $_uid ?>pencil"><i class="bi bi-pencil"></i></button>
		</div>

	</div>
	<script>
		(_ => {
			$('#<?= $_uid ?>pencil').on('click', function(e) {
				e.stopPropagation();

				_.get.modal(_.url('<?= $this->route ?>/tag/<?= $dto->id ?>'))
					.then(modal => modal.on('success', (e, data) => $('#<?= $_uid ?>').val(data.tag)));

			});

		})(_brayworth_);
	</script>

	<div class="row g-2 <?= ($ideas ?? false) ? '' : 'd-none' ?>">
		<div class="col mb-2" id="<?= $_ideas = strings::rand() ?>">
			<label for="<?= $_uid = strings::rand() ?>">idea</label>
			<input type="text" class="form-control js-idea-idea" id="<?= $_uid ?>" value="<?= $dto->forum_idea_idea ?>">
		</div>
	</div>

	<div class="row g-2 d-none">
		<div class="col mb-2">
			<label for="forum-link">link</label>
			<input type="text" name="link" id="forum-link" class="form-control">
		</div>
	</div>

	<div class="row g-2">
		<div class="col mb-2 js-link-display"></div>
	</div>

	<script>
		(_ => {
			const form = $('#<?= $_form ?>');

			const unsubscribeOnCheck = function() {
				let me = this;
				if (!this.checked) {
					let em = $(this).data('email');
					$.getJSON(_.url('<?= $this->route ?>/unsubscribe/<?= $dto->id ?>?email=' + encodeURIComponent(em)), data => {
						_.growl('unsubscribed ' + em);
						$(me).closest('.form-check').remove();
					});
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
							$('.js-link-display', form).html('');
							if (d.data.length > 0) {
								let ul = $('<ul class="list-unstyled"></ul>');
								$('.js-link-display', form).append(ul);
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

			$(document).ready(() => {
				$('#topic-is-complete').on('change', function() {
					_.post({
						url: _.url('<?= $this->route ?>'),
						data: {
							action: $(this).prop('checked') ? 'mark-complete' : 'mark-incomplete',
							id: <?= $dto->id ?>

						}

					}).then(_.growl)

				});

				$('input[data-role="watch-topic"]').on('change', function() {
					if (this.checked) {
						$.getJSON(_.url('<?= $this->route ?>/subscribe/<?= $dto->id ?>'), d => {
							_.growl(d);

						});

					} else {
						$.getJSON(_.url('<?= $this->route ?>/unsubscribe/<?= $dto->id ?>'), d => {
							_.growl(d);

						});

					}

				});

				$('input[data-role="other-watch-topic"]').on('change', unsubscribeOnCheck);

				$('select[data-role="add-subscriber"]').on('change', function() {
					let _me = $(this);
					let em = _me.val();
					let name = _me.find('option[value="' + em + '"]').html();
					$.getJSON(_.url('<?= $this->route ?>/subscribe/<?= $dto->id ?>?email=' + encodeURIComponent(em)), data => {
						_.growl(data);
						if ('ack' == data.response) {
							let randomID = 'cj_' + parseInt(Math.random() * 5000);
							let formCheck = $('<div class="form-check"></div>');

							let formCheckInput = $('<input class="form-check-input" type="checkbox" data-email="' + em + '" checked />')
							formCheckInput.on('change', unsubscribeOnCheck);
							formCheckInput.attr('id', randomID);
							formCheck.append(formCheckInput).append('<label class="form-check-label" for="' + randomID + '">' + name + '</label>');

							formCheck.insertBefore(_me.closest('.form-group'));

						}

					});

					$('option', this).each(function(i, el) {
						if ($(el).prop('value') == em)
							$(el).remove();

					});

				})

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

				if (!!window._cms_) {
					$('#forum-link')
						.closest('.form-row')
						.removeClass('d-none');

					$('#forum-link').autofill({
						source: _cms_.search.forum,
						select: (event, ui) => {
							_.post({
									url: _.url('<?= $this->route ?>'),
									data: {
										action: 'add-link',
										id: <?= $dto->id ?>,
										link: ui.item.id,

									}
								})
								.then(d => {
									_.growl(d);
									$('#forum-link').val('');
									showLinks(<?= $dto->id ?>);
								});
						}
					});
				}

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

				$('.js-idea-idea', form)
					.on('focus', function(e) {
						$(this).select();
					})
					.autofill({
						source: (request, response) => {
							_.post({
								url: _.url('<?= $this->route ?>'),
								data: {
									action: 'idea-search',
									term: request.term,
								},
							}).then(response);
						},
						select: (event, ui) => {
							_.post({
									url: _.url('<?= $this->route ?>'),
									data: {
										action: 'idea-set',
										id: <?= $dto->id ?>,
										forum_idea_id: ui.item.id,

									}
								})
								.then(_.growl);
						}

					});
			});
		})(_brayworth_);
	</script>
</form>