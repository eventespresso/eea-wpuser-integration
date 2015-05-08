<?php
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }
/**
 * This file contains the definition of the EE_WPUsers Config
 *
 * @since 1.0.0
 * @package EE WPUsers
 * @subpackage config
 */

/**
 * Class defining the WPUsers Config object stored on EE_Registry::instance->CFG
 *
 * @since 1.0.0
 *
 * @package EE WPUsers
 * @subpackage config
 * @author Darren Ethier
 */
class EE_WPUsers_Config extends EE_Config_Base {

	/**
	 * Global default setting for whether login is required to register for an event.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $force_login;


	/**
	 * Global setting for what gets used for the registration page url.
	 *
	 * @since 1.1.3
	 * @var
	 */
	public $registration_page;



	/**
	 * Global default setting for whether a new wp_user is created on frontend when a registration has
	 * a new attendee (with new details).
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $auto_create_user;




	/**
	 * Global default setting for what role a new wp_user is created as when auto created via frontend
	 * registration.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $default_wp_user_role;


	/**
	 * constructor
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->force_login = false;
		$this->registration_page = '';
		$this->auto_create_user = false;
		$this->default_wp_user_role = 'subscriber';
	}

} //end EE_WPUsers_Config
