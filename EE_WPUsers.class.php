<?php

use EventEspresso\core\services\routing\PrimaryRoute;
use EventEspresso\core\services\routing\RouteHandler;
use EventEspresso\WpUser\domain\Domain;

/**
 * Class definition for the EE_WPUsers object
 *
 * @since        1.0.0
 * @package      EE WPUsers
 */
class EE_WPUsers extends EE_Addon
{
    /**
     * Set up
     *
     * @throws EE_Error
     */
    public static function register_addon()
    {
        $registration_array = [
            'plugin_slug'      => Domain::LICENSE_PLUGIN_SLUG,
            'version'          => EE_WPUSERS_VERSION,
            'min_core_version' => EE_WPUSERS_MIN_CORE_VERSION_REQUIRED,
            'main_file_path'   => EE_WPUSERS_PLUGIN_FILE,
            'config_class'     => 'EE_WPUsers_Config',
            'config_name'      => 'user_integration',
            'admin_callback'   => 'additional_admin_hooks',
            'module_paths'     => [
                EE_WPUSERS_PATH . 'EED_WP_Users_SPCO.module.php',
                EE_WPUSERS_PATH . 'EED_WP_Users_Admin.module.php',
                EE_WPUSERS_PATH . 'EED_WP_Users_Ticket_Selector.module.php',
            ],
            'dms_paths'        => [EE_WPUSERS_PATH . 'core/data_migration_scripts'],
            'autoloader_paths' => [
                'EE_WPUsers_Config'              => EE_WPUSERS_PATH . 'EE_WPUsers_Config.php',
                'EE_SPCO_Reg_Step_WP_User_Login' => EE_WPUSERS_PATH . 'EE_SPCO_Reg_Step_WP_User_Login.class.php',
                'EE_DMS_2_0_0_user_option'       =>
                    EE_WPUSERS_PATH
                    . 'core/data_migration_scripts/2_0_0_stages/EE_DMS_2_0_0_user_option.dmsstage.php',
            ],
            'namespace'        => [
                'FQNS' => 'EventEspresso\WpUser',
                'DIR'  => __DIR__,
            ],
            'license' => [
                'beta'             => false,
                'main_file_path'   => EE_WPUSERS_PLUGIN_FILE,
                'min_core_version' => Domain::CORE_VERSION_REQUIRED,
                'plugin_id'        => Domain::LICENSE_PLUGIN_ID,
                'plugin_name'      => Domain::LICENSE_PLUGIN_NAME,
                'plugin_slug'      => Domain::LICENSE_PLUGIN_SLUG,
                'version'          => EE_WPUSERS_VERSION,
                'wp_override'      => false,
            ],
            'pue_options'      => [
                'pue_plugin_slug' => 'eea-wp-user-integration',
                'checkPeriod'     => '24',
                'use_wp_update'   => false,
            ],
        ];
        // the My Events Shortcode registration depends on EE version.
        if (EE_Register_Addon::_meets_min_core_version_requirement('4.9.46.rc.024')) {
            // register shortcode for new system.
            $registration_array['shortcode_fqcns'] = [
                'EventEspresso\WpUser\domain\entities\shortcodes\EspressoMyEvents',
            ];
        } else {
            // register shortcode for old system.
            $registration_array['shortcode_paths'] = [
                EE_WPUSERS_PATH . 'EES_Espresso_My_Events.shortcode.php',
            ];
        }
        // register addon via Plugin API
        EE_Register_Addon::register('EE_WPUsers', $registration_array);
    }


    /**
     * Register things that have to happen early in loading.
     */
    public function after_registration()
    {
        $this->register_dependencies();
        add_action(
            'AHEE__EventEspresso_core_services_routing_Router__brewEspresso',
            [$this, 'handleWpUserRoutes'],
            10,
            3
        );
    }


