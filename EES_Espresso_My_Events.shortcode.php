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


		//if user is not logged in, let's redirect to wp-login.php
		if ( ! is_user_logged_in() ) {
			$redirect_url = esc_url( site_url( $_SERVER['REQUEST_URI'] ) );
			wp_safe_redirect( add_query_arg( array( 'redirect_to' => $redirect_url ), site_url( '/wp-login.php?') ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( 'EES_Espresso_My_Events', 'enqueue_styles_and_scripts' ) );
		}
	}


	public static function set_hooks() {}
	public static function set_hooks_admin() {}


	/**
	 * Setup all the styles and scripts we'll use for this shortcode.
	 *
	 */
	public static function enqueue_styles_and_scripts() {

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
			'your_events_title' => __( 'Your Events', 'event_espresso' ),
			'your_tickets_title' => __( 'Your Tickets', 'event_espresso' ),
			'per_page' => 10
		) );

		//merge with defaults
		$attributes = array_merge( $default_shortcode_attributes, $attributes );
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
	 * @return EE_Base_Class[]
	 */
	protected function _get_template_objects( $att_id, $template_args = array() ) {

		//required values available?
		if ( empty( $template_args )
		     || empty( $template_args['object_type'] )
			 || empty( $template_args['per_page'] )
			 || empty( $template_args['page'] ) ) {
			return array(); //need info yo.
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
			return array();
		}

		return EE_Registry::instance()->load_model( $template_args['object_type'] )->get_all( $query_args );
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
		$page = (int) EE_Registry::instance()->REQ->get( 'page', 1 );

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
			'page' => $page
		);

		//grab any contact that is attached to this user
		$att_id = get_user_meta( get_current_user_id(), 'EE_Attendee_ID', true );

		//if there is an attached attendee we can use that to retrieve all the related events and
		//registrations.  Otherwise those will be left empty.
		if ( $att_id ) {
			$template_args['objects'] = $this->_get_template_objects( $att_id, $template_args );
		}
		return $template_args;
	}
} //end EES_Espresso_My_Events