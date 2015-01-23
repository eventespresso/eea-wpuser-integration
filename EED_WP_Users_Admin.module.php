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
		add_action( 'profile_update', array( 'EED_WP_Users_Admin', 'sync_with_contact' ), 10, 2 );
		add_action( 'user_register', array( 'EED_WP_Users_Admin', 'sync_with_contact') );
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





	/**
	 * Callback for the 'profile_update' and 'user_register' hooks that enable syncing saved user data
	 * with an EE_Attendee record.
	 * This callback detects whether we're creating a user record or not.
	 * If creating:
	 * 	- Is there already an EE_Contact that matches the first name/last name/email address of
	 * 	the user record?
	 * 	- Yes -> attach it.
	 * 	- No -> create it.
	 *
	 * If updating:
	 * 	- Is there already an attached EE_Contact record on the user account?
	 * 	- Yes -> update it.
	 * 	- No -> do the same as when we create user.
	 *
	 *
	 * @since 1.0.0
	 * @param int      $user_id       The id of the user that was just created/updated.
	 * @param obj|null $old_user_data Object container user's data prior to update.  If empty, then
	 *                                		         the user_register hook was fired.
	 *
	 * @return void
	 */
	public static function sync_with_contact( $user_id, $old_user_data = null ) {
		$user = get_userdata( $user_id );

		//creating?
		if ( empty( $old_user_data ) ) {
			self::_connect_wp_user_with_contact( $user );
			return;
		}

		//if we make it here then we're updating an existing user
		$att_id = get_user_meta( $user->ID, 'EE_Attendee_ID', true );

		if ( empty( $att_id ) ) {
			self::_connect_wp_user_with_contact( $user );
			return;
		} else {
			//update the existing attendee attached to the wp_user!
			$att = EE_Registry::instance()->load_model('Attendee')->get_one_by_ID( $att_id );
			if ( $att instanceof EE_Attendee ) {
				$att->set_email( $user->user_email );
				$att->set_fname( $user->first_name );
				$att->set_lname( $user->last_name );
				$att->save();
			}
		}
		return;
	}





	/**
	 * This takes an incoming wp_user object and either connects it with an existing contact that
	 * matches its details, or creates a new attendee and attaches.
	 *
	 * @since 1.0.0
	 * @param WP_User $user
	 *
	 * @return EE_Attendee
	 */
	protected static function _connect_wp_user_with_contact( WP_User $user ) {
		//no attached EE_Attendee. Is there an existing attendee that matches this user's details?
		$att = self::_find_existing_attendee_from_wpuser( $user );
		if ( $att instanceof EE_Attendee ) {
			update_user_meta( $user->ID, 'EE_Attendee_ID', $existing_attendee->ID() );
		} else {
			$att = self::_create_attendee_and_attach_wp_user( $user );
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
	protected static function _find_existing_attendee_from_wpuser( WP_User $user ) {
		$existing_attendee = EE_Registry::instance()->load_model( 'Attendee' )->find_existing_attendee( array(
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
	 * @param WP_User $user
	 *
	 * @return EE_Attendee
	 */
	protected static function _create_attendee_and_attach_wpuser( WP_User $user ) {
		$att = EE_Attendee::new_instance( array(
			'ATT_fname' => $user->first_name,
			'ATT_lname' => $user->last_name,
			'ATT_email' => $user->user_email
			));
		$att->save();

		//attach to user
		update_user_meta( $user->ID, 'EE_Attendee_ID', $att->ID() );
		return $att;
	}

} //end EED_WP_Users_Admin
