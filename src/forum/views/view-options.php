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
use green;	?>

<ul class="nav flex-column">
	<li class="nav-item"><a class="h5" href="<?= strings::url($this->route) ?>">forums</a></li>
	<li class="nav-item"><a class="nav-link" href="<?= strings::url($this->route . '/flagged') ?>">flagged</a></li>
	<li class="nav-item"><a class="nav-link" href="#" id="new-topic">new</a></li>
	<li class="nav-item"><a class="nav-link" href="#" id="clone-topic">clone</a></li>
	<?php
	if (currentUser::isAdmin()) {
		if ($this->data->dto->closed) {
			printf('<li class="nav-item"><a class="nav-link" href="%s">re-open</a></li>', strings::url('forum/reopenTopic/' . $this->data->dto->id));
		} else {
			printf('<li class="nav-item"><a class="nav-link" href="%s">close topic</a></li>', strings::url('forum/closeTopic/' . $this->data->dto->id));
		}
	}	?>

	<li class="nav-item mt-2">
		<a class="h6" href="<?= strings::url('forum/view/' . $this->data->dto->id) ?>"><?= sprintf('topic : #%s', $this->data->dto->id) ?></a>

	</li>

</ul>

<div class="form-group row">
	<div class="col">
		<div class="form-check">
			<!-- --[complete]-- -->
			<input class="form-check-input" type="checkbox" id="topic-is-complete" <?php if ($this->data->dto->complete) print "checked"; ?> />

			<label class="form-check-label" for="topic-is-complete">complete</label>

		</div>

		<div class="form-check">
			<!-- --[watch]-- -->
			<input class="form-check-input" type="checkbox" name="watch" id="watch" value="yes" data-role="watch-topic" <?= ($this->data->dto->subscribed(currentUser::email()) ? 'checked=checked' : '') ?>>
			<label class="form-check-label" for="watch">watch</label>

		</div>

		<!-- --[not resolved]-- -->
		<div class="form-check">
			<input class="form-check-input" type="radio" name="resolved" value="0" id="<?= $_uid = strings::rand() ?>-not" <?= 0 == $this->data->dto->resolved ? 'checked' : '' ?>>
			<label class="form-check-label" for="<?= $_uid ?>-not">not resolved</label>

		</div>

		<!-- --[resolved]-- -->
		<div class="form-check">
			<input class="form-check-input" type="radio" name="resolved" value="<?= config::resolved_resolved ?>" id="<?= $_uid = strings::rand() ?>" <?= config::resolved_resolved == $this->data->dto->resolved ? 'checked' : '' ?>>
			<label class="form-check-label" for="<?= $_uid ?>">resolved</label>

		</div>

		<!-- --[resolved - no action]-- -->
		<div class="form-check">
			<input class="form-check-input" type="radio" name="resolved" value="<?= config::resolved_noaction ?>" id="<?= $_uid ?>-na" <?= config::resolved_noaction == $this->data->dto->resolved ? 'checked' : '' ?>>
			<label class="form-check-label" for="<?= $_uid ?>-na">no action</label>

		</div>
		<script>
			(_ => {
				$('#<?= $_uid ?>, #<?= $_uid ?>-na, #<?= $_uid ?>-not')
					.on('change', function(e) {
						let _me = $(this);
						_.post({
							url: _.url('<?= $this->route ?>'),
							data: {
								action: 'set-resolved',
								id: <?= (int)$this->data->dto->id ?>,
								val: _me.prop('checked') ? _me.val() : 0

							},

						}).then(d => _.growl(d));

					});

			})(_brayworth_);
		</script>

		<div class="form-check">
			<!-- --[flag]-- -->
			<input class="form-check-input" type="checkbox" name="flag" id="<?= $_uid = strings::rand() ?>" <?= $this->data->dto->flag ? 'checked' : '' ?>>
			<label class="form-check-label" for="<?= $_uid ?>">flag</label>

		</div>
		<script>
			(_ => {
				$('#<?= $_uid ?>').on('change', function(e) {
					let _me = $(this);
					_.post({
						url: _.url('<?= $this->route ?>'),
						data: {
							action: 'set-flag',
							id: <?= (int)$this->data->dto->id ?>,
							val: _me.prop('checked') ? 1 : 0

						},

					}).then(d => _.growl(d));

				});

			})(_brayworth_);
		</script>

	</div>

