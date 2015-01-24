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
	 * Default setting for whether login is required to register for an event.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $force_login;


	/**
	 * constructor
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->force_login = false;
	}

} //end EE_WPUsers_Config
