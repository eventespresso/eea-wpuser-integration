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

		//register our check for whether we continue loading or redirect.  Has to run on a later hook where we have access
		//to is_singular() or is_archive().
		add_action( 'parse_query', array( $this, 'setup_for_load' ), 10 );
	}



	/**
	 * setup_for_load
	 *
	 * @return void
	 */
	public function setup_for_load() {
		EE_Registry::instance()->load_core( 'Request_Handler' );

		//was a resend registration confirmation in the request?
		if ( EE_Registry::instance()->REQ->is_set( 'resend' ) ) {
			EES_Espresso_My_Events::resend_reg_confirmation_email();
		}

		//conditionally load assets
		if ( ! has_action( 'wp_enqueue_scripts', array( 'EES_Espresso_My_Events', 'enqueue_styles_and_scripts' ) ) ) {
			add_action( 'wp_enqueue_scripts', array( 'EES_Espresso_My_Events', 'enqueue_styles_and_scripts' ) );
		}
	}




	/**
	 * The following two methods are called on every page load.  So only use them for hooks to be set globally.
	 * Even though these are not abstract methods, they are required because they get called by the
	 * 'AHEE__EE_System__set_hooks_for_shortcodes_modules_and_addons' action that is set in EE_Config.
	 */
	public static function set_hooks() {}
	public static function set_hooks_admin() {
		add_action( 'wp_ajax_ee_my_events_load_paged_template', array( 'EES_Espresso_My_Events', 'load_paged_template_via_ajax' ) );
		add_action( 'wp_ajax_nopriv_ee_my_events_load_paged_template', array( 'EES_Espresso_My_Events', 'load_paged_template_via_ajax' ) );
	}



	/**
	 * Setup all the styles and scripts we'll use for this shortcode.
	 *
	 */
	public static function enqueue_styles_and_scripts() {
		static $scripts_loaded = false;
		if ( $scripts_loaded ) {
			return;
		}
		wp_register_style( 'ees-my-events', EE_WPUSERS_URL . 'assets/css/ees-espresso-my-events.css', array( 'espresso_default' ), EE_WPUSERS_VERSION );
		wp_register_script( 'ees-my-events-js', EE_WPUSERS_URL . 'assets/js/ees-espresso-my-events.js', array( 'espresso_core' ), EE_WPUSERS_VERSION, true );
		wp_enqueue_style( 'ees-my-events' );
		wp_enqueue_script( 'ees-my-events-js' );
	}


	/**
	 * Just a helper method for getting the url for the displayed page.
	 *
	 * @param  WP  $WP
	 * @param bool $purge_ee_request_params  Set to true if all EE Request params should be purged from any generated URL.
	 *
	 * @return bool|string|void
	 */
	public static function get_current_page( $WP = null, $purge_ee_request_params = false ) {
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

		if ( $purge_ee_request_params ) {
			$current_page = remove_query_arg( array( 'resend', 'token' ), $current_page );
		}
		return $current_page;
	}


	/**
	 * process_shortcode - ESPRESSO_MY_EVENTS - Return a list of event and registration information specific to the
	 * logged in user.
	 *
	 * [ESPRESSO_MY_EVENTS] - defaults to use the "event_section" layout for the returned events.
	 * [ESPRESSO_MY_EVENTS template="simple_list_table"] - the value for the template param will load a template matching
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

		//last check for whether user is logged in.  If not, then we display a link to login.
		if ( ! is_user_logged_in() ) {
			$redirect_url = EES_Espresso_My_Events::get_current_page();
			return apply_filters(
				'FHEE__Espresso_My_Events__process_shortcode__redirect_to_login_instructions',
				'<a class="ee-wpui-login-link" href="' . add_query_arg( array( 'redirect_to' => $redirect_url ), site_url( '/wp-login.php') ) . '">'
				. esc_html__( 'Login to see your registrations.', 'event_espresso' ) . '</a>'
			);
		}

		//if made it here then all assets should be loaded and we are good to go!
		return $this->load_template($attributes);
	}


	/**
	 * process ajax callback for the ee_my_events_load_paged_template action.
	 */
	public static function load_paged_template_via_ajax() {
		//template tags file is not loaded apparently so need to load:
		if ( is_readable( EE_PUBLIC . 'template_tags.php' )) {
			require_once( EE_PUBLIC . 'template_tags.php' );
		}
		/** @type EES_Espresso_My_Events $shortcode */
		$shortcode = EES_Espresso_My_Events::instance();
		$template_content = $shortcode->load_template( array(), false );
		$json_response = json_encode( array(
			'content' => $template_content
			)
		);
		// make sure there are no php errors or headers_sent.  Then we can set correct json header.
		if ( NULL === error_get_last() || ! headers_sent() )
			header('Content-Type: application/json; charset=UTF-8');
		echo $json_response;
		exit;
	}



	/**
	 * This loads the template for the shortcode.
	 *
	 * @param array $attributes    Any provided attributes that are being used.  Optional. Ajax requests usually do not
	 *                             have anything set for this.
	 * @param bool  $with_wrapper  indicate whether to load the template with the wrapper or not.  Ajax calls typically
	 *                             do not require the wrapper because its just the basic content that is changing.
	 * @return string  The generated html.
	 */
	public function load_template( $attributes = array(), $with_wrapper = true ) {
		//load helpers
		EE_Registry::instance()->load_helper( 'Template' );

		//add filter for locate template to add paths
		add_filter( 'FHEE__EEH_Template__locate_template__template_folder_paths', array( 'EES_Espresso_My_Events', 'template_paths' ), 10 );

		//set default attributes and filter
		$default_shortcode_attributes = apply_filters(
			'FHEE__EES_Espresso_My_Events__process_shortcode__default_shortcode_atts',
			array(
				'template' => 'event_section',
				'your_events_title' => esc_html__( 'Your Registrations', 'event_espresso' ),
				'your_tickets_title' => esc_html__( 'Your Tickets', 'event_espresso' ),
				'per_page' => 100,
				'with_wrapper' => $with_wrapper
			)
		);

		//merge with defaults
		$attributes = array_merge( $default_shortcode_attributes, (array) $attributes );
		$template_args = $this->_get_template_args( $attributes );

		if ( ! EE_FRONT_AJAX ) {
			$this->_enqueue_localized_js_object( $attributes );
		}

		return EEH_Template::locate_template( $template_args['path_to_template'], $template_args, true, true );
	}




	/**
	 * Callback for FHEE__EEH_Template__locate_template__template_folder_paths filter to register this shortcode's,
	 * template paths for locate_template.
	 *
	 * @param $template_folder_paths
	 * @return array
	 */
	public static function template_paths( $template_folder_paths ) {
		$template_folder_paths[] = EE_WPUSERS_TEMPLATE_PATH;
		return $template_folder_paths;
	}


	/**
	 * This takes care of setting up the localized object for js calls.
	 * We are able to set this up late in page load because our js is enqueued to load in the footer.
	 * @param array $attributes  Incoming array of attributes to use in the localized js object
	 */
	public function _enqueue_localized_js_object( $attributes ) {
		$attributes = (array) $attributes;
		$js_object = array(
			'template' => isset( $attributes['template'] ) ? $attributes['template'] : 'event_section',
			'per_page' => isset( $attributes['per_page'] ) ? $attributes['per_page'] : 10
		);

		wp_localize_script( 'ees-my-events-js', 'EE_MYE_JS', $js_object );
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
					'path' => 'loop-espresso_my_events-simple_list_table.template.php',
				),
				'event_section' => array(
					'object_type' => 'Event',
					'path' => 'loop-espresso_my_events-event_section.template.php'
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
			if (
				isset( $template_object_map[$template_slug]['object_type'] ) &&
				in_array( $template_object_map[$template_slug]['object_type'], $accepted_object_types )
			) {
				//next verify that the path for the template is valid
				if ( isset( $template_object_map[$template_slug]['path'] ) && EEH_File::is_readable( EE_WPUSERS_TEMPLATE_PATH . $template_object_map[$template_slug]['path'] ) ) {
					//yay made it here you awesome template object you.
					return $template_info;
				}
			}
		}
		//oh noes, not setup properly, so let's just use a safe known default.
		return array(
			'template' => 'event_section',
			'object_type' => 'Registration',
			'path' => 'loop-espresso_my_events-event_section.template.php'
		);
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
				'limit' => array( $offset, $template_args['per_page'] ),
				'group_by' => 'EVT_ID'
			);
			$model = EE_Registry::instance()->load_model( 'Event' );
		} elseif ( $template_args['object_type'] == 'Registration' ) {
			$query_args = array(
				0 => array( 'ATT_ID' => $att_id ),
				'limit' => array( $offset, $template_args['per_page'] )
			);
			$model = EE_Registry::instance()->load_model( 'Registration' );
		} else {
			//get out no valid object_types here.
			return $object_info;
		}
		
		$object_info['objects'] = $model->get_all( $query_args );
		$object_info['object_count'] = $model->count( array( $query_args[0] ), null, true );
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
		$page = (int) EE_Registry::instance()->REQ->get( 'ee_mye_page', 0 );

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


		$template = $attributes['template'] ? $attributes['template'] : 'event_section';
		$template_info = $this->_get_template_info( $template );

		//define template_args
		$template_args = array(
			'object_type' => $template_info['object_type'],
			'objects' => array(),
			'your_events_title' => isset( $attributes['your_events_title'] ) ? $attributes['your_events_title'] : $attributes['your_events_title'] ,
			'your_tickets_title' => isset( $attributes['your_tickets_title'] ) ? $attributes['your_tickets_title'] : $attributes['your_tickets_title'],
			'template_slug' => $template_info['template'],
			'per_page' => $per_page,
			'path_to_template' => $template_info['path'],
			'page' => $page,
			'object_count' => 0,
			'att_id' => 0,
			'with_wrapper' => isset( $attributes['with_wrapper'] ) ? $attributes['with_wrapper'] : true,
		);

		//grab any contact that is attached to this user
		$att_id = get_user_option( 'EE_Attendee_ID', get_current_user_id() );

		//if there is an attached attendee we can use that to retrieve all the related events and
		//registrations.  Otherwise those will be left empty.
		if ( $att_id ) {
			$object_info = $this->_get_template_objects( $att_id, $template_args );
			$template_args['objects'] = $object_info['objects'];
			$template_args['object_count'] = $object_info['object_count'];
			$template_args['att_id'] = $att_id;
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
					EES_Espresso_My_Events::get_current_page( null, true )
				);
			exit;
		}
	}

} //end EES_Espresso_My_Events