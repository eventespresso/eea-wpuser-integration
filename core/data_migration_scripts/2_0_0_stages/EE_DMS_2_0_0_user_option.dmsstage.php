<?php

/**
 * Data Migration Stage for converting WP User meta 'EE_Attendee_ID' to be wp user options.
 *
 * @package         WP User Integration Addon
 * @subpackage      data migrations
 * @since           2.0.0
 * @author          Darren Ethier
 */
class EE_DMS_2_0_0_user_option extends EE_Data_Migration_Script_Stage_Table
{

    public function __construct()
    {
        /** @type WPDB $wpdb */ global $wpdb;
        $this->_pretty_name = __('Moving EE_Attendee_ID records to user_option.', 'event_espresso');
        // define tables
        $this->_old_table = $wpdb->usermeta;
        // build SQL WHERE clauses
        $this->_extra_where_sql = "WHERE meta_key LIKE '%EE_Attendee_ID'";
        parent::__construct();
    }


    /**
     * @param array $user_meta
     * @return void
     */
    protected function _migrate_old_row($user_meta)
    {
        /** @type WPDB $wpdb */ global $wpdb;
        $attid   = absint($user_meta['meta_value']);
        $userid  = absint($user_meta['user_id']);
        $add_new = true;
        // check for valid attid
        if (! $attid) {
            $this->add_error(
                sprintf(
                    __('Invalid saved Attendee ID with value of=%1$d. Error: "%2$s"', 'event_espresso'),
                    $attid,
                    $wpdb->last_error
                )
            );
            $add_new = false;
        }

        // check for valid userid
        if (! $userid) {
            $this->add_error(
                sprintf(
                    __('Invalid user ID with value of=%1$d. Error: "%2$s"', 'event_espresso'),
                    $userid,
                    $wpdb->last_error
                )
            );
            $add_new = false;
        }

        if ($add_new) {
            // first transfer to user_option
            update_user_option($userid, 'EE_Attendee_ID', $attid);
        }

        // next delete the old meta
        // even if the data was incomplete to insert a new one. We won't stop
        // until every last one of them is gone
        delete_user_meta($userid, 'EE_Attendee_ID');
    }


    /**
     * Overrides parent because we only want to stop when we are certain there
     * are no more original records left, in order to solve #8596
     *
     * @param int $num_items_to_migrate
     * @return int number of items ACTUALLY migrated
     */
    public function _migration_step($num_items_to_migrate = 50)
    {
        $rows                    = $this->_get_rows($num_items_to_migrate);
        $items_actually_migrated = 0;
        foreach ($rows as $old_row) {
            $this->_migrate_old_row($old_row);
            $items_actually_migrated++;
        }
        if (! $this->_records_remaining()) {
            $this->set_completed();
        }
        return $items_actually_migrated;
    }


    /**
     * We need an accurate count of how many more records need migration, in order to solve #8596
     *
     * @return int
     * @global wpdb $wpdb
     */
    protected function _records_remaining()
    {
        global $wpdb;
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $this->_old_table WHERE meta_key = 'EE_Attendee_ID'"
        );
    }


    /**
     * Overrides parent because we want ot make sure we fetch ALL the records
     * (because earlier code modified the original query set meaning that once
     * we got halfway through migrating this stage's data, we had also removed
     * half the data we were working on, but were using offsets and so were
     * trying to grab data from the 2nd half of the query set which was now half the size,
     * so we were getting nothing and were looping)
     *
     * @param int   $limit
     * @return array of arrays like $wpdb->get_results($sql, ARRAY_A)
     * @global wpdb $wpdbic
     */
    protected function _get_rows($limit)
    {
        global $wpdb;
        $query = "SELECT * FROM $this->_old_table WHERE meta_key = 'EE_Attendee_ID' LIMIT $limit";
        return $wpdb->get_results($query, ARRAY_A);
    }
}
