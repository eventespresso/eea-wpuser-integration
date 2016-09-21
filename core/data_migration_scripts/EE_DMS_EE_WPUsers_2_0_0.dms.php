<?php
/**
 * This file contains the Dat Migration Script for WP Users addon version 2.0.0
 *
 * @since 2.0.0
 * @package  EE WP Users Addon
 * @subpackage dms
 */
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 *
 * WP Users Integration Addon Data Migration Script
 *
 * @since 2.0.0
 *
 * @package		EE WP Users Addon
 * @subpackage	dms
 * @author 		Darren Ethier
 *
 * ------------------------------------------------------------------------
 */
class EE_DMS_EE_WPUsers_2_0_0 extends EE_Data_Migration_Script_Base{

	public function __construct() {
		$this->_pretty_name = __("Data Migration to WP Users Integration 2.0.0.", "event_espresso");
		$this->_pretty_name .= is_multisite() ? '<strong>' . __( ' WordPress Multisite use is detected. Please contact Event Espresso Support before migrating.', 'event_espresso' ) . '</strong>' : '';
		$this->_migration_stages = array(
			new EE_DMS_2_0_0_user_option()
		);
		parent::__construct();
	}
	/**
	 * Indicates whether or not this data migration script should migrate data or not
	 * @param array $current_database_state_of keys are EE plugin slugs like
	 *				'Core', 'Calendar', 'Mailchimp',etc, Your addon's slug can be retrieved
	 *				using $this->slug(). Your addon's entry database state is located
	 *				at $current_database_state_of[ $this->slug() ] if it was previously
	 *				installed; if it wasn't previously installed its NOT in the array
	 * @return boolean
	 */
	public function can_migrate_from_version($current_database_state_of) {
		$version_string = isset( $current_database_state_of[$this->slug()] ) ? $current_database_state_of[$this->slug()] : false;

		//let's also determine if migrations are needed by the presence of any EE_Attendee_ID
		//relations in the db.  If there are none then no migrations needed!
		/** @type WPDB */
		global $wpdb;
		$has_records = $wpdb->get_var( "SELECT COUNT('user_id') FROM $wpdb->usermeta WHERE meta_key LIKE '%EE_Attendee_ID%'");

		if ( ! $version_string || ! $has_records ) {
			return false;
		} else if ( version_compare( $version_string, '2.0.0', '<=' ) 
			&& version_compare( $version_string, '1.0.0', '>=' )
		) {
				return true;
		} else {

			return false;
		}
	}

	public function schema_changes_after_migration() {}

	public function schema_changes_before_migration() {}
}

// End of file EE_DMS_eea-people-addon_0_0_1.dms.php
