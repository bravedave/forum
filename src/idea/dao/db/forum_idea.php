<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dao;

$dbc = \sys::dbCheck('forum_idea');
$dbc->defineField('idea', 'varchar', 100);
$dbc->defineField('data', 'text');
$dbc->defineField('tag', 'varchar');
$dbc->defineField('created', 'datetime');
$dbc->defineField('updated', 'datetime');

// $dbc->defineIndex('forum_idea_idx_', '`closed` ASC, `complete` ASC, `updated` ASC');
$dbc->check();
