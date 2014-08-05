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
		add_action( 'AHEE__EE_Single_Page_Checkout__process_attendee_information__end', array( 'EE_WPUsers', 'actionAddAttendeeAsWPUser' ), 10, 2 );
		
	}
	
	public static function filterAnswerForWPUser($value, $registration, $question_id) {
		if ( empty($value) ) {
			$current_user = wp_get_current_user();
			
			if ( $current_user instanceof WP_User ) {
				switch ($question_id) {

					case 1:
						$value = $current_user->get('first_name');
						break;
					
					case 2:
						$value = $current_user->get('last_name');
						break;
					
					case 3:
						$value = $current_user->get('user_email');
						break;
					
					default:
				}
			}
		}
		return $value;
	}
	
	public static function actionAddAttendeeAsWPUser ($ee_Single_Page_Checkout, $valid_data) {
		foreach ($valid_data as $registrant) {
			$attendee = EEM_Attendee::get_attendee(array('ATT_fname'=>$registrant['fname']));
		}	
		
		ob_start();
		var_dump($valid_data);
		var_dump($attendee);
		$temp=  ob_get_clean();
		file_put_contents('/tmp/log.html',$temp, FILE_APPEND);
		
		

	}
}
