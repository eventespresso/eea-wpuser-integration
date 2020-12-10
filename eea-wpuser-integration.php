<?php

if (! defined('ABSPATH')) {
    exit('No direct script access allowed');
}

/*
  Plugin Name:  Event Espresso - WP Users (EE4.6+)
  Plugin URI:  http://www.eventespresso.com
  Description: This adds the WP users integration.
  Version: 2.1.0.rc.002
  Author: Event Espresso
  Author URI: http://www.eventespresso.com
  License: GPLv2
  TextDomain: event_espresso
  Copyright (c) 2008-2014 Event Espresso  All Rights Reserved.

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
 *
 * @since        1.0.0
 * @package      EE WPUsers
 *
 */
define('EE_WPUSERS_VERSION', '2.1.0.rc.002');
define('EE_WPUSERS_MIN_CORE_VERSION_REQUIRED', '4.11.0.rc.001');
define('EE_WPUSERS_PLUGIN_FILE', __FILE__);


function load_ee_core_wpusers()
{
    static $loaded = false;
    if (! $loaded
        && class_exists('EE_Addon')
        && class_exists('EventEspresso\core\domain\DomainBase')
    ) {
        $loaded = true;
        define('EE_WPUSERS_PATH', plugin_dir_path(__FILE__));
        define('EE_WPUSERS_URL', plugin_dir_url(__FILE__));
        define('EE_WPUSERS_TEMPLATE_PATH', EE_WPUSERS_PATH . 'templates/');
        define('EE_WPUSERS_BASENAME', plugin_basename(EE_WPUSERS_PLUGIN_FILE));
        require_once EE_WPUSERS_PATH . 'EE_WPUsers.class.php';
        EE_WPUsers::register_addon();
    }
}

add_action('AHEE__EE_System__load_espresso_addons', 'load_ee_core_wpusers');


/**
 * @returns EventEspresso\core\domain\DomainInterface
 */
function getWpUserDomain()
{
    static $domain;
    if (! $domain instanceof EventEspresso\WpUser\domain\Domain) {
        $domain = EventEspresso\core\domain\DomainFactory::getShared(
            new EventEspresso\core\domain\values\FullyQualifiedName(
                'EventEspresso\WpUser\domain\Domain'
            ),
            [EE_WPUSERS_PLUGIN_FILE, EE_WPUSERS_VERSION]
        );
    }
    return $domain;
}