    protected function register_dependencies()
    {
        EE_Dependency_Map::register_class_loader(
            'EventEspresso\WpUser\domain\Domain',
            static function () {
                return getWpUserDomain();
            }
        );
        EE_Dependency_Map::register_dependencies(
            'EventEspresso\WpUser\domain\entities\shortcodes\EspressoMyEvents',
            [
                'EventEspresso\core\services\cache\PostRelatedCacheManager' => EE_Dependency_Map::load_from_cache,
                'EventEspresso\core\services\request\Request'               => EE_Dependency_Map::load_from_cache,
                'EEM_Event'                                                 => EE_Dependency_Map::load_from_cache,
                'EEM_Registration'                                          => EE_Dependency_Map::load_from_cache,
            ]
        );
    }


    /**
     *  additional admin hooks
     */
    public function additional_admin_hooks()
    {
        // is admin and not in M-Mode ?
        if (
            is_admin()
            && (
                class_exists('EventEspresso\core\domain\services\database\MaintenanceStatus')
                && EventEspresso\core\domain\services\database\MaintenanceStatus::isDisabled()
            ) || ! EE_Maintenance_Mode::instance()->level()
        ) {
            add_filter('plugin_action_links', [$this, 'plugin_actions'], 10, 2);
        }
    }


    /**
     * plugin_actions
     * Add a settings link to the Plugins page, so people can go straight from the plugin page to the settings page.
     *
     * @param $links
     * @param $file
     * @return array
     */
    public function plugin_actions($links, $file)
    {
        if ($file === EE_WPUSERS_BASENAME) {
            array_unshift(
                $links,
                '<a href="admin.php?page=espresso_registration_form&action=wp_user_settings">'
                . esc_html__('Settings', 'event_espresso')
                . '</a>'
            );
        }
        return $links;
    }


    /**
     * Used to get a user id for a given EE_Attendee id.
     * If none found then null is returned.
     *
     * @param int $att_id The attendee id to find a user match with.
     * @return int|null     $user_id if found otherwise null.
     */
    public static function get_attendee_user($att_id)
    {
        global $wpdb;
        $key     = $wpdb->get_blog_prefix() . 'EE_Attendee_ID';
        $query   = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '$key' AND meta_value = '%d'";
        $user_id = $wpdb->get_var($wpdb->prepare($query, (int) $att_id));
        return $user_id ? (int) $user_id : null;
    }


    /**
     * used to determine if forced login is turned on for the event or not.
     *
     * @param int|EE_Event $event Either event_id or EE_Event object.
     * @return bool true YES forced login turned on false NO forced login turned off.
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function is_event_force_login($event)
    {
        return self::_get_wp_user_event_setting('force_login', $event);
    }


    public static function is_auto_user_create_on($event)
    {
        return self::_get_wp_user_event_setting('auto_create_user', $event);
    }


    public static function default_user_create_role($event)
    {
        return self::_get_wp_user_event_setting('default_wp_user_role', $event);
    }


    /**
     * This retrieves the specific wp_user setting for an event as indicated by key.
     *
     * @param string $key           What setting are we retrieving
     * @param        $event
     * @return mixed Whatever the value for the key is or what is set as the global default if it doesn't
     *                              exist.
     * @throws EE_Error
     * @throws ReflectionException
     * @internal param EE_Event|int $EE_Event or event id
     */
    protected static function _get_wp_user_event_setting($key, $event)
    {
        // any global defaults?
        $config         = isset(EE_Registry::instance()->CFG->addons->user_integration)
            ? EE_Registry::instance()->CFG->addons->user_integration
            : false;
        $global_default = [
            'force_login'          => $config && isset($config->force_login) ? $config->force_login : false,
            'auto_create_user'     => $config && isset($config->auto_create_user) ? $config->auto_create_user : false,
            'default_wp_user_role' => $config && isset($config->default_wp_user_role)
                ? $config->default_wp_user_role
                : 'subscriber',
        ];


        $event    = $event instanceof EE_Event
            ? $event
            : EE_Registry::instance()->load_model('Event')->get_one_by_ID((int) $event);
        $settings = $event instanceof EE_Event
            ? $event->get_post_meta('ee_wpuser_integration_settings', true)
            : [];
        if (! empty($settings)) {
            $value = isset($settings[ $key ]) ? $settings[ $key ] : $global_default[ $key ];

            // since post_meta *might* return an empty string.  If the default global value is boolean, then let's make
            // sure we cast the value returned from the post_meta as boolean in case its an empty string.
            return is_bool($global_default[ $key ]) ? (bool) $value : $value;
        }
        return $global_default[ $key ];
    }


