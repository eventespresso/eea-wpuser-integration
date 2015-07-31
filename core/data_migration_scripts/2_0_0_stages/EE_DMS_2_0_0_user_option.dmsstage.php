<?php
if ( !defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 *
 * Data Migration Stage for converting WP User meta 'EE_Attendee_ID' to be wp user options.
 *
 * @package			WP User Integration Addon
 * @subpackage      data migrations
 * @since           2.0.0
 * @author			Darren Ethier
 *
 */
class EE_DMS_2_0_0_user_option extends EE_Data_Migration_Script_Stage_Table {

	protected $_wp_user_meta_table;

	function __construct(){
		/** @type WPDB $wpdb */
		global $wpdb;
		$this->_pretty_name = __( 'Moving EE_Attendee_ID records to user_option.', 'event_espresso' );
		// define tables
		$this->_old_table 					= $wpdb->usermeta;
		// build SQL WHERE clauses
		$this->_extra_where_sql = "WHERE meta_key LIKE '%EE_Attendee_ID'";
		parent::__construct();
	}



	/**
	 * @param array $user_meta
	 * @return void
	 */
	protected function _migrate_old_row( $user_meta ) {
		/** @type WPDB $wpdb */
		global $wpdb;
		$attid = absint( $user_meta[ 'meta_value' ] );
		$userid = absint( $user_meta['user_id'] );

		//check for valid attid
		if ( ! $attid ) {
			$this->add_error(
				sprintf(
					__( 'Invalid saved Attendee ID with value of=%1$d. Error: "%2$s"', 'event_espresso' ),
					$attid,
					$wpdb->last_error
				)
			);
			return;
		}

		//check for valid userid
		if ( ! $userid ) {
			$this->add_error(
				sprintf(
					__( 'Invalid user ID with value of=%1$d. Error: "%2$s"', 'event_espresso' ),
					$userid,
					$wpdb->last_error
				)
			);
			return;
		}

		//first transfer to user_option
		update_user_option( $userid, 'EE_Attendee_ID', $attid );

		//next delete the old meta
		delete_user_meta( $userid, 'EE_Attendee_ID' );
	}

} //end of EE_DMS_2_0_0_user_option.dmsstage.php