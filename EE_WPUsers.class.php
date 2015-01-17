<?php

if (!defined('ABSPATH'))
	exit('No direct script access allowed');

//define constants
define( 'EE_WPUSERS_PATH', plugin_dir_path( __FILE__ ) );
define( 'EE_WPUSERS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Class definition for the EE_WPUsers object
 *
 * @since 		1.0.0
 * @package 	EE WPUsers
 */
class EE_WPUsers extends EE_Addon {

	/**
	 * Set up
	 */
	public static function register_addon() {
		// register addon via Plugin API
		EE_Register_Addon::register(
				'EE_WPUsers', array(
				'version' => EE_WPUSERS_VERSION,
				'min_core_version' => '4.6.0.alpha',
				'main_file_path' => EE_WPUSERS_PLUGIN_FILE,
				'module_paths' => array( EE_WPUSERS_PATH . 'EED_WP_Users_SPCO.module.php' ),
				// if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
				'pue_options' => array(
					'pue_plugin_slug' => 'eea-wpuser-integration',
					'checkPeriod' => '24',
					'use_wp_update' => FALSE
				)
			)
		);
	}

}

// end of class EE_WPUsers
