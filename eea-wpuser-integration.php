<?php

if ( ! defined( 'ABSPATH' ) )
	exit( 'No direct script access allowed' );
/*
  Plugin Name: 	Event Espresso - WP Users (EE4.6+)
  Plugin URI: 	http://www.eventespresso.com
  Description: 	This adds the WP users integration.
  Version: 		2.0.14.p
  Author: 		Event Espresso
  Author URI: 	http://www.eventespresso.com
  License: 		GPLv2
  TextDomain: 	event_espresso
  Copyright 	(c) 2008-2014 Event Espresso  All Rights Reserved.

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * EE WPUsers add-on for Event Espresso
 * @since 		1.0.0
 * @package 	EE WPUsers
 *
 */
define( 'EE_WPUSERS_VERSION', '2.0.14.p' );
define( 'EE_WPUSERS_MIN_CORE_VERSION_REQUIRED', '4.8.21.rc.005' );
define( 'EE_WPUSERS_PLUGIN_FILE', __FILE__ );


function load_ee_core_wpusers() {
	if ( class_exists( 'EE_Addon' ) ) {
		// new_addon version
		require_once ( plugin_dir_path( __FILE__ ) . 'EE_WPUsers.class.php' );
		EE_WPUsers::register_addon();
	}

}

add_action( 'AHEE__EE_System__load_espresso_addons', 'load_ee_core_wpusers' );

// End of file ee-addon-wpusers.php
// Location: wp-content/plugins/ee4-wpusers/ee-addon-wpusers.php