    /**
     * used to update the force login setting for an event.
     *
     * @param int|EE_Event $event       Either the EE_Event object or int.
     * @param bool         $force_login value.  If turning off you can just not send.
     * @return mixed (via downstream activity)
     * @throws EE_Error (via downstream activity)
     * @throws ReflectionException
     */
    public static function update_event_force_login($event, $force_login = false)
    {
        return self::_update_wp_user_event_setting('force_login', $event, $force_login);
    }


    /**
     * used to update the auto create user setting for an event.
     *
     * @param int|EE_Event $event       Either the EE_Event object or int.
     * @param bool         $auto_create value.  If turning off you can just not send.
     * @return mixed (via downstream activity)
     * @throws EE_Error (via downstream activity)
     * @throws ReflectionException
     */
    public static function update_auto_create_user($event, $auto_create = false)
    {
        return self::_update_wp_user_event_setting('auto_create_user', $event, $auto_create);
    }


    public static function update_default_wp_user_role($event, $default_role = 'subscriber')
    {
        return self::_update_wp_user_event_setting('default_wp_user_role', $event, $default_role);
    }


    /**
     * used to update the wp_user event specific settings.
     *
     * @param string       $key   What setting is being updated.
     * @param int|EE_Event $event Either the EE_Event object or id.
     * @param mixed        $value The value being updated.
     * @return mixed Returns meta_id if the meta doesn't exist, otherwise returns true on success
     *                            and false on failure. NOTE: If the meta_value passed to this function is the
     *                            same as the value that is already in the database, this function returns false.
     * @throws EE_Error
     * @throws ReflectionException
     */
    protected static function _update_wp_user_event_setting($key, $event, $value)
    {
        $event = $event instanceof EE_Event
            ? $event
            : EE_Registry::instance()->load_model('Event')->get_one_by_ID((int) $event);

        if (! $event instanceof EE_Event) {
            return false;
        }
        $settings         = $event->get_post_meta('ee_wpuser_integration_settings', true);
        $settings         = empty($settings) ? [] : $settings;
        $settings[ $key ] = $value;
        return $event->update_post_meta('ee_wpuser_integration_settings', $settings);
    }


    /**
     * @param RouteHandler      $router
     * @param string            $route_request_type
     * @param EE_Dependency_Map $dependency_map
     * @throws Exception
     */
    public function handleWpUserRoutes(
        RouteHandler $router,
        string $route_request_type,
        EE_Dependency_Map $dependency_map
    ) {
        if ($route_request_type === PrimaryRoute::ROUTE_REQUEST_TYPE_REGULAR) {
            $routes_and_dependencies = [
                'EventEspresso\WpUser\domain\entities\routing\EspressoEventEditor' => [
                    'EE_Admin_Config'                                      => EE_Dependency_Map::load_from_cache,
                    'EE_Dependency_Map'                                    => EE_Dependency_Map::load_from_cache,
                    'EventEspresso\core\services\loaders\LoaderInterface'  => EE_Dependency_Map::load_from_cache,
                    'EventEspresso\core\services\request\RequestInterface' => EE_Dependency_Map::load_from_cache,
                ],
                'EventEspresso\WpUser\domain\entities\routing\GQLRequests'         => [
                    'EE_Dependency_Map'                                       => EE_Dependency_Map::load_from_cache,
                    'EventEspresso\core\services\loaders\LoaderInterface'     => EE_Dependency_Map::load_from_cache,
                    'EventEspresso\core\services\request\RequestInterface'    => EE_Dependency_Map::load_from_cache,
                    'EventEspresso\core\services\assets\AssetManifestFactory' => EE_Dependency_Map::load_from_cache,
                ],
            ];
            foreach ($routes_and_dependencies as $route => $dependencies) {
                $dependency_map->registerDependencies($route, $dependencies);
                $router->addRoute($route);
            }
        }
    }
}
