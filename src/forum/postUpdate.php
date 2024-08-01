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

use application;
use bravedave\dvc\service;
use green;

class postUpdate extends service {
  protected function _upgrade() {
    config::forum_checkdatabase();

    green\beds_list\config::green_beds_list_checkdatabase();
    green\baths\config::green_baths_checkdatabase();
    green\property_type\config::green_property_type_checkdatabase();
    green\postcodes\config::green_postcodes_checkdatabase();
    green\users\config::green_users_checkdatabase();

    green\people\config::green_people_checkdatabase();
    green\properties\config::green_properties_checkdatabase();

    config::route_register('forum', 'dvc\\forum\\controller');
    config::route_register('idea', 'dvc\\idea\\controller');

    config::route_register('people', 'green\\people\\controller');
    config::route_register('properties', 'green\\properties\\controller');
    config::route_register('beds', 'green\\beds_list\\controller');
    config::route_register('baths', 'green\\baths\\controller');
    config::route_register('property_type', 'green\\property_type\\controller');
    config::route_register('postcodes', 'green\\postcodes\\controller');
  }

  static function upgrade() {
    $app = new self(application::startDir());
    $app->_upgrade();
  }
}
