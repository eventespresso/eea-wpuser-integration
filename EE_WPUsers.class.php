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
				'main_file_path' 				=> EE_WPUSERS_PLUGIN_FILE,
				
				// if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
				'pue_options'			=> array(
					'pue_plugin_slug' => 'ee-addon-wpusers',
					'plugin_basename' => EE_WPUSERS_PLUGIN_FILE,
					'checkPeriod' => '24',
					'use_wp_update' => FALSE
					)
			)
		);
		
		add_filter( 'FHEE__EEM_Answer__get_attendee_question_answer_value__answer_value', array( 'EE_WPUsers', 'filterAnswerForWPUser' ), 10, 3 );
		
	}
	
	public static function filterAnswerForWPUser($value, $registration, $question_id) {
		if ( empty($value) ) {
			$user_query = new WP_User_Query( array( 'meta_key' => 'EE_Attendee_ID', 'meta_value' => $registration->attendee_ID() ) );
			if ( count($user_query) == 1 ) {
				switch ($question_id) {
					
					case 1:
						$value = $user_query[0]->get('first_name');
						break;
					
					case 2:
						$value = $user_query[0]->get('last_name');
						break;
					
					case 3:
						$value = $user_query[0]->get('user_email');
						break;
					
					default:
				}
			}
		}
		return $value;
	}
}
