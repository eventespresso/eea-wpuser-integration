<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * [ESPRESSO_MY_EVENTS] shortcode class
 *
 * @package			Event Espresso
 * @subpackage		/shortcodes/
 * @author			Darren Ethier
 * @since           4.6.29
 *
 * ------------------------------------------------------------------------
 */
class EES_Espresso_My_Events extends EES_Shortcode {


	public function run( WP $WP ) {
		//if fallback processor is running then this shortcode is running in an unsupported area
		if ( apply_filters( 'FHEE__fallback_shortcode_processor__EES_Espresso_Events', false ) ) {
			return;
		}

		EE_Registry::instance()->load_core( 'Request_Handler' );

		//if user is not logged in, let's redirect to wp-login.php
		if ( ! is_user_logged_in() ) {
			$redirect_url = EES_Espresso_My_Events::get_current_page( $WP );
			wp_safe_redirect( add_query_arg( array( 'redirect_to' => $redirect_url ), site_url( '/wp-login.php') ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( 'EES_Espresso_My_Events', 'enqueue_styles_and_scripts' ) );

			//was a resend registration confirmation in the request?
			if ( EE_Registry::instance()->REQ->is_set( 'resend' ) ) {
				EE_Espresso_My_Events::resend_reg_confirmation_email();
			}
		}
	}


	public static function set_hooks() {}
	public static function set_hooks_admin() {}


	/**
	 * Setup all the styles and scripts we'll use for this shortcode.
	 *
	 */
	public static function enqueue_styles_and_scripts() {
		wp_register_style( 'ees-my-events', EE_WPUSERS_URL . 'assets/css/ees-espresso-my-events.css', array( 'espresso_default' ), EVENT_ESPRESSO_VERSION );
		wp_enqueue_style( 'ees-my-events' );
	}


	/**
	 * Just a helper method for getting the url for the displayed page.
	 * @param  WP|null $WP
	 * @return bool|string|void
	 */
	public static function get_current_page( $WP  = null ) {
		$post_id = EE_Registry::instance()->REQ->get_post_id_from_request( $WP );
		if  ( $post_id ) {
			$current_page = get_permalink( $post_id );
		} else {
			if ( empty( $WP ) || ! $WP instanceof WP ) {
				global $wp;
				$WP = $wp;
			}
			if ( $WP->request ) {
				$current_page = site_url( $WP->request );
			} else {
				$current_page = esc_url( site_url( $_SERVER['REQUEST_URI'] ) );
			}
		}
		return $current_page;
	}


	/**
	 * process_shortcode - ESPRESSO_MY_EVENTS - Return a list of event and registration information specific to the
	 * logged in user.
	 *
	 * [ESPRESSO_MY_EVENTS] - defaults to use the "simple_list_table" layout for the returned events.
	 * [ESPRESSO_MY_EVENTS template="event_section"] - the value for the template param will load a template matching
	 *                                                  the given slug.
	 * [ESPRESSO_MY_EVENTS your_events_title="Your Classes"] - a way to modify the default "Your Events" title to
	 *                                                          something different
	 * [ESPRESSO_MY_EVENTS your_tickets_title="Your Tokens"] - a way to modify the default "Your Tickets" title to
	 *                                                          something different.
	 * [ESPRESSO_MY_EVENTS per_page=10] - use this to indicate the number of records per page shown ("records" is
	 *                                    dependent on template being generated.
	 *
	 * @param array $attributes  see description above for current available attributes.
	 * @return string
	 */
	public function process_shortcode( $attributes = array() ) {

		//if fallback processor is running, then let's exit because this is currently unsupported
		if ( apply_filters( 'FHEE__fallback_shortcode_processor__EES_Espresso_Events', false ) ) {
			if ( WP_DEBUG ) {
				return '<div class="important-notice ee-attention">'
				       . __( 'The [ESPRESSO_MY_EVENTS] is not supported outside of post content fields at this time.', 'event_espresso' )
				       . '</div>';
			} else {
				return '';
			}
		}

		//if made it here then all assets should be loaded and we are good to go!

		//load helpers
		EE_Registry::instance()->load_helper( 'Template' );

		//set default attributes and filter
		$default_shortcode_attributes = apply_filters( 'FHEE__EES_Espresso_My_Events__process_shortcode__default_shortcode_atts', array(
			'template' => 'simple_list_table',
			'your_events_title' => esc_html__( 'Your Events', 'event_espresso' ),
			'your_tickets_title' => esc_html__( 'Your Tickets', 'event_espresso' ),
			'per_page' => 10
		) );

		//merge with defaults
		$attributes = array_merge( $default_shortcode_attributes, (array) $attributes );
		$template_args = $this->_get_template_args( $attributes );

		return EEH_Template::locate_template( $template_args['template_path'], $template_args, true, true );
	}


	/**
	 * This returns a map of template slug to objects queried from the db (and the path to the template).
	 * Helps keep things more efficient instead of doing unnecessary queries.
	 * Users adding their own custom templates should use the filter in here to add them.
	 * @return array
	 */
	protected function _get_template_object_map() {

		/**
		 * This filter can be used to add custom templates for this shortcode.
		 * Once custom templates can be registered, then they can be utilized in the `[ESPRESSO_MY_EVENTS]`
		 * shortcode via the "template" parameter.
		 */
		return apply_filters(
			'FHEE__EES_Espresso_My_Events__process_shortcode_template_object_map',
			array(
				'simple_list_table' => array(
					'object_type' => 'Registration',
					'path' => EE_WPUSERS_TEMPLATE_PATH . 'loop-espresso_my_events-simple_list_table.template.php'
				),
				'event_section' => array(
					'object_type' => 'Event',
					'path' => EE_WPUSERS_TEMPLATE_PATH . 'loop-espresso_my_events-event_section.template.php'
				)
			)
		);
	}


	/**
	 * This verifies if there is a registered template for the given slug and that the values for the registered
	 * path check out.  Otherwise, load a default.
	 *
	 * @param string $template_slug  The template slug to check for.
	 * @return array a formatted array containing:
	 *               array(
	 *                  'template' => 'template_slug', //validated slug for template
	 *                  'object_type' => 'Registration', //validated object type for template
	 *                  'path' => 'full_path_to/template', //validate full path to template on the server
	 *               )
	 */
	protected function _get_template_info( $template_slug = '' ) {
		EE_Registry::instance()->load_helper( 'File' );

		$template_object_map = $this->_get_template_object_map();

		//first verify that the given slug is in the map.
		if ( is_string( $template_slug ) && ! empty( $template_slug ) && isset( $template_object_map[$template_slug] ) ) {
			$template_info = $template_object_map[$template_slug];
			$template_info['template'] = $template_slug;
			//next verify that there is an object type and that it matches one of the EE models used for querying.
			$accepted_object_types = array( 'Event', 'Registration' );
			if ( isset( $template_object_map[$template_slug]['object_type'] )
			     && in_array( $template_object_map[$template_slug]['object_type'], $accepted_object_types ) ) {
				//next verify that the path for the template is valid
				if ( isset( $template_object_map[$template_slug]['path'] ) && EEH_File::is_readable( $template_object_map[$template_slug]['path'] ) ) {

					//yay made it here you awesome template object you.
					return $template_info;
				}
			}
		} else {
			//oh noes, not setup properly, so let's just use a safe known default.
			return array(
				'template' => 'simple_list_table',
				'object_type' => 'Registration',
				'path' => EE_WPUSERS_TEMPLATE_PATH . 'loop-espresso_my_events-simple_list_table.template.php'
			);
		}
	}


	/**
	 * This returns an array of EE_Base_Class objects matching the params in the given template args.
	 *
	 * @param int   $att_id     This should be the ID of the EE_Attendee being queried against
	 * @param array $template_args  The generated template args for the template.
	 * @return array Returns an array:
	 *               array(
	 *                  'objects' => array(), //an array of EE_Base_Class objects
	 *                  'object_count' => 0, //count of all records matching query params without limits.
	 *               );
	 */
	protected function _get_template_objects( $att_id, $template_args = array() ) {
		$object_info = array(
			'objects' => array(),
			'object_count' => 0
		);

		//required values available?
		if ( empty( $template_args )
		     || empty( $template_args['object_type'] )
			 || empty( $template_args['per_page'] )
			 || empty( $template_args['page'] ) ) {
			return $object_info; //need info yo.
		}

		$offset = ( $template_args['page'] - 1 ) * $template_args['per_page'];
		$att_id = (int) $att_id;

		if ( $template_args['object_type'] == 'Event' ) {
			$query_args = array(
				0 => array( 'Registration.ATT_ID' => $att_id ),
				'limit' => array( $offset, $template_args['per_page'] )
			);
		} elseif ( $template_args['object_type'] == 'Registration' ) {
			$query_args = array(
				0 => array( 'ATT_ID' => $att_id ),
				'limit' => array( $offset, $template_args['per_page'] )
			);
		} else {
			//get out no valid object_types here.
			return $object_info;
		}

		$object_info['objects'] = EE_Registry::instance()->load_model( $template_args['object_type'] )->get_all( $query_args );
		$object_info['object_count'] = EE_Registry::instance()->load_model( $template_args['object_type'] )->count( array( $query_args[0] ), null, true );
		return $object_info;
	}



	/**
	 * This sets up the template arguments for the shortcode template being parsed.
	 *
	 * @param array $attributes incoming shortcode attributes.
	 * @return array
	 */
	protected function _get_template_args( $attributes ) {
		//any parameters coming from the request?
		$per_page = (int) EE_Registry::instance()->REQ->get( 'per_page', $attributes['per_page'] );
		$page = (int) EE_Registry::instance()->REQ->get( 'ee_mye_page', false );

		//if $page is empty then it's likely this is being loaded outside of ajax and wp has usurped
		//the page value for its query.  So let's see if its in the query.
		if ( ! $page ) {
			global $wp_query;
			if ( $wp_query instanceof $wp_query && isset( $wp_query->query ) && isset( $wp_query->query['paged'] ) ) {
				$page = $wp_query->query['paged'];
			} else {
				$page = 1;
			}
		}


		$template = $attributes['template'] ? $attributes['template'] : 'simple_list_table';
		$template_info = $this->_get_template_info( $template );

		//define template_args
		$template_args = array(
			'object_type' => $template_info['object_type'],
			'objects' => array(),
			'your_events_title' => $attributes['your_events_title'],
			'your_tickets_title' => $attributes['your_tickets_title'],
			'template_slug' => $template_info['template'],
			'per_page' => $per_page,
			'template_path' => $template_info['path'],
			'page' => $page,
			'object_count' => 0
		);

		//grab any contact that is attached to this user
		$att_id = get_user_meta( get_current_user_id(), 'EE_Attendee_ID', true );

		//if there is an attached attendee we can use that to retrieve all the related events and
		//registrations.  Otherwise those will be left empty.
		if ( $att_id ) {
			$object_info = $this->_get_template_objects( $att_id, $template_args );
			$template_args['objects'] = $object_info['objects'];
			$template_args['object_count'] = $object_info['object_count'];
		}
		return $template_args;
	}


	/**
	 * 	Resend Registration Confirmation Email.
	 */
	public static function resend_reg_confirmation_email() {
		EE_Registry::instance()->load_core( 'Request_Handler' );
		$reg_url_link = EE_Registry::instance()->REQ->get( 'token' );

		// was a REG_ID passed ?
		if ( $reg_url_link ) {
			$registration = EE_Registry::instance()->load_model( 'Registration' )->get_one( array( array( 'REG_url_link' => $reg_url_link )));
			if ( $registration instanceof EE_Registration ) {
				// resend email
				EED_Messages::process_resend( array( '_REG_ID' => $registration->ID() ));
			} else {
				EE_Error::add_error(
					__( 'The Registration Confirmation email could not be sent because a valid Registration could not be retrieved from the database.', 'event_espresso' ),
					__FILE__, __FUNCTION__, __LINE__
				);
			}
		} else {
			EE_Error::add_error(
				__( 'The Registration Confirmation email could not be sent because a registration token is missing or invalid.', 'event_espresso' ),
				__FILE__, __FUNCTION__, __LINE__
			);
		}
		// request sent via AJAX ?
		if ( EE_FRONT_AJAX ) {
			echo json_encode( EE_Error::get_notices( FALSE ));
			die();
			// or was JS disabled ?
		} else {
			// save errors so that they get picked up on the next request
			EE_Error::get_notices( TRUE, TRUE );
			wp_safe_redirect(
					EES_Espresso_My_Events::get_current_page()
				);
		}
	}

} //end EES_Espresso_My_Events