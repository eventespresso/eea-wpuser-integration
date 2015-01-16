<?php

if (!defined('ABSPATH'))
	exit('No direct script access allowed');

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
			// if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
			'pue_options' => array(
				'pue_plugin_slug' => 'eea-wpuser-integration',
				'checkPeriod' => '24',
				'use_wp_update' => FALSE
			)
				)
		);

		add_filter('FHEE__EEM_Answer__get_attendee_question_answer_value__answer_value', array('EE_WPUsers', 'filterAnswerForWPUser'), 10, 3);
		add_action('AHEE__EE_Single_Page_Checkout__process_attendee_information__end', array('EE_WPUsers', 'actionAddAttendeeAsWPUser'), 10, 2);
		add_action('AHEE__event_tickets_datetime_ticket_row_template_before_close', array('EE_WPUsers', 'insert_ticket_meta_interface'), 10, 1);
	}

	/**
	 * Added to filter that processes the return to the registration form of whether and answer to the question exists for that
	 * @param type $value
	 * @param type $registration
	 * @param type $question_id
	 * @return type
	 */
	public static function filterAnswerForWPUser($value, $registration, $question_id) {
		if (empty($value)) {
			$current_user = wp_get_current_user();

			if ($current_user instanceof WP_User) {
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

	public static function actionAddAttendeeAsWPUser($ee_Single_Page_Checkout, $valid_data) {
		foreach ($valid_data as $registrant) {
			// Try to find a pre-existing attendee. If SPCO gave us access to the registration object, wouldn't have to do this step.
			$attendee = EEM_Attendee::instance()->get_attendee(array(
				'ATT_fname' => $registrant['fname'],
				'ATT_lname' => $registrant['lname'],
				'ATT_email' => $registrant['email']
			));


			if ($attendee instanceof EE_Attendee) { // should always be a match, since SPCO just finished putting the attendee in the DB
				// Try to find an existing WP user matching the Attendee. Just match by email, since that should be unique in WP land.
				$user = get_user_by('email', $registrant['email']);
				if ($user != FALSE) {
// if there is a pre-existing attendee-wpuser connection, should always be 1-1, but update just to make sure and cause it's the same number of lines of code to test as to push the value onto a wp user that didn't have a attendee associated with it.
					update_user_meta($user->ID, 'EE_Attendee_ID', $attendee->ID());
				} else { // no pre-existing wp-user, create one
					// Generate the password and create the user
					$password = wp_generate_password(12, false);
					$user_id = wp_create_user(apply_filters('FHEE__EE_WPUsers__actionAddAttendeeAsWPUser__username', $registrant['email'], $registrant), $password, $registrant['email']);

					if ($user_id instanceof WP_Error) {
						// @todo something went boom! put in some error handling
					} else { // user was added, fill in the details
						// Set the users details
						//Additional fields can be found here: http://codex.wordpress.org/Function_Reference/wp_update_user
						wp_update_user(
								array(
									'ID' => $user_id,
									'nickname' => $registrant['fname'] . ' ' . $registrant['lname'],
									'display_name' => $registrant['fname'] . ' ' . $registrant['lname'],
									'first_name' => $registrant['fname'],
									'last_name' => $registrant['lname'],
									'description' => __('Registered via event registration form.', 'event_espresso'),
								)
						);

						// Set the role
						$user = new WP_User($user_id);
						$user->set_role('subscriber');

						// Email the user
						wp_mail($registrant['email'], 'Welcome to ' . EE_Config::instance()->get_config_option('name'), 'Your Username: ' . apply_filters('FHEE__WPUsers_create_wp_username', $registrant['email'], $registrant) . ' Your Password: ' . $password);
						update_user_meta($user_id, 'EE_Attendee_ID', $attendee->ID());
					} // end of filling in the details
				} // end of wp-user creation
			} else {
				// SOL?
			} // end of test to make sure is attendee
		} // end of loop over attendees
	}

	public static function insert_ticket_meta_interface($TKT_ID) {
		$Ticket_model = EEM_Ticket::instance();
		$ticket = $Ticket_model->get_one_by_ID($TKT_ID);
		if ($ticket instanceof EE_Ticket) {
			$template_args = array(
				'TKT_WPU_meta' => $ticket->get_extra_meta('TKT_WPU_meta', TRUE),
				'ticket_meta_help_link' => ''
			);
			$template = EE_WPUSERS_TEMPLATE_PATH . 'event_tickets_datetime_ticket_row_metadata.template.php';
			EEH_Template::locate_template($template, $template_args, TRUE, FALSE);
		}
	}

}

// end of class EE_WPUsers