</div>

<?php
$uDao = new green\users\dao\users;
foreach ($this->data->dto->subscribers() as $s) {
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

}	// foreach ( $this->data->dto->subscribers() as $s) {

if (currentUser::isAdmin()) {
	if ($users = $uDao->getActive()) {
		$subs = array('<option>Subscribe a User</option>');
		foreach ($users as $u) {
			if (!$this->data->dto->subscribed($u->email)) {
				$subs[] = sprintf('<option value="%s">%s</option>', $u->email, $u->name);
			}
		}
		printf('<div class="form-group"><select data-role="add-subscriber" class="form-control">%s</select></div>', implode('', $subs));
	}
}
?>

<div class="form-group">
	<label for="topic-priority">Priority</label>

	<select id="topic-priority" class="form-control"><?php
																										$pri = config::FORUM_NORMAL_PRIORITY;
																										if ($this->data->dto->priority &&  (strstr(config::FORUM_PRIORITY_ALL_VALID, $this->data->dto->priority) !== FALSE))
																											$pri = $this->data->dto->priority;
																										?>

		<option value="<?= config::FORUM_BROKEN_PRIORITY ?>" <?php if ($pri == config::FORUM_BROKEN_PRIORITY) print 'selected'; ?>><?= config::FORUM_BROKEN_PRIORITY_TEXT ?></option>
		<option value="<?= config::FORUM_URGENT_PRIORITY ?>" <?php if ($pri == config::FORUM_URGENT_PRIORITY) print 'selected'; ?>><?= config::FORUM_URGENT_PRIORITY_TEXT ?></option>
		<option value="<?= config::FORUM_HIGH_PRIORITY ?>" <?php if ($pri == config::FORUM_HIGH_PRIORITY) print 'selected'; ?>><?= config::FORUM_HIGH_PRIORITY_TEXT ?></option>
		<option value="<?= config::FORUM_MEDIUM_PRIORITY ?>" <?php if ($pri == config::FORUM_MEDIUM_PRIORITY) print 'selected'; ?>><?= config::FORUM_MEDIUM_PRIORITY_TEXT ?></option>
		<option value="<?= config::FORUM_NORMAL_PRIORITY ?>" <?php if ($pri == config::FORUM_NORMAL_PRIORITY) print 'selected'; ?>><?= config::FORUM_NORMAL_PRIORITY_TEXT ?></option>
		<option value="<?= config::FORUM_LOW_PRIORITY ?>" <?php if ($pri == config::FORUM_LOW_PRIORITY) print 'selected'; ?>><?= config::FORUM_LOW_PRIORITY_TEXT ?></option>

	</select>

</div>

<div class="form-group">
	<label for="forum-tag">Tag:</label>

	<div class="input-group">
		<input type="text" name="tag" id="<?= $_uid = strings::rand()  ?>" class="form-control" value="<?= $this->data->dto->tag ?>" readonly />

		<div class="input-group-prepend">
			<button type="button" class="btn input-group-text" id="<?= $_uid ?>pencil"><i class="bi bi-pencil"></i></button>

		</div>

	</div>

</div>
<script>
	(_ => {
		$('#<?= $_uid ?>pencil').on('click', function(e) {
			e.stopPropagation();

			let url = _.url('<?= $this->route ?>/tag/<?= $this->data->dto->id ?>');
			_.get.modal(url)
				.then(modal => modal.on('success', (e, data) => {
					$('#<?= $_uid ?>').val(data.tag);
					// console.log( data);

				}));

		});

	})(_brayworth_);
</script>

<div class="row">
	<div class="col" id="link-display"></div>
</div>

<div class="form-group">
	<label for="forum-link">Link:</label>

	<input type="text" name="link" id="forum-link" class="form-control" />

