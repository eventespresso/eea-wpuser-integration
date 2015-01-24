<?php

if (!defined('ABSPATH'))
	exit('No direct script access allowed');

//define constants
define( 'EE_WPUSERS_PATH', plugin_dir_path( __FILE__ ) );
define( 'EE_WPUSERS_URL', plugin_dir_url( __FILE__ ) );
define( 'EE_WPUSERS_TEMPLATE_PATH', EE_WPUSERS_PATH . 'templates/' );

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
				'config_class' => 'EE_WPUsers_Config',
				'config_name' => 'user_integration',
				'module_paths' => array(
					EE_WPUSERS_PATH . 'EED_WP_Users_SPCO.module.php',
					EE_WPUSERS_PATH . 'EED_WP_Users_Admin.module.php'
				 ),
				'autoloader_paths' => array(
					'EE_WPUsers_Config' => EE_WPUSERS_PATH . 'EE_WPUsers_Config.php'
					),
				// if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
				'pue_options' => array(
					'pue_plugin_slug' => 'eea-wpuser-integration',
					'checkPeriod' => '24',
					'use_wp_update' => FALSE
				)
			)
		);
	}


	/**
	 * other helper methods
	 */


	/**
	 * Used to get a user id for a given EE_Attendee id.
	 * If none found then null is returned.
	 *
	 * @param int     $att_id The attendee id to find a user match with.
	 *
	 * @return int|null     $user_id if found otherwise null.
	 */
	public static function get_attendee_user( $att_id ) {
		global $wpdb;
		$query = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'EE_Attendee_ID' AND meta_value = '%d'";
		$user_id = $wpdb->get_var( $wpdb->prepare( $query, (int) $att_id ) );
		return $user_id ? (int) $user_id : NULL;
	}

}

// end of class EE_WPUsers
