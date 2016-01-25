<?php
/**
 * Template for registration table displayed on user profile page.
 * @since 1.1.4
 * @package WP Users Integration
 * @subpackage templates
 *
 * Template args:
 * @type    EE_Attendee  $attendee
 * @type    EE_Registration[] $registrations
 */
?>
<h3><?php IS_PROFILE_PAGE ? _e( 'All your registrations', 'event_espresso' ) : _e( 'All Registrations for this user', 'event_espresso' ); ?></h3>
<div class="admin-primary-mbox-dv">
	<br/>

	<div class="admin-primary-mbox-tbl-wrap">
		<table class="admin-primary-mbox-tbl">
			<thead>
			<tr>
				<th class="jst-left"><?php esc_html_e( 'Event Name', 'event_espresso' );?></th>
				<th class="jst-left"><?php esc_html_e( 'REG ID', 'event_espresso' );?></th>
				<th class="jst-left"><?php esc_html_e( 'TXN ID', 'event_espresso' );?></th>
				<th class="jst-left"><?php esc_html_e( 'Reg Code', 'event_espresso' );?></th>
				<th class="jst-rght"><?php esc_html_e( 'Ticket Price', 'event_espresso' );?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach( $registrations as $registration ) : ?>
				<?php
					$final_price = method_exists( $registration, 'final_price' ) ? $registration->final_price() : $registration->price_paid();
				?>
				<tr>
					<td class="jst-left">
						<?php
						$event_url = add_query_arg( array( 'action' => 'edit', 'post' => $registration->event_ID() ), admin_url( 'admin.php?page=espresso_events' ));
						echo EE_Registry::instance()->CAP->current_user_can( 'ee_edit_event', 'espresso_events_edit', $registration->event_ID() ) ?  '<a href="'. $event_url .'"  title="'. esc_attr__( 'Edit Event', 'event_espresso' ) .'">' . $registration->event_name() . '</a>' : $registration->event_name();
						?>
					</td>
					<td class="jst-left">
						<?php
						$reg_url = EE_Admin_Page::add_query_args_and_nonce( array( 'action'=>'view_registration', '_REG_ID'=>$registration->ID() ), REG_ADMIN_URL );
						echo EE_Registry::instance()->CAP->current_user_can( 'ee_read_registration', 'espresso_registrations_view_registration', $registration->ID() ) ? '
							<a href="'.$reg_url.'" title="' . esc_attr__( 'View Registration Details', 'event_espresso' ) . '">' . 
								esc_html__( 'View Registration', 'event_espresso' ) .
						    '</a>' : '<a href="' . $registration->edit_attendee_information_url() . '" target="_blank">' . __('Edit Registration', 'event_espresso' ) . '</a>';
						?>
					</td>
					<td class="jst-left">
						<?php
						$txn_url = EE_Admin_Page::add_query_args_and_nonce( array( 'action'=>'view_transaction', 'TXN_ID'=>$registration->transaction_ID() ), TXN_ADMIN_URL );
						echo EE_Registry::instance()->CAP->current_user_can( 'ee_read_transaction', 'espresso_transactions_view_transaction' ) ? '
						<a href="'.$txn_url.'" title="' . esc_attr__( 'View Transaction Details', 'event_espresso' ) . '">' .
							sprintf( esc_html__('View Transaction %d', 'event_espresso'), $registration->transaction_ID() ) . 
						'</a>' : $registration->transaction_ID();
						?>
					</td>
					<td class="jst-left"><?php echo $registration->reg_code();?></td>
					<td class="jst-rght"><?php echo EEH_Template::format_currency( $final_price );?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>