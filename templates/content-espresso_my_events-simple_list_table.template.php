<?php
/**
 * Template for the "simple_list_table" content template for the [ESPRESSO_MY_EVENTS] shortcode
 *
 * Available template args:
 * @type    $registration EE_Registration registration object
 */
?>
<tr>
	<td class="ee-status-strip reg-status-<?php echo $registration->status_ID(); ?>"></td>
	<td>
		<a aria-labelledby="<?php printf( __( 'Link to %s', 'event_espresso' ), $registration->event_name() ); ?>" href="<?php echo get_permalink( $registration->event_ID() ); ?>"><?php echo $registration->event_name(); ?></a>
	</td>
	<td>
		<?php echo $registration->ticket() instanceof EE_Ticket ? $registration->ticket()->name() : ''; ?>
	</td>
	<td>
		<?php
			$event = $registration->event();
			if ( $event instanceof EE_Event ) {
				$venues = $event->venues();
				foreach ( $venues as $venue ) {
					if ( $venue instanceof EE_Venue) {
						echo $venue->name() . '<br>';
					}
				}
			} else {
				echo '';
			}
		?>
	</td>
	<td>
		<?php
		$actions = array();
		//only show the edit registration link if the registration has question groups
		$actions['edit_registration'] = $registration->count_question_groups()
			? '<a aria-label="' . __( 'Link to edit attendee information', 'event_espresso' ) . '" href="' . $registration->edit_attendee_information_url() . '">'
			. '<span class="ee-icon ee-icon-user-edit ee-icon-size-16"></span></a>'
			: '';
		//resend confirmation email.
		$resend_registration_link = add_query_arg(
			array( 'token' => $registration->reg_url_link(), 'resend' => true ),
			get_permalink( EE_Registry::instance()->REQ->get_post_id_from_request() )
		);
		if ( $registration->is_primary_registrant() ||
		     ( ! $registration->is_primary_registrant()
		       && $registration->status_ID() == EEM_Registration::status_id_approved ) ) {

			$actions['resend_registration'] = '<a aria-label="' . __( 'Link to resend registration message', 'event_espresso' ) . '" href="' . $resend_registration_link . '">'
				. '<span class="dashicons dashicons-email-alt"></span></a>';
		}

		//make payment?
		if ( $registration->is_primary_registrant()
		     && $registration->transaction() instanceof EE_Transaction
			 && $registration->transaction()->remaining() ) {
			$actions['make_payment'] = '<a aria-label="' . __( 'Link to make payment' , 'event_espresso' ) . '" href="' . $registration->payment_overview_url() . '">'
				. '<span class="dashicons dashicons-cart"></span></a>';
		}

		//receipt link?
		if ( $registration->is_primary_registrant() && $registration->receipt_url() ) {
			$actions['receipt'] = '<a aria-label="' . __( 'Link to view receipt', 'event_espresso' ) . '" href="' . $registration->receipt_url() . '">'
				. '<span class="dashicons dashicons-media-default ee-icon-size-18"></span></a>';
		}

		//invoice link?
		if ( $registration->is_primary_registrant() && $registration->invoice_url() ) {
			$actions['invoice'] = '<a aria-label="' . __( 'Link to view invoice', 'event_espresso' ) . '" href="' . $registration->invoice_url() . '">'
			                      . '<span class="dashicons dashicons-media-spreadsheet ee-icon-size-18"></span></a>';
		}

		//filter actions
		$actions = apply_filters( 'FHEE__EES_Espresso_My_Events__actions',
			$actions,
			$registration
		);

		//...and echo the actions!
		echo implode( '&nbsp;', $actions );
		?>
	</td>
</tr>