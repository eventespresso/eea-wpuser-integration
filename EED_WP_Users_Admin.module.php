<?php
if ( ! defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}
/**
 * This file contains the module for the EE WP Users addon ee admin integration
 *
 * @since      1.0.0
 * @package    EE WP Users
 * @subpackage modules, admin
 */

/**
 *
 * EED_WP_Users_Adminmodule.  Takes care of WP Users integration with EE admin.
 *
 * @since          1.0.0
 *
 * @package        EE WP Users
 * @subpackage     modules, admin
 * @author         Darren Ethier
 *
 * ------------------------------------------------------------------------
 */
class EED_WP_Users_Admin extends EED_Module
{
    
    
    public static function set_hooks()
    {
    }
    
    public static function set_hooks_admin()
    {
        add_action('admin_enqueue_scripts', array('EED_WP_Users_Admin', 'admin_enqueue_scripts_styles'));
        
        //hook into EE contact publish metabox.
        add_action('post_submitbox_misc_actions', array('EED_WP_Users_Admin', 'add_link_to_wp_user_account'));
        
        //hook into wp users
        add_action('edit_user_profile', array('EED_WP_Users_Admin', 'add_link_to_ee_contact_details'));
        add_action('show_user_profile', array('EED_WP_Users_Admin', 'add_link_to_ee_contact_details'));
        add_action('edit_user_profile', array('EED_WP_Users_Admin', 'view_registrations_for_contact'));
        add_action('show_user_profile', array('EED_WP_Users_Admin', 'view_registrations_for_contact'));
        add_action('profile_update', array('EED_WP_Users_Admin', 'sync_with_contact'), 10, 2);
        add_action('user_register', array('EED_WP_Users_Admin', 'sync_with_contact'));
        
        //hook into attendee saves
        add_filter('FHEE__Registrations_Admin_Page__insert_update_cpt_item__attendee_update',
            array('EED_WP_Users_Admin', 'add_sync_with_wp_users_callback'), 10);
        
        //hook into registration_form_admin_page routes and config.
        add_filter('FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_routes',
            array('EED_WP_Users_Admin', 'add_wp_user_default_settings_route'), 10, 2);
        add_filter('FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_config',
            array('EED_WP_Users_Admin', 'add_wp_user_default_settings_config'), 10, 2);
        add_filter('FHEE__Extend_Events_Admin_Page__page_setup__page_config',
            array('EED_WP_Users_Admin', 'add_ticket_capability_help_tab'), 10, 2);
        add_filter('FHEE__EE_Admin_Page___publish_post_box__box_label',
            array('EED_WP_Users_Admin', 'modify_settings_publish_box_label'), 10, 3);
        
        //hooking into event editor
        add_action('add_meta_boxes', array('EED_WP_Users_Admin', 'add_metaboxes'));
        add_filter('FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks',
            array('EED_WP_Users_Admin', 'set_callback_save_wp_user_event_setting'), 10, 2);
        add_filter('FHEE__EED_WP_Users_Admin__event_editor_metabox__wp_user_form_content',
            array('EED_WP_Users_Admin', 'set_capability_default_user_create_role_event_editor'), 10);
        
        //hook into ticket editor in event editor.
        add_action('AHEE__event_tickets_datetime_ticket_row_template__advanced_details_end',
            array('EED_WP_Users_Admin', 'insert_ticket_meta_interface'), 10, 2);
        add_action('AHEE__espresso_events_Pricing_Hooks___update_tkts_new_ticket',
            array('EED_WP_Users_Admin', 'update_capability_on_ticket'), 10, 4);
        add_action('AHEE__espresso_events_Pricing_Hooks___update_tkts_update_ticket',
            array('EED_WP_Users_Admin', 'update_capability_on_ticket'), 10, 4);
        add_action('AHEE__espresso_events_Pricing_Hooks___update_tkts_new_default_ticket',
            array('EED_WP_Users_Admin', 'update_capability_on_ticket'), 10, 4);
        
        
        //hook into model deletes that may affect relations set on WP_User.
        add_action('AHEE__EE_Base_Class__delete_permanently__before',
            array('EED_WP_Users_Admin', 'remove_relations_on_delete'));
    }
    
    
    public static function admin_enqueue_scripts_styles()
    {
        if (get_current_screen()->base == 'profile' || get_current_screen()->base == 'user-edit') {
            wp_register_style('ee-admin-css', EE_ADMIN_URL . 'assets/ee-admin-page.css', array(),
                EVENT_ESPRESSO_VERSION);
            wp_register_style('espresso_att', REG_ASSETS_URL . 'espresso_attendees_admin.css', array('ee-admin-css'),
                EVENT_ESPRESSO_VERSION);
            wp_enqueue_style('espresso_att');
        }
    }
    
    
    public function run($WP)
    {
    }
    
    
    /**
     * Register metaboxes for event editor.
     */
    public static function add_metaboxes()
    {
        $page  = EE_Registry::instance()->REQ->get('page');
        $route = EE_Registry::instance()->REQ->get('action');
        
        // on event editor page?
        if ($page == 'espresso_events' && ($route == 'edit' || $route == 'create_new')) {
            add_meta_box('eea_wp_user_integration', __('User Integration Settings', 'event_espresso'),
                array('EED_WP_Users_Admin', 'event_editor_metabox'), null, 'side', 'default');
        }
    }
    
    
    public static function add_sync_with_wp_users_callback($callbacks)
    {
        $callbacks[] = array('EED_WP_Users_Admin', 'sync_with_wp_user');
        
        return $callbacks;
    }
    
    
    /**
     * Callback for post_submitbox_misc_actions that adds a link to the wp user
     * edit page for the user attached to the EE_Attendee (if present).
     *
     * @since 1.0.0
     */
    public static function add_link_to_wp_user_account()
    {
        global $post;
        if ( ! $post instanceof WP_Post || $post->post_type != 'espresso_attendees') {
            return;
        }
        
        //is there an attached wp_user for this attendee record?
        $user_id = EE_WPUsers::get_attendee_user($post->ID);
        
        if (empty($user_id)) {
            return;
        }
        
        
        //let's get the WP_user and setup the link
        $url = get_edit_user_link($user_id);
        
        //if $url is empty, that means logged in user does not have access to view user details so we bail.
        if (empty($url)) {
            return;
        }
        
        //we HAVE url so let's assemble the item to display.
        ?>
        <div class="misc-pub-section">
            <span class="dashicons dashicons-universal-access ee-icon-color-grey ee-icon-size-20"></span>
            <a href="<?php echo $url; ?>" title="<?php _e('Click to view WordPress user profile',
                'event_espresso'); ?>"><?php _e('WordPress User Profile', 'event_espresso'); ?></a>
        </div>
        <?php
    }
    
    
    /**
     * callback for edit_user_profile that is used to display a table of all the registrations this
     * user is connected with.
     *
     * @param WP_User $user
     *
     * @return string
     */
    public static function view_registrations_for_contact($user)
    {
        if ( ! $user instanceof WP_User) {
            return '';
        }
        
        //is there an attadched EE_Attendee?
        $att_id = get_user_option('EE_Attendee_ID', $user->ID);
        
        if (empty($att_id)) {
            return; //bail, no attached attendee_id.
        }
        
        //grab contact
        $contact = EEM_Attendee::instance()->get_one_by_ID($att_id);
        
        //if no contact then bail
        if ( ! $contact instanceof EE_Attendee) {
            return;
        }
        
        $template_args = array(
            'attendee'      => $contact,
            'registrations' => $contact->get_many_related('Registration')
        );
        EEH_Template::display_template(EE_WPUSERS_TEMPLATE_PATH . 'eea-wp-users-registrations-table.template.php',
            $template_args);
    }
    
    
    /**
     * callback for edit_user_profile that is used to add link to the EE_Attendee
     * details if there is one attached to the user.
     *
     * @param WP_User $user
     */
    public static function add_link_to_ee_contact_details($user)
    {
        if ( ! $user instanceof WP_User) {
            return;
        }
        
        //is there an attached EE_Attendee?
        $att_id = get_user_option('EE_Attendee_ID', $user->ID);
        
        if (empty($att_id)) {
            return; //bail, no attached attendee_id.
        }
        
        //does logged in user have the capability to edit this attendee?
        if ( ! EE_Registry::instance()->CAP->current_user_can('ee_edit_contacts', 'edit_attendee', $att_id)) {
            return; //bail no access.
        }
        
        //url
        $url = admin_url(add_query_arg(array(
            'page'   => 'espresso_registrations',
            'action' => 'edit_attendee',
            'post'   => $att_id
        ), 'admin.php'));
        ?>
        <table class="form-table">
            <tr class="ee-wpuser-integration-row">
                <th></th>
                <td>
                    <p><?php _e('When you save this user profile, the details will be synced with the attached Event Espresso Contact',
                            'event_espresso'); ?></p>
                    <p><a class="button button-secondary" href="<?php echo $url; ?>"
                          title="<?php _e('Click to go to Attendee Details',
                              'event_espresso'); ?>"><?php _e('View Linked Contact', 'event_espresso'); ?></a></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    
    /**
     * Callback for the 'profile_update' and 'user_register' hooks that enable syncing saved user data
     * with an EE_Attendee record.
     * This callback detects whether we're creating a user record or not.
     * If creating:
     *    - Is there already an EE_Contact that matches the first name/last name/email address of
     *    the user record?
     *    - Yes -> attach it.
     *    - No -> create it.
     *
     * If updating:
     *    - Is there already an attached EE_Contact record on the user account?
     *    - Yes -> update it.
     *    - No -> do the same as when we create user.
     *
     *
     * @since 1.0.0
     *
     * @param int      $user_id                        The id of the user that was just created/updated.
     * @param obj|null $old_user_data                  Object container user's data prior to update.  If empty, then
     *                                                 the user_register hook was fired.
     *
     * @return void
     */
    public static function sync_with_contact($user_id, $old_user_data = null)
    {
        $user = get_userdata($user_id);
        
        //creating?
        if (empty($old_user_data)) {
            self::_connect_wp_user_with_contact($user);
            
            return;
        }
        
        //if we make it here then we're updating an existing user
        $att_id = get_user_option('EE_Attendee_ID', $user->ID);
        
        if (empty($att_id)) {
            self::_connect_wp_user_with_contact($user);
            
            return;
        } else {
            //update the existing attendee attached to the wp_user!
            $att = EE_Registry::instance()->load_model('Attendee')->get_one_by_ID($att_id);
            if ($att instanceof EE_Attendee) {
                $att->set_email($user->user_email);
                $att->set_fname($user->first_name);
                $att->set_lname($user->last_name);
                $att->set('ATT_bio', $user->user_description);
                $att->save();
            }
        }
        
        return;
    }
    
    
    /**
     * Callback for FHEE__Registrations_Admin_Page__insert_update_cpt_item__attendee_update
     * filter.  Used to sync the saved Attendee data with any attached wp_user.
     * Note: currently this does NOT create a user.
     *
     * @param EE_Attendee $attendee
     * @param array       $request_data The request data from the save.
     *
     * @return void
     */
    public static function sync_with_wp_user(EE_Attendee $attendee, $request_data)
    {
        //is there a user for this attendee ID?
        $user_id = EE_WPUsers::get_attendee_user($attendee->ID());
        
        if (empty($user_id)) {
            return;
        }
        
        //made it here, so let's sync the main attendee details with the user account
        //remove the existing action for updates so that we don't cause recursion.
        remove_action('profile_update', array('EED_WP_Users_Admin', 'sync_with_contact'));
        wp_update_user(
            array(
                'ID'          => $user_id,
                'first_name'  => $attendee->fname(),
                'last_name'   => $attendee->lname(),
                'user_email'  => $attendee->email(),
                'description' => $attendee->get('ATT_bio'),
            )
        );
        
        return;
    }
    
    
    /**
     * This takes an incoming wp_user object and either connects it with an existing contact that
     * matches its details, or creates a new attendee and attaches.
     *
     * @since 1.0.0
     *
     * @param WP_User $user
     *
     * @return EE_Attendee
     */
    protected static function _connect_wp_user_with_contact(WP_User $user)
    {
        //no attached EE_Attendee. Is there an existing attendee that matches this user's details?
        $att = self::_find_existing_attendee_from_wpuser($user);
        if ($att instanceof EE_Attendee && ! EE_WPUsers::get_attendee_user($att->ID())) {
            update_user_option($user->ID, 'EE_Attendee_ID', $att->ID());
        }
        
        return $att;
    }
    
    
    /**
     * Using the given WP_User object, this method finds an EE_Attendee that matches email
     * address, first name, last name and returns if it exists.
     *
     * @param WP_User $user
     *
     * @return EE_Attendee|bool false if EE_Attendee does not exist.
     */
    protected static function _find_existing_attendee_from_wpuser(WP_User $user)
    {
        $existing_attendee = EE_Registry::instance()->load_model('Attendee')->find_existing_attendee(array(
            'ATT_fname' => $user->first_name,
            'ATT_lname' => $user->last_name,
            'ATT_email' => $user->user_email
        ));
        
        return $existing_attendee instanceof EE_Attendee ? $existing_attendee : false;
    }
    
    
    /**
     * This creates an EE_Attendee record using data from the given user and attaches that
     * EE_Attendee to the user.
     *
     * @since 1.0.0
     *
     * @param WP_User $user
     *
     * @return EE_Attendee
     */
    protected static function _create_attendee_and_attach_wpuser(WP_User $user)
    {
        $att = EE_Attendee::new_instance(array(
            'ATT_fname' => $user->first_name,
            'ATT_lname' => $user->last_name,
            'ATT_email' => $user->user_email,
            'ATT_bio'   => $user->user_description,
        ));
        $att->save();
        
        //attach to user
        update_user_option($user->ID, 'EE_Attendee_ID', $att->ID());
        
        return $att;
    }
    
    
    /**
     * callback for FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_routes.
     * Add additional routes for saving WP_User settings to the Registration Form admin page system
     *
     * @param array         $page_routes current array of page routes.
     * @param EE_Admin_Page $admin_page
     *
     * @since 1.0.0
     *
     * @return array
     */
    public static function add_wp_user_default_settings_route($page_routes, EE_Admin_Page $admin_page)
    {
        $page_routes['wp_user_settings']        = array(
            'func'       => array('EED_WP_Users_Admin', 'wp_user_settings'),
            'args'       => array($admin_page),
            'capability' => 'manage_options'
        );
        $page_routes['update_wp_user_settings'] = array(
            'func'       => array('EED_WP_Users_Admin', 'update_wp_user_settings'),
            'args'       => array($admin_page),
            'capability' => 'manage_options',
            'noheader'   => true
        );
        
        return $page_routes;
    }
    
    
    /**
     * callback for the wp_user_settings route.
     *
     * @param EE_Admin_Page $admin_page
     *
     * @return string html for displaying wp_user_settings.
     */
    public static function wp_user_settings(EE_Admin_Page $admin_page)
    {
        $template_args['admin_page_content'] = self::_wp_user_settings_form()->get_html_and_js();
        $admin_page->set_add_edit_form_tags('update_wp_user_settings');
        $admin_page->set_publish_post_box_vars(null, false, false, null, false);
        $admin_page->set_template_args($template_args);
        $admin_page->display_admin_page_with_sidebar();
    }
    
    
    /**
     * This outputs the settings form for WP_User_integration.
     *
     * @since 1.0.0
     *
     * @return string html form.
     */
    protected static function _wp_user_settings_form()
    {
        EE_Registry::instance()->load_helper('HTML');
        EE_Registry::instance()->load_helper('Template');
        
        return new EE_Form_Section_Proper(
            array(
                'name'            => 'wp_user_settings_form',
                'html_id'         => 'wp_user_settings_form',
                'layout_strategy' => new EE_Div_Per_Section_Layout(),
                'subsections'     => apply_filters('FHEE__EED_WP_Users_Admin___wp_user_settings_form__form_subsections',
                    array(
                        'main_settings_hdr' => new EE_Form_Section_HTML(EEH_HTML::h3(__('WP User Integration Defaults',
                            'event_espresso'))),
                        'main_settings'     => EED_WP_Users_Admin::_main_settings()
                    )
                )
            )
        );
    }
    
    
    /**
     * Output the main settings section for wp_user_integration settings page.
     *
     * @return string html form.
     */
    protected static function _main_settings()
    {
        global $wp_roles;
        $registration_turned_off_msg = get_option('users_can_register') ? '' : '<br><div class="error inline"><p></p>' . sprintf(__('Registration is currently turned off for your site, so the registration link will not show.  If you want the registration link to show please %sgo here%s to turn it on.',
                'event_espresso'), '<a href="' . admin_url('options-general.php') . '">', '</a>') . '</p></div>';
        
        return new EE_Form_Section_Proper(
            array(
                'name'            => 'wp_user_settings_tbl',
                'html_id'         => 'wp_user_settings_tbl',
                'html_class'      => 'form-table',
                'layout_strategy' => new EE_Admin_Two_Column_Layout(),
                'subsections'     => apply_filters('FHEE__EED_WP_Users_Admin___main_settigns__form_subsections',
                    array(
                        'force_login'            => new EE_Yes_No_Input(
                            array(
                                'html_label_text'         => __('Default setting for Login Required on Registration',
                                    'event_espresso'),
                                'html_help_text'          => __('When this is set to "Yes", that means when you create an event the default for the "Login Required" setting on that event will be set to "Yes".  When Login Required is set to "Yes" on an event it means that before users can register they MUST be logged in.  You can still override this on each event.',
                                    'event_espresso'),
                                'default'                 => isset(EE_Registry::instance()->CFG->addons->user_integration->force_login) ? EE_Registry::instance()->CFG->addons->user_integration->force_login : false,
                                'display_html_label_text' => false
                            )
                        ),
                        'registration_page'      => new EE_Text_Input(
                            array(
                                'html_label_text'         => __('Registration Page URL (if different from default WordPress Registration)',
                                    'event_espresso'),
                                'html_help_text'          => __('When login is required on an event, this will be the url used for the registration link on the login form',
                                        'event_espresso') . $registration_turned_off_msg,
                                'default'                 => isset(EE_Registry::instance()->CFG->addons->user_integration->registration_page) ? EE_Registry::instance()->CFG->addons->user_integration->registration_page : '',
                                'display_html_label_text' => true
                            )
                        ),
                        'auto_create_user'       => new EE_Yes_No_Input(
                            array(
                                'html_label_text'         => __('Default setting for User Creation on Registration.',
                                    'event_espresso'),
                                'html_help_text'          => __('When this is set to "Yes", that means when you create an event the default for the "Create User On Registration" setting on that event will be set to "Yes".  When this setting is set to "Yes" on an event it means that when new non-logged in users register for an event, a new WP_User is created for them.  You can still override this on each event.',
                                    'event_espresso'),
                                'default'                 => isset(EE_Registry::instance()->CFG->addons->user_integration->auto_create_user) ? EE_Registry::instance()->CFG->addons->user_integration->auto_create_user : false,
                                'display_html_label_text' => false
                            )
                        ),
                        'default_wp_user_role'   => new EE_Select_Input(
                            $wp_roles->get_names(),
                            array(
                                'html_label_text'         => __('Default role for User Creation on Registration.',
                                    'event_espresso'),
                                'html_help_text'          => __('On new events, when User creation is set to yes, this setting indicates what the default role for new users will be on creation. You can still override this on each event.',
                                    'event_espresso'),
                                'default'                 => isset(EE_Registry::instance()->CFG->addons->user_integration->default_wp_user_role) ? EE_Registry::instance()->CFG->addons->user_integration->default_wp_user_role : 'subscriber',
                                'display_html_label_text' => false
                            )
                        ),
                        'sync_user_with_contact' => new EE_Yes_No_Input(
                            array(
                                'html_label_text'         => __('Always sync contact information with WP user profile?',
                                        'event_espresso') . EEH_Template::get_help_tab_link('user_sync_info'),
                                'html_help_text'          => __(
                                    'This global option is used to indicate behaviour when a logged in user registers for an event, and what happens to that user’s related contact, which in turn is related to the primary registration.',
                                    'event_espresso'
                                ),
                                'default'                 => isset(EE_Registry::instance()->CFG->addons->user_integration->sync_user_with_contact) ? EE_Registry::instance()->CFG->addons->user_integration->sync_user_with_contact : true,
                                'display_html_label_text' => false
                            )
                        )
                    ) //end form subsections
                ) //end apply_filters for form subsections
            )
        );
    }
    
    
    /**
     * callback for the update_wp_user_settings route.
     * This handles the config update when the settings are saved.
     *
     * @param EE_Admin_Page $admin_page
     *
     * @return void
     */
    public static function update_wp_user_settings(EE_Admin_Page $admin_page)
    {
        $config = EE_Registry::instance()->CFG->addons->user_integration;
        try {
            $form = self::_wp_user_settings_form();
            if ($form->was_submitted()) {
                //capture form data
                $form->receive_form_submission();
                
                //validate_form_data
                if ($form->is_valid()) {
                    $valid_data                     = $form->valid_data();
                    $config->force_login            = $valid_data['main_settings']['force_login'];
                    $config->registration_page      = $valid_data['main_settings']['registration_page'];
                    $config->auto_create_user       = $valid_data['main_settings']['auto_create_user'];
                    $config->default_wp_user_role   = $valid_data['main_settings']['default_wp_user_role'];
                    $config->sync_user_with_contact = $valid_data['main_settings']['sync_user_with_contact'];
                }
            } else {
                if ($form->submission_error_message() != '') {
                    EE_Error::add_error($form->submission_error_message(), __FILE__, __FUNCTION__, __LINE__);
                }
            }
        } catch (EE_Error $e) {
            $e->get_error();
        }
        
        EE_Error::add_success(__('User Integration Settings updated.', 'event_espresso'));
        EE_Registry::instance()->CFG->update_config('addons', 'user_integration', $config);
        $admin_page->redirect_after_action(false, '', '', array('action' => 'wp_user_settings'), true);
    }
    
    
    /**
     * callback for FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_config.
     * Add additional config for saving WP_User settings to the Registration Form admin page system.
     *
     * @param array         $page_config current page config.
     * @param EE_Admin_Page $admin_page
     *
     * @since  1.0.0
     *
     * @return array
     */
    public static function add_wp_user_default_settings_config($page_config, EE_Admin_Page $admin_page)
    {
        $page_config['wp_user_settings'] = array(
            'nav'           => array(
                'label' => __('User Integration Settings', 'event_espresso'),
                'order' => 50
            ),
            'require_nonce' => false,
            'help_tabs'     => array(
                'wp_user_settings_help_tab' => array(
                    'title'   => __('WP User Settings', 'event_espresso'),
                    'content' => self::_settings_help_tab_content()
                )
            ),
            'metaboxes'     => array('_publish_post_box', '_espresso_news_post_box', '_espresso_links_post_box')
        );
        
        return $page_config;
    }
    
    
    /**
     * Callback for the WP Users Settings help tab content as set in the page_config array
     *
     * @return string
     */
    protected static function _settings_help_tab_content()
    {
        EE_Registry::instance()->load_helper('Template');
        
        return EEH_Template::display_template(EE_WPUSERS_TEMPLATE_PATH . 'settings_help_tab.help_tab.php', array(),
            true);
    }
    
    
    /**
     * Callback for FHEE__Extend_Events_Admin_Page__page_setup__page_config.
     * Just injecting config for help tab contents added for ticket capability fields.
     *
     * @param array         $page_config current page config
     * @param EE_Admin_Page $admin_page
     *
     * @since 1.0.0
     *
     * @return array
     */
    public static function add_ticket_capability_help_tab($page_config, EE_Admin_Page $admin_page)
    {
        EE_Registry::instance()->load_helper('Template');
        $file                                                             = EE_WPUSERS_TEMPLATE_PATH . 'ticket_capability_help_content.template.php';
        $page_config['create_new']['help_tabs']['ticket_capability_info'] = array(
            'title'   => __('Ticket Capability Restrictions', 'event_espresso'),
            'content' => EEH_Template::display_template($file, array(), true)
        );
        $page_config['edit']['help_tabs']['ticket_capability_info']       = array(
            'title'   => __('Ticket Capability Restrictions', 'event_espresso'),
            'content' => EEH_Template::display_template($file, array(), true)
        );
        
        return $page_config;
    }
    
    
    /**
     * This is the metabox content for the wp user integration in the event editor.
     *
     * @param WP_Post $post
     * @param array   $metabox metabox arguments
     *
     * @return string html for metabox content.
     */
    public static function event_editor_metabox($post, $metabox)
    {
        //setup form and print out!
        echo self::_get_event_editor_wp_users_form($post)->get_html_and_js();
    }
    
    
    /**
     * Generate the event editor wp user settings form.
     *
     * @return EE_Form_Section_Proper
     */
    protected static function _get_event_editor_wp_users_form($post)
    {
        global $wp_roles;
        $evt_id = $post instanceof EE_Event ? $post->ID() : null;
        $evt_id = empty($evt_id) && isset($post->ID) ? $post->ID : 0;
        EE_Registry::instance()->load_helper('HTML');
        
        return new EE_Form_Section_Proper(
            array(
                'name'            => 'wp_user_event_settings_form',
                'html_id'         => 'wp_user_event_settings_form',
                'layout_strategy' => new EE_Div_Per_Section_Layout(),
                'subsections'     => apply_filters('FHEE__EED_WP_Users_Admin__event_editor_metabox__wp_user_form_content',
                    array(
                        'force_login'              => new EE_Yes_No_Input(
                            array(
                                'html_label_text'         => __('Force Login for registrations?', 'event_espresso'),
                                'html_help_text'          => __('If yes, then all people registering for this event must login before they can register',
                                    'event_espresso'),
                                'default'                 => EE_WPUsers::is_event_force_login($evt_id),
                                'display_html_label_text' => true
                            )
                        ),
                        'spacing1'                 => new EE_Form_Section_HTML('<br>'),
                        'auto_user_create'         => new EE_Yes_No_Input(
                            array(
                                'html_label_text'         => __('Auto Create users with registrations?',
                                    'event_espresso'),
                                'html_help_text'          => __('If yes, then when non-logged in users register for this event, a user will automatically be created.',
                                    'event_espresso'),
                                'default'                 => EE_WPUsers::is_auto_user_create_on($evt_id),
                                'display_html_label_text' => true
                            )
                        ),
                        'spacing2'                 => new EE_Form_Section_HTML('<br>'),
                        'default_user_create_role' => new EE_Select_Input(
                            $wp_roles->get_names(),
                            array(
                                'html_label_text'         => __('Default role for auto-created users:',
                                    'event_espresso'),
                                'html_help_text'          => __('When users are auto-created, what default role do you want them to have?',
                                    'event_espresso'),
                                'default'                 => EE_WPUsers::default_user_create_role($evt_id),
                                'display_html_label_text' => true
                            )
                        ),
                    )
                )
            )
        );
    }
    
    /**
     * Callback for FHEE__EED_WP_Users_Admin__event_editor_metabox__wp_user_form_content.
     * Limit the Default role for auto-created users option to roles with manage_options cap.
     *
     * @param array $array of meta_box subsections.
     *
     * @return array
     */
    public static function set_capability_default_user_create_role_event_editor($array)
    {
        if ( ! current_user_can('manage_options')) {
            unset($array['default_user_create_role']);
        }
        
        return $array;
    }
    
    /**
     * callback for FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks
     * Set the callback for updating wp_user_settings on event update.
     *
     * @param  array $callbacks existing array of callbacks.
     */
    public static function set_callback_save_wp_user_event_setting($callbacks)
    {
        $callbacks[] = array('EED_WP_Users_Admin', 'save_wp_user_event_setting');
        
        return $callbacks;
    }
    
    
    /**
     * Callback for FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks.
     * Saving WP_User event specific settings when events updated.
     *
     * @param EE_Event $event
     * @param array    $req_data request data.
     *
     * @return bool   true success, false fail.
     */
    public static function save_wp_user_event_setting(EE_Event $event, $req_data)
    {
        try {
            $form = self::_get_event_editor_wp_users_form($event);
            if ($form->was_submitted()) {
                $form->receive_form_submission();
                
                if ($form->is_valid()) {
                    $valid_data = $form->valid_data();
                    EE_WPUsers::update_event_force_login($event, $valid_data['force_login']);
                    EE_WPUsers::update_auto_create_user($event, $valid_data['auto_user_create']);
                    if ( current_user_can( 'manage_options' ) ) {
                        EE_WPUsers::update_default_wp_user_role($event, $valid_data['default_user_create_role']);
                    }
                }
            } else {
                if ($form->submission_error_message() != '') {
                    EE_Error::add_error($form->submission_error_message(), __FILE__, __FUNCTION__, __LINE__);
                    
                    return false;
                }
            }
        } catch (EE_Error $e) {
            $e->get_error();
        }
        
        EE_Error::add_success(__('User Integration Event Settings updated.', 'event_espresso'));
        
        return true;
    }
    
    
    /**
     * Callback for FHEE__EE_Admin_Page___publish_post_box__box_label.
     * Used to change the label to something more descriptive for the WP_Users settings page.
     *
     * @param string        $box_label original label
     * @param string        $route     The route (used to target the specific box)
     * @param EE_Admin_Page $admin_page
     *
     * @return string        New label
     */
    public static function modify_settings_publish_box_label($box_label, $route, EE_Admin_Page $admin_page)
    {
        if ($route == 'wp_user_settings') {
            $box_label = __('Update Settings', 'event_espresso');
        }
        
        return $box_label;
    }
    
    
    /**
     * Callback for AHEE__event_tickets_datetime_ticket_row_template__advanced_details_end.
     * This is used to add the form to the tickets for the capabilities.
     *
     * @since 1.0.0
     *
     * @param string|int $tkt_row                  This will either be the ticket row number for an existing ticket or
     *                                             'TICKETNUM' for ticket skeleton.
     * @param int        $TKT_ID                   The id for a Ticket or 0 (which is not for any ticket)
     *
     * @return string form for capabilities required.
     */
    public static function insert_ticket_meta_interface($tkt_row, $TKT_ID)
    {
        //build our form and print.
        echo self::_get_ticket_capability_required_form($tkt_row, $TKT_ID)->get_html_and_js();
    }
    
    
    /**
     * Form generator for capability field on tickets.
     *
     * @since 1.0.0
     * @see   EED_WP_Users_Admin::insert_ticket_meta_interface for params documentation
     *
     * @return string
     */
    protected static function _get_ticket_capability_required_form($tkt_row, $TKT_ID)
    {
        $ticket      = EE_Registry::instance()->load_model('Ticket')->get_one_by_ID($TKT_ID);
        $current_cap = $ticket instanceof EE_Ticket ? $ticket->get_extra_meta('ee_ticket_cap_required', true, '') : '';
        
        EE_Registry::instance()->load_helper('HTML');
        
        return new EE_Form_Section_Proper(
            array(
                'name'            => 'wp-user-ticket-capability-container-' . $tkt_row,
                'html_class'      => 'wp-user-ticket-capability-container',
                'layout_strategy' => new EE_Div_Per_Section_Layout(),
                'subsections'     => apply_filters('FHEE__EED_WP_Users_Admin___get_ticket_capability_required_form__form_subsections',
                    array(
                        'ticket_capability_hdr-' . $tkt_row => new EE_Form_Section_HTML(EEH_HTML::h5(__('Ticket Capability Requirement',
                                'event_espresso') . EEH_Template::get_help_tab_link('ticket_capability_info'), '',
                            'tickets-heading')),
                        'TKT_capability'                    => new EE_Text_Input(
                            array(
                                'html_class'              => 'TKT-capability',
                                'html_name'               => 'wp_user_ticket_capability_input[' . $tkt_row . '][TKT_capability]',
                                'html_label_text'         => __('WP User Capability required for purchasing this ticket:',
                                    'event_espresso'),
                                'default'                 => $current_cap,
                                'display_html_label_text' => true
                            )
                        )
                    ) // end EE_Form_Section_Proper subsections
                ) // end subsections apply_filters
            ) //end  main EE_Form_Section_Proper options array
        ); //end EE_Form_Section_Proper
    }
    
    
    /**
     * Callback for AHEE__espresso_events_Pricing_Hooks___update_tkts_new_ticket and
     * AHEE__espresso_events_Pricing_Hooks___update_tkts_update_ticket.
     * Used to hook into ticket saves so that we update any capability requirement set for a ticket.
     *
     * @param EE_Ticket         $tkt
     * @param int               $tkt_row         The ticket row this ticket corresponds with (used for knowing
     *                                           what form element to retrieve from).
     * @param array | EE_Ticket $tkt_form_data   The original incoming ticket form data OR the original created
     *                EE_Ticket from that form data depending on which hook this callback is called on.
     * @param array             $all_form_data   All incoming form data for ticket editor (includes datetime data)
     *
     * @return void      This is an action callback so returns are ignored.
     */
    public static function update_capability_on_ticket(EE_Ticket $tkt, $tkt_row, $tkt_form_data, $all_form_data)
    {
        try {
            $ticket_id = $tkt_form_data instanceof EE_Ticket ? $tkt_form_data->ID() : $tkt->ID();
            $form      = self::_get_ticket_capability_required_form($tkt_row, $ticket_id);
            if ($form->was_submitted()) {
                $form->receive_form_submission();
                if ($form->is_valid()) {
                    $valid_data = $form->valid_data();
                    $tkt->update_extra_meta('ee_ticket_cap_required', $valid_data['TKT_capability']);
                }
            }
        } catch (EE_Error $e) {
            $e->get_error();
        }
    }
    
    
    /**
     * Callback for AHEE__EE_Base__delete__before to handle ensuring any relations WP_UserIntegration has set up with
     * the EE_Base_Class child object is handled when the object is permanently deleted.
     *
     * @param EE_Base_Class $model_object
     */
    public static function remove_relations_on_delete(EE_Base_Class $model_object)
    {
        if ($model_object instanceof EE_Event) {
            delete_post_meta($model_object->ID(), 'ee_wpuser_integration_settings');
        }
        
        if ($model_object instanceof EE_Ticket) {
            $model_object->delete_extra_meta('ee_ticket_cap_required');
        }
    }
    
} //end EED_WP_Users_Admin