</div>

<script>
	(_ => $(document).ready(() => {
		$('#topic-is-complete').on('change', function() {
			_.post({
				url: _.url('<?= $this->route ?>'),
				data: {
					action: $(this).prop('checked') ? 'mark-complete' : 'mark-incomplete',
					id: <?= $this->data->dto->id ?>

				}

			}).then(_.growl)

		});

		$('input[data-role="watch-topic"]').on('change', function() {
			if (this.checked) {
				$.getJSON(_.url('<?= $this->route ?>/subscribe/<?= $this->data->dto->id ?>'), d => {
					_.growl(d);

				});

			} else {
				$.getJSON(_.url('<?= $this->route ?>/unsubscribe/<?= $this->data->dto->id ?>'), d => {
					_.growl(d);

				});

			}

		});

		let unsubscribeOnCheck = function() {
			var me = this;
			if (!this.checked) {
				var em = $(this).data('email');
				//~ console.log( 'change:unsubscribe' );
				$.getJSON(_.url('<?= $this->route ?>/unsubscribe/<?= $this->data->dto->id ?>?email=' + encodeURIComponent(em)), data => {
					_.growl('unsubscribed ' + em);
					$(me).closest('.form-check').remove();

				});

			}

		}

		$('input[data-role="other-watch-topic"]').on('change', unsubscribeOnCheck);

		$('select[data-role="add-subscriber"]').on('change', function() {
			var _me = $(this);
			var em = _me.val();
			var name = _me.find('option[value="' + em + '"]').html();
			$.getJSON(_.url('<?= $this->route ?>/subscribe/<?= $this->data->dto->id ?>?email=' + encodeURIComponent(em)), data => {
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
						id: <?= (int)$this->data->dto->id ?>,
						priority: $(this).val(),
					}

				})
				.done(_.growl);

		});

		if (!!window._cms_) {
			$('#forum-link').autofill({
				source: _cms_.search.forum,
				select: function(event, ui) {
					_.post({
							url: _.url('<?= $this->route ?>'),
							data: {
								action: 'add-link',
								id: <?= $this->data->dto->id ?>,
								link: ui.item.id,

							}

						})
						.then(d => {
							_.growl(d);
							$('#forum-link').val('');
							showLinks(<?= $this->data->dto->id ?>);

						});

				}

			});

		}

		let showLinks = function(id) {
			_.post({
					url: _.url('<?= $this->route ?>'),
					data: {
						action: 'get-links',
						id: id,

					}

				})
				.then(function(d) {
					if ('ack' == d.response) {
						if (d.data.length > 0) {
							let ul = $('<ul class="list-unstyled"></ul>');
							$.each(d.data, function(i, el) {
								let link = $('<a></a>').html(el.description).attr('href', _.url('<?= $this->route ?>/view/' + el.id));
								let trash = $('<a href="#"><i class="bi bi-trash pull-right"></i></a>').on('click', function(e) {
									e.stopPropagation();
									e.preventDefault();
									let _me = $(this);

									_.post({
											url: _.url('<?= $this->route ?>'),
											data: {
												action: 'remove-link',
												id: <?= $this->data->dto->id ?>,
												link: el.id,

											}

										})
										.then(function(d) {
											_.growl(d);
											_me.closest('li').remove();

										});

								});

								$('<li></li>').append(trash).append(link).appendTo(ul)

							});
							$('#link-display').html('').append(ul);

						} else {
							$('#link-display').html('');
						}
					} else {
						_.growl(d);
					}

				});

		}

		showLinks(<?= $this->data->dto->id ?>);

		$('#new-topic').on('click', function(e) {
			e.stopPropagation();
			e.preventDefault();

			_.get.modal(_.url('<?= $this->route ?>/add/'))
				.then(modal => {
					console.log(modal.closest('form'));
					$('input[name="link"]', modal.closest('form')).val(<?= $this->data->dto->id ?>);
					modal.on('success', e => showLinks(<?= $this->data->dto->id ?>));

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

	}))(_brayworth_);
</script>