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
use strings;
use sys;

	if ( $this->comments )
		printf( '<form method="post" action="%s">', strings::url('forum/'));

	?>
<div data-role="controls" class="row mb-2">
	<div class="col">
		<div id="forum-subject" class="h4"><?= $this->data->dto->description ?></div>

		<input type="text" class="d-none" name="property_search" id="associated_property_search"
			placeholder="assign a property" value="<?= $this->data->dto->address ?>" />

		<div style="position: relative;">
			<div id="associated_property_searchResults"
				style="position: absolute; top: 0; left: 5px; background-color: white; z-index: 99; width: 280px;"></div>

		</div>
		<?php if ( !$this->data->dto->closed ) $this->load('view-comment-form-2'); ?>

	</div>

</div>

<div class="row">
	<div class="col"><?php
	if ( count( $this->data->dto->children )) {
		for ( $i = count($this->data->dto->children); $i > 0; $i-- )
			print forumUtility::printThread( $this->data->dto->children[$i-1], $reversed = true );

	}
	?></div>

</div>

<div class="row border-top">
<?php
	printf( '<div class="col-lg-1 col-md-2 text-center px-0 pt-2">%s<div class="small">%s</div></div>',
			html::icon( $this->data->dto->name),
			strings::asShortDate( $this->data->dto->updated, true ));

	printf( '<div class="col-lg-11 col-md-10 border-left" id="initial-forum-comment">%s</div>',
		sys::AutoTextAsHTML( $this->data->dto->comment ));
	?>

</div>

<div>
<?php
	if ( $this->comments ) {
		if ( $this->data->dto->closed ) {
			if ( currentUser::isDavid()) {	?>
		<div class="mt-1 text-right">
			<a class="button button-raised" href="<?= URL ?>forum/reopenTopic/<?= $this->data->dto->id ?>">re-open topic</a>

		</div>
<?php
			}

		}

	?>

</div>
<?php
	print '</form>';	// because we are in comments loop

	?>

<script>
$(document).ready( function() {
	$('img').addClass('img-fluid');

})
</script>
<?php
	}	// if ( $this->comments ) {	?>

<script>
$(document).ready( function() {
	$('#associated_property_search').autofill({
		source: _cms_.search.property,
		select: function(event, ui) {
			if ( /string/.test( typeof( ui.item.id )))
				window.location.href = _.url( '<?=  $this->route ?>/tagProperty/<?= $this->data->dto->id ?>/' + ui.item.id);

			else
				console.log( typeof( ui.item.id ));

		},
		appendTo : '#associated_property_searchResults'
	});

<?php	if ( currentUser::isAdmin()) {	?>

	var fs = $('<input type="text" class="form-control" value="<?= addslashes( $this->data->dto->description) ?>" />');
	fs
	.on( 'focus', function() {
		$(this).closest( 'form').on( 'submit', function( e) { return false; });
	})
	.on( 'blur', function() {
		$(this).closest( 'form').off( 'submit');
	})
	.on( 'change', function() {
		_.post({
			url : _.url( '<?=  $this->route ?>/updateSubject'),
			data : {
				id : <?= (int)$this->data->dto->id ?>,
				subject : $(this).val()
			}
		})
		.done( d => _.growl( d))

	})

	var fsD = $('<div class="input-group"></div>').append( fs);

	$('#forum-subject')
	.html('')
	.append(fsD);

<?php	}	// if ( currentUser::isAdmin()) 	?>

});
</script>
