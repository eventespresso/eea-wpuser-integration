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




	/**
	 * used to determine if forced login is turned on for the event or not.
	 *
	 * @param int|EE_Event $event Either event_id or EE_Event object.
	 *
	 * @return bool   true YES forced login turned on false NO forced login turned off.
	 */
	public static function is_event_force_login( $event ) {
		$event = $event instanceof EE_Event ? $event : EE_Registry::instance()->load_model( 'Event' )->get_one_by_ID( (int) $event );
		$settings = $event instanceof EE_Event ? $event->get_post_meta( 'ee_wpuser_integration_settings', true ) : array();
		if ( !empty( $settings ) ) {
			return (bool) ( isset( $settings['forced_login'] ) ? $settings['forced_login'] : false );
		}
		return false;
	}



	/**
	 * used to update the force login setting for an event.
	 *
	 * @param int|EE_Event $event Either the EE_Event object or int.
	 * @param bool $force_login value.  If turning off you can just not send.
	 *
	 * @throws EE_Error (via downstream activity)
	 * @return mixed Returns meta_id if the meta doesn't exist, otherwise returns true on success and false on failure. NOTE: If the meta_value passed to this function is the same as the value that is already in the database, this function returns false.
	 */
	public static function update_event_force_login( $event, $force_login = false ) {
		$event = $event instanceof EE_Event ? $event : EE_Registry::instance()->load_model( 'Event' )->get_one_by_ID( (int) $event );

		if ( ! $event instanceof EE_Event ) {
			return false;
		}
		$settings = $event->get_post_meta( 'ee_wpuser_integration_settings', true );
		$settings = empty( $settings ) ? array() : $settings;
		$settings['forced_login'] = $force_login;
		return $event->update_post_meta( 'ee_wpuser_integration_settings', $settings );
	}

}

// end of class EE_WPUsers
