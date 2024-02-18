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

$dbc = sys::dbCheck('forum_board');

$dbc->defineField('name', 'varchar');
$dbc->defineField('status', 'int');
$dbc->defineField('assigned_user_id', 'bigint');
$dbc->defineField('priority', 'varchar', 10);
$dbc->defineField('description', 'text');
$dbc->defineField('idea', 'tinyint');
$dbc->defineField('link', 'varchar');
$dbc->defineField('user_id', 'bigint');
$dbc->defineField('archived', 'tinyint');

$dbc->defineField('created', 'datetime');
$dbc->defineField('updated', 'datetime');

$dbc->check();
