<?php
namespace EventEspresso\WpUser\domain\entities\shortcodes;

use EE_Registry;
use EventEspresso\core\services\shortcodes\EspressoShortcode;
use EventEspresso\core\services\cache\PostRelatedCacheManager;
use EE_Request;
use EEM_Event;
use EEM_Registration;
use EE_Registration;
use EED_Messages;
use EE_Error;
use EEH_URL;
use EEH_Template;
use WP_Query;
use EEH_File;

class EspressoMyEvents extends EspressoShortcode
{

    /**
     * Query argument key for indicating resending registration
     */
    const RESEND_QUERY_ARGUMENT_KEY = 'resend';

    /**
     * Query argument key for indicating the registration url identifier.
     */
    const REGISTRATION_TOKEN_QUERY_ARGUMENT_KEY = 'token';


    /**
     * Query argument key for indicating the current page value on a paged view.
     */
    const MY_EVENTS_PAGE_QUERY_ARGUMENT_KEY = 'ee_mye_page';

    /**
     * @var EEM_Event
     */
    private $event_model;


    /**
     * @var EEM_Registration
     */
    private $registration_model;


    /**
     * @var EE_Request
     */
    private $request;

    public function __construct(
        PostRelatedCacheManager $cache_manager,
        EE_Request $request,
        EEM_Event $event_model,
        EEM_Registration $registration_model
    ) {
        parent::__construct($cache_manager);
        $this->request = $request;
        $this->event_model = $event_model;
        $this->registration_model = $registration_model;

        // set ajax hooks
        $this->setAjaxHooks();
    }


    /**
     * the actual shortcode tag that gets registered with WordPress
     *
     * @return string
     */
    public function getTag()
    {
        return 'ESPRESSO_MY_EVENTS';
    }

    /**
     * the length of time in seconds to cache the results of the processShortcode() method
     * 0 means the processShortcode() results will NOT be cached at all
     *
     * @return int
     */
    public function cacheExpiration()
    {
        return 0;
    }

    /**
     * a place for adding any initialization code that needs to run prior to wp_header().
     * this may be required for shortcodes that utilize a corresponding module,
     * and need to enqueue assets for that module
     * !!! IMPORTANT !!!
     * After performing any logic within this method required for initialization
     *         $this->shortcodeHasBeenInitialized();
     * should be called to ensure that the shortcode is setup correctly.
     *
     * @return void
     * @throws EE_Error
     */
    public function initializeShortcode()
    {
        // if a resend registration confirmation is in the request then let's call that method which in turn could
        // end up redirecting.
        if ($this->request->is_set(self::RESEND_QUERY_ARGUMENT_KEY)) {
            $this->resendRegistrationConfirmationEmail();
        }

        // load assets
        wp_register_style(
            'ees-my-events',
            EE_WPUSERS_URL . 'assets/css/ees-espresso-my-events.css',
            array('espresso_default'),
            EE_WPUSERS_VERSION
        );
        wp_register_script(
            'ees-my-events-js',
            EE_WPUSERS_URL . 'assets/js/ees-espresso-my-events.js',
            array('espresso_core'),
            EE_WPUSERS_VERSION,
            true
        );
        wp_enqueue_style('ees-my-events');
        wp_enqueue_script('ees-my-events-js');

        $this->shortcodeHasBeenInitialized();
    }


    /**
     *
     */
    protected function setAjaxHooks()
    {
        // load ajax listeners
        add_action(
            'wp_ajax_ee_my_events_load_paged_template',
            array($this, 'loadPagedTemplateViaAjax')
        );
        add_action(
            'wp_ajax_nopriv_ee_my_events_load_paged_template',
            array($this, 'loadPagedTemplateViaAjax')
        );
    }


    /**
     * This takes care of setting up the localized object for js calls.
     * We are able to set this up late in page load because our js is enqueued to load in the footer.
     *
     * @param array $attributes Incoming array of attributes to use in the localized js object
     */
    public function enqueueLocalizedJavascriptObject($attributes)
    {
        $attributes = (array) $attributes;
        $js_object = array(
            'template' => isset($attributes['template']) ? $attributes['template'] : 'event_section',
            'per_page' => isset($attributes['per_page']) ? $attributes['per_page'] : 10,
        );
        wp_localize_script('ees-my-events-js', 'EE_MYE_JS', $js_object);
    }

    /**
     * callback that runs when the shortcode is encountered in post content.
     * IMPORTANT !!!
     * remember that shortcode content should be RETURNED and NOT echoed out
     *
     * @param array $attributes
     * @return string
     * @throws EE_Error
     */
    public function processShortcode($attributes = array())
    {
        // If use is not logged in, then we display a link to login.
        if (! is_user_logged_in()) {
            $redirect_url = EEH_URL::current_url();
            /**
             * This filter is using the old filter identifier for back compat.
             */
            return apply_filters(
                'FHEE__Espresso_My_Events__process_shortcode__redirect_to_login_instructions',
                '<a class="ee-wpui-login-link" href="' . wp_login_url($redirect_url) . '">'
                . esc_html__('Login to see your registrations.', 'event_espresso') . '</a>'
            );
        }

        // if made it here then all assets should be loaded and we are good to go!
        return $this->loadTemplate($attributes);
    }


    /**
     * Callback for ajax hooks.
     * Takes care of returning the content requested by the ajax request.
     */
    public function loadPagedTemplateViaAjax()
    {
        // intialize attributes array
        $attributes = array();
        // template file sent with the request?
        if ($this->request->get('template', '') !== '') {
            $attributes['template'] = $this->request->get('template');
        }
        // template tags file is not loaded apparently so need to load:
        if (is_readable(EE_PUBLIC . 'template_tags.php')) {
            require_once EE_PUBLIC . 'template_tags.php';
        }

        $template_content = $this->loadTemplate($attributes, false);
        $json_response = wp_json_encode(
            array(
                'content' => $template_content,
            )
        );
        // make sure there are no php errors or headers_sent.  Then we can set correct json header.
        if (null === error_get_last() || ! headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
        }
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
     * @return string The generated html.
     * @throws EE_Error
     */
    protected function loadTemplate($attributes = array(), $with_wrapper = true)
    {
        // add filter for locate template to add paths
        add_filter(
            'FHEE__EEH_Template__locate_template__template_folder_paths',
            array($this, 'templatePaths'),
            10
        );

        // set default attributes and filter
        // note this is keeping the old filter string for backwards compatibility reasons.
        $default_shortcode_attributes = apply_filters(
            'FHEE__EES_Espresso_My_Events__process_shortcode__default_shortcode_atts',
            array(
                'template'           => 'event_section',
                'your_events_title'  => esc_html__('Your Registrations', 'event_espresso'),
                'your_tickets_title' => esc_html__('Your Tickets', 'event_espresso'),
                'per_page'           => 100,
                'with_wrapper'       => $with_wrapper,
            )
        );

        // merge with defaults
        $attributes = array_merge($default_shortcode_attributes, (array) $attributes);
        $template_args = $this->getTemplateArguments($attributes);

        if (! $this->request->front_ajax) {
            $this->enqueueLocalizedJavascriptObject($attributes);
        }

        return EEH_Template::locate_template($template_args['path_to_template'], $template_args);
    }


    /**
     * Callback for FHEE__EEH_Template__locate_template__template_folder_paths filter to register this shortcode's,
     * template paths for locate_template.
     *
     * @param $template_folder_paths
     * @return array
     */
    public function templatePaths($template_folder_paths)
    {
        $template_folder_paths[] = EE_WPUSERS_TEMPLATE_PATH;
        return $template_folder_paths;
    }


    /**
     * This sets up the template arguments for the shortcode template being parsed.
     *
     * @param array $attributes incoming shortcode attributes, these will already have defaults applied.
     * @return array
     * @throws EE_Error
     */
    protected function getTemplateArguments($attributes)
    {
        // any parameters coming from the request?
        $per_page = (int) $this->request->get('per_page', $attributes['per_page']);
        $page = (int) $this->request->get(self::MY_EVENTS_PAGE_QUERY_ARGUMENT_KEY, 0);

        // if $page is empty then it's likely this is being loaded outside of ajax and wp has usurped
        // the page value for its query.  So let's see if its in the query.
        if (! $page) {
            global $wp_query;
            if ($wp_query instanceof WP_Query && isset($wp_query->query, $wp_query->query['paged'])) {
                $page = $wp_query->query['paged'];
            } else {
                $page = 1;
            }
        }


        $template = $attributes['template'] ? $attributes['template'] : 'event_section';
        $template_info = $this->getTemplateInfo($template);

        // define template_args
        $template_args = array(
            'object_type'        => $template_info['object_type'],
            'objects'            => array(),
            'your_events_title'  => $attributes['your_events_title'],
            'your_tickets_title' => $attributes['your_tickets_title'],
            'template_slug'      => $template_info['template'],
            'per_page'           => $per_page,
            'path_to_template'   => $template_info['path'],
            'page'               => $page,
            'object_count'       => 0,
            'att_id'             => 0,
            'with_wrapper'       => $attributes['with_wrapper'],
        );

        // grab any contact that is attached to this user
        $attendee_id = get_user_option('EE_Attendee_ID', get_current_user_id());

        // if there is an attached attendee we can use that to retrieve all the related events and
        // registrations.  Otherwise those will be left empty.
        if ($attendee_id) {
            $object_info = $this->getTemplateObjects($attendee_id, $template_args);
            $template_args['objects'] = $object_info['objects'];
            $template_args['object_count'] = $object_info['object_count'];
            $template_args['att_id'] = $attendee_id;
        }
        return $template_args;
    }


    /**
     * This verifies if there is a registered template for the given slug and that the values for the registered
     * path check out.  Otherwise, load a default.
     *
     * @param string $template_slug The template slug to check for.
     * @return array a formatted array containing:
     *                              array(
     *                              'template' => 'template_slug', //validated slug for template
     *                              'object_type' => 'Registration', //validated object type for template
     *                              'path' => 'full_path_to/template', //validate full path to template on the server
     *                              )
     * @throws EE_Error
     */
    protected function getTemplateInfo($template_slug = '')
    {
        $template_object_map = $this->getTemplateObjectMap();

        // first verify that the given slug is in the map.
        if (is_string($template_slug) && ! empty($template_slug) && isset($template_object_map[ $template_slug ])) {
            $template_info = $template_object_map[ $template_slug ];
            $template_info['template'] = $template_slug;
            // next verify that there is an object type and that it matches one of the EE models used for querying.
            $accepted_object_types = array('Event', 'Registration');
            if (isset($template_object_map[ $template_slug ]['object_type'], $template_object_map[ $template_slug ]['path'])
                && in_array($template_object_map[ $template_slug ]['object_type'], $accepted_object_types, true)
            ) {
                // yay made it here you awesome template object you.
                return $template_info;
            }
        }
        // oh noes, not setup properly, so let's just use a safe known default.
        return array(
            'template'    => 'event_section',
            'object_type' => 'Event',
            'path'        => 'loop-espresso_my_events-event_section.template.php',
        );
    }


    protected function getTemplateObjectMap()
    {
        /**
         * This filter can be used to add custom templates for this shortcode.
         * Once custom templates can be registered, then they can be utilized in the `[ESPRESSO_MY_EVENTS]`
         * shortcode via the "template" parameter.
         * Note: The filter identifier is the old one for backwards compatibility.
         */
        return apply_filters(
            'FHEE__EES_Espresso_My_Events__process_shortcode_template_object_map',
            array(
                'simple_list_table' => array(
                    'object_type' => 'Registration',
                    'path'        => 'loop-espresso_my_events-simple_list_table.template.php',
                ),
                'event_section'     => array(
                    'object_type' => 'Event',
                    'path'        => 'loop-espresso_my_events-event_section.template.php',
                ),
            )
        );
    }


    /**
     * This returns an array of EE_Base_Class objects matching the params in the given template args.
     *
     * @param int   $attendee_id        This should be the ID of the EE_Attendee being queried against
     * @param array $template_arguments The generated template args for the template.
     * @return array Returns an array:
     *                                  array(
     *                                  'objects' => array(), //an array of EE_Base_Class objects
     *                                  'object_count' => 0, //count of all records matching query params without
     *                                  limits.
     *                                  );
     * @throws EE_Error
     */
    protected function getTemplateObjects($attendee_id, $template_arguments = array())
    {
        $object_info = array(
            'objects'      => array(),
            'object_count' => 0,
        );

        // required values available?
        if (empty($template_arguments)
            || empty($template_arguments['object_type'])
            || empty($template_arguments['per_page'])
            || empty($template_arguments['page'])) {
            return $object_info; // need info yo.
        }

        $offset = ($template_arguments['page'] - 1) * $template_arguments['per_page'];
        $attendee_id = (int) $attendee_id;

        if ($template_arguments['object_type'] === 'Event') {
            $query_args = array(
                0          => array('Registration.ATT_ID' => $attendee_id),
                'limit'    => array($offset, $template_arguments['per_page']),
                'group_by' => 'EVT_ID',
            );
            $model = $this->event_model;
        } elseif ($template_arguments['object_type'] === 'Registration') {
            $query_args = array(
                0       => array('ATT_ID' => $attendee_id),
                'limit' => array($offset, $template_arguments['per_page']),
            );
            $model = $this->registration_model;
        } else {
            // get out no valid object_types here.
            return $object_info;
        }
        // allow $query_args to be filtered.
        $query_args = (array) apply_filters(
            'FHEE__Espresso_My_Events__getTemplateObjects__query_args',
            $query_args,
            $template_arguments,
            $attendee_id
        );
        $object_info['objects'] = $model->get_all($query_args);
        $object_info['object_count'] = $model->count(array($query_args[0]), null, true);
        return $object_info;
    }


    /**
     * Handles passing off a registration to be processed by the EED_Messages module for resending the registration
     * confirmation email.
     *
     * @throws EE_Error
     */
    protected function resendRegistrationConfirmationEmail()
    {
        $registration_link = $this->request->get(self::REGISTRATION_TOKEN_QUERY_ARGUMENT_KEY);

        // was a REG_ID passed ?
        if ($registration_link) {
            $registration = $this->registration_model->get_one(array(array('REG_url_link' => $registration_link)));
            if ($registration instanceof EE_Registration) {
                // resend email
                EED_Messages::process_resend(array('_REG_ID' => $registration->ID()));
            } else {
                EE_Error::add_error(
                    esc_html__(
                        'The Registration Confirmation email could not be sent because a valid Registration could not be retrieved from the database.',
                        'event_espresso'
                    ),
                    __FILE__,
                    __FUNCTION__,
                    __LINE__
                );
            }
        } else {
            EE_Error::add_error(
                esc_html__(
                    'The Registration Confirmation email could not be sent because a registration token is missing or invalid.',
                    'event_espresso'
                ),
                __FILE__,
                __FUNCTION__,
                __LINE__
            );
        }
        // request sent via AJAX ?
        if ($this->request->front_ajax) {
            echo wp_json_encode(EE_Error::get_notices(false));
            die();
            // or was JS disabled ?
        }
        // save errors so that they get picked up on the next request
        EE_Error::get_notices(true, true);
        wp_safe_redirect(
            EEH_URL::current_url_without_query_paramaters(
                array(
                    self::RESEND_QUERY_ARGUMENT_KEY,
                    self::REGISTRATION_TOKEN_QUERY_ARGUMENT_KEY,
                )
            )
        );
        exit;
    }
}
