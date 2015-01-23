<?php
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * This file contains the module for the EE WP Users addon ee admin integration
 *
 * @since 1.0.0
 * @package  EE WP Users
 * @subpackage modules, admin
 */
/**
 *
 * EED_WP_Users_Adminmodule.  Takes care of WP Users integration with EE admin.
 *
 * @since 1.0.0
 *
 * @package		EE WP Users
 * @subpackage	modules, admin
 * @author 		Darren Ethier
 *
 * ------------------------------------------------------------------------
 */
class EED_WP_Users_Admin  extends EED_Module {


	public static function set_hooks() {}
	public static function set_hooks_admin() {
		//hook into EE contact publish metabox.
		add_action( 'post_submitbox_misc_actions', array( 'EED_WP_Users_Admin', 'add_link_to_wp_user_account' ) );

		//hook into wp users
		add_action( 'edit_user_profile', array( 'EED_WP_Users_Admin', 'add_link_to_ee_contact_details') );
	}
	public static function enqueue_scripts_styles() {}
	public function run( $WP ) {}




	/**
	 * Callback for post_submitbox_misc_actions that adds a link to the wp user
	 * edit page for the user attached to the EE_Attendee (if present).
	 *
	 * @since 1.0.0
	 */
	public static function add_link_to_wp_user_account() {
		global $post;
		if ( ! $post instanceof WP_Post || $post->post_type != 'espresso_attendees' ) {
			return;
		}

		//is there an attached wp_user for this attendee record?
		$user_id = EE_WPUsers::get_attendee_user( $post->ID );

		if ( empty( $user_id ) ) {
			return;
		}


		//let's get the WP_user and setup the link
		$url = get_edit_user_link( $user_id );

		//if $url is empty, that means logged in user does not have access to view user details so we bail.
		if ( empty( $url ) ) {
			return;
		}

		//we HAVE url so let's assemble the item to display.
		?>
		<div class="misc-pub-section">
			<span class="dashicons dashicons-universal-access ee-icon-color-grey ee-icon-size-20"></span>
			<a href="<?php echo $url; ?>" title="<?php _e('Click to view WordPress user profile', 'event_espresso'); ?>"><?php _e('WordPress User Profile', 'event_espresso'); ?></a>
		</div>
		<?php
	}



	/**
	 * callback for edit_user_profile that is used to add link to the EE_Attendee
	 * details if there is one attached to the user.
	 *
	 * @param WP_User $user
	 */
	public static function add_link_to_ee_contact_details( $user ) {
		if ( ! $user instanceof WP_User ) {
			return;
		}

		//is there an attached EE_Attendee?
		$att_id = get_user_meta( $user->ID, 'EE_Attendee_ID', true );

		if ( empty( $att_id ) ) {
			return; //bail, no attached attendee_id.
		}

		//does logged in user have the capability to edit this attendee?
		if ( ! EE_Registry::instance()->CAP->current_user_can( 'ee_edit_contacts', 'edit_attendee', $att_id ) )  {
			return; //bail no access.
		}

		//url
		$url = admin_url( add_query_arg( array(
			'page' => 'espresso_registrations',
			'action' => 'edit_attendee',
			'post' => $att_id
			), 'admin.php' ) );
		?>
		<table class="form-table">
			<tr class="ee-wpuser-integration-row">
				<th></th>
				<td>
					<p><?php _e('When you save this user profile, the details will be synced with the attached Event Espresso Contact', 'event_espresso' ); ?></p>
					<p><a class="button button-secondary" href="<?php echo $url; ?>" title="<?php _e('Click to go to Attendee Details', 'event_espresso'); ?>"><?php _e('View Linked Contact', 'event_espresso'); ?></a></p>
				</td>
			</tr>
		</table>
		<?php
	}

} //end EED_WP_Users_Admin
