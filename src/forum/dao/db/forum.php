<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\forum\dao;

use sys;

$dbc = sys::dbCheck( 'forum');
$dbc->defineField( 'description', 'varchar', 100 );
$dbc->defineField( 'tag', 'varchar', 20 );
$dbc->defineField( 'parent', 'bigint');
$dbc->defineField( 'link', 'varchar');
$dbc->defineField( 'comment', 'mediumblob' );
$dbc->defineField( 'thread', 'varchar', 256 );
$dbc->defineField( 'priority', 'varchar', 10 );
$dbc->defineField( 'updated', 'datetime' );
$dbc->defineField( 'closed', 'tinyint' );
$dbc->defineField( 'closed_date', 'datetime' );
$dbc->defineField( 'complete', 'tinyint' );
$dbc->defineField( 'complete_date', 'datetime' );
$dbc->defineField( 'user_id', 'bigint');
$dbc->defineField( 'by_email', 'tinyint' );
$dbc->defineField( 'flag', 'tinyint' );
$dbc->defineField( 'resolved', 'tinyint' );
$dbc->defineField( 'property_id', 'bigint');
$dbc->defineField( 'forum_idea_id', 'bigint');
$dbc->defineField( 'notify', 'text' );

$dbc->defineIndex('forum_idx_closed_complete_updated', '`closed` ASC, `complete` ASC, `updated` ASC' );
$dbc->defineIndex('forum_idx_parent', '`parent` ASC' );
$dbc->check();
