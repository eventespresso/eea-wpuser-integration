<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
 /**
 *
 * Class EE_SPCO_Reg_Step_WP_User_Login
 *
 * This is the reg step that adds a wp_user login step to the SPCO checkout process.
 *
 * @package 		WP Users Integration
 * @subpackage 	spco
 * @author 		Darren Ethier
 * @since 		1.0.0
 *
 */
class EE_SPCO_Reg_Step_WP_User_Login extends EE_SPCO_Reg_Step {


	/**
	 * constructor
	 *
	 * @param EE_Checkout $checkout
	 */
	public function __construct( EE_Checkout $checkout ) {
		$this->_slug = 'wpuser_login';
		$this->_name = __( 'Login', 'event_espresso' );
		$this->_template = '';
		$this->checkout = $checkout;
		$this->_reset_success_message();
		add_action( 'AHEE__Single_Page_Checkout___initialize_reg_step__wpuser_login', array( $this, 'this_step_initialized' ) );
	}


	/**
	 * Callback on 'AHEE__Single_Page_Checkout___initialize_reg_step__wpuser_login'.
	 * This is implemented to delay setting the instructions text (_instructions property) because we need a redirect url
	 * that is ONLY available after the reg step has been initialized.
	 *
	 * @param EE_SPCO_Reg_Step_WP_User_Login $spco_step
	 */
	public function this_step_initialized( EE_SPCO_Reg_Step_WP_User_Login $spco_step ) {
		$registration_url = ! EE_Registry::instance()->CFG->addons->user_integration->registration_page
			? esc_url(
				add_query_arg(
					array(
						'ee_do_auto_login' => 1,
						'ee_load_on_login' => 1,
						'redirect_to' => $this->checkout->next_step->reg_step_url(),
					),  wp_registration_url()
				)
			)
			: EE_Registry::instance()->CFG->addons->user_integration->registration_page;
		$instructions = get_option( 'users_can_register' )
			? sprintf( __( 'The event you have selected requires logging in before you can register. You can %sregister for an account here%s if you don\'t have a login.', 'event_espresso' ), '<a href="' . $registration_url . '">', '</a>' )
			: __( 'The event you have selected requires logging in before you can register.', 'event_espresso' );
		$this->set_instructions( $instructions );
	}

	public function translate_js_strings() {}



	public function enqueue_styles_and_scripts() {
		EED_WP_Users_SPCO::enqueue_scripts_styles( EED_Single_Page_Checkout::instance() );
	}


	/**
	 * Initialize the reg step
	 *
	 * @return boolean
	 */
	public function initialize_reg_step() {
		//check if the any selected event in the checkout has forced login on.  If it doesn't then we remove this step.
		$require_login = false;
		foreach ( $this->checkout->transaction->registrations() as $registration ) {
			$require_login = EE_WPUsers::is_event_force_login( $registration->event() );
			if ( $require_login ) {
				break;
			}
		}
		if ( ! $require_login || ( $require_login && is_user_logged_in() ) ) {
			$this->checkout->skip_reg_step( $this->_slug );
			return false;
		}
		return true;
	}




	public function generate_reg_form() {
		EE_Registry::instance()->load_helper( 'HTML' );
		return new EE_Form_Section_Proper(
			array(
				'name' => $this->reg_form_name(),
				'html_id' => $this->reg_form_name(),
				'subsections' => array(
					'default_hidden_inputs' => $this->reg_step_hidden_inputs(),
					'eea_wp_user_instructions' => new EE_Form_Section_HTML( EEH_HTML::p( $this->_instructions ) ),
					'eea_wp_user_login_name' => new EE_Text_Input(
						array(
							'html_name' => 'ee_user[login]',
							'html_id' => 'ee_user-login',
							'html_class' => 'ee-reg-qstn',
							'required' => true,
							'html_label_id' => 'ee_user-login-label',
							'html_label_class' => 'ee-reg-qstn',
							//deliberately no text_domain because this is a wp core translated string
							'html_label_text' => __( 'Username' ),
							)
					),
					'eea_wp_user_password' => new EE_Password_Input(
						array(
							'html_name' => 'ee_user[password]',
							'html_id' => 'ee_user-password',
							'html_class' => 'ee-reg-qstn',
							'required' => true,
							'html_label_id' => 'ee_user-password-label',
							'html_label_class' => 'ee-reg-qstn',
							//deliberately no text_domain because this is a wp core translated string
							'html_label_text' => __( 'Password' ),
							)
					),
					'eea_wp_user_login_error_notice' => new EE_Form_Section_HTML( EEH_HTML::div( '', 'login_error_notice' ) . EEH_HTML::divx() )
					),
				'layout_strategy' => new EE_Div_Per_Section_Layout()
				)
		);
	}



	public function process_reg_step() {
		$valid_data = $this->checkout->current_step->valid_data();
		if ( empty( $valid_data ) ) {
			EE_Error::add_error( __( 'No valid question responses were received.', 'event_espresso' ), __FILE__, __FUNCTION__, __LINE__ );
			return false;
		}



		$login_data = array(
			'user_login' => $valid_data['eea_wp_user_login_name'],
			'login_pass' => $valid_data['eea_wp_user_password']
			);

		$processed = EED_WP_Users_SPCO::process_login_form( $login_data, false );

		//if we've got errors then we'll set the return data to the json response and return false.  Otherwise we'll just return true.
		if ( is_array( $processed['wp_user_response'] ) && isset( $processed['wp_user_response']['show_login_form'] ) && $processed['wp_user_response']['show_login_form'] ) {
			//let's just rejig the wp_user_response so error gets handled correctly with our spco inline login form.
			$return_data = array(
				'wp_user_response' => array(
					'require_login' => false,
					'show_login_form' => false,
					'show_errors_in_context' => true,
					'validation_error' => array()
					)
				);
			foreach ( (array) $processed['wp_user_response']['validation_error']['field'] as $field ) {
				if ( $field == 'user_login' ) {
					$return_data['wp_user_response']['validation_error']['field'][] = 'ee_user-login';
				} elseif ( $field == 'user_pass' ) {
					$return_data['wp_user_response']['validation_error']['field'][] = 'ee_user-password';
				} else {
					$return_data['wp_user_response']['validation_error']['field'][] = 'login_error_notice';
				}
			}
			$this->checkout->json_response->set_return_data( $return_data );
			return false;
		}  else {
			$this->set_completed();
			return true;
		}

	}




	public function update_reg_step() {}



} // end class EE_SPCO_Reg_Step_WP_User_Login
