<?php if ( ! defined('ABSPATH')) exit('No direct script access allowed');

/**
 * Class definition for the EE_WPUsers object
 *
 * @since 		1.0.0
 * @package 	EE WPUsers
 */
class EE_WPUsers extends EE_Addon {
	public static function register_addon() {
		// register addon via Plugin API
		EE_Register_Addon::register(
			'EE_WPUsers',
			array(
				'version' 					=> EE_WPUSERS_VERSION,
				'min_core_version' => '4.3.0',
				'main_file_path' 				=> EE_WPUSERS_PLUGIN_DIR . 'src',
				'autoloader_paths' => array(
					'EE_WPUsers' 						=> EE_WPUSERS_PLUGIN_DIR . '/src/EE_WPUsers.class.php'
				),
				// if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
				'pue_options'			=> array(
					'pue_plugin_slug' => 'ee-addon-wpusers',
					'plugin_basename' => EE_WPUSERS_PLUGIN_FILE,
					'checkPeriod' => '24',
					'use_wp_update' => FALSE
					)
			)
		);
	}
}
