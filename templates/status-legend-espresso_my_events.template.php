<?php
/**
 * Template for the "status legend" box for the [ESPRESSO_MY_EVENTS] shortcode output.
 *
 * Available template args:
 * @type    string  $template_slug  The slug for the called template. eg. 'simple_list_table', or 'event_section'.
 *
 */
$reg_statuses = EEM_Registration::reg_status_array( array(), true );
$per_col = 5;
$count = 1;

//let's setup the legend items
$items = array();
foreach ( $reg_statuses as $status_code => $status_label ) {
	if ( $template_slug == 'event_section' ) {
		//include event statuses
		switch ( $status_code ) {
			case EEM_Registration::status_id_pending_payment :
				$event_status = EEH_Template::pretty_status( EE_Datetime::upcoming, false, 'sentence' );
				break;
			case EEM_Registration::status_id_cancelled :
				$event_status = EEH_Template::pretty_status( EE_Datetime::expired, false, 'sentence' );
				break;
			case EEM_Registration::status_id_declined :
				$event_status = EEH_Template::pretty_status( EE_Datetime::cancelled, false, 'sentence' );
				break;
			case EEM_Registration::status_id_approved :
				$event_status = EEH_Template::pretty_status( EE_Datetime::active, false, 'sentence' );
				break;
			case EEM_Registration::status_id_incomplete :
				$event_status = EEH_Template::pretty_status( EE_Datetime::sold_out, false, 'sentence' );
				break;
			case EEM_Registration::status_id_not_approved :
				$event_status = '';
				break;
		}

		$status_label = $event_status ? sprintf( esc_html__( '%s Registration, %s Event', 'event_espresso' ), $status_label, $event_status ) : sprintf( esc_html__( '%s Registration', 'event_espresso' ), $status_label );
	}
	$items[$status_code] = array(
		'class' => 'ee-status-legend-box ee-status-' . $status_code,
		'desc' => $status_label
	);
}

if ( $template_slug == 'event_section' ) {
	//add additional event status labels
	$items[EE_Datetime::inactive] = array(
		'class' => 'ee-status-legend-box ee-status-' . EE_Datetime::inactive,
		'desc' => sprintf( esc_html__( '%s Event', 'event_espresso' ), EEH_Template::pretty_status( EE_Datetime::inactive, false, 'sentence' ) )
		);
	$items[EE_Datetime::postponed] = array(
		'class' => 'ee-status-legend-box ee-status-' . EE_Datetime::postponed,
		'desc' => sprintf( esc_html__( '%s Event', 'event_espresso' ), EEH_Template::pretty_status( EE_Datetime::postponed, false, 'sentence' ) )
	);
}

//add action icons
$items['edit_registration'] = array(
	'class' => 'ee-icon ee-icon-user-edit',
	'desc' => esc_html__('Edit the registration details.', 'event_espresso' )
);
$items['resend_notification'] = array(
	'class' => 'dashicons dashicons-email-alt',
	'desc' => esc_html__( 'Resend registration notification.', 'event_espresso' )
);
$items['transaction'] = array(
	'class' => 'dashicons dashicons-cart',
	'desc' => esc_html__( 'Make a payment', 'event_espresso' )
);
$items['receipt'] = array(
	'class' => 'dashicons dashicons-media-default',
	'desc' => esc_html__( 'View Receipt', 'event_espresso' )
);
$items['invoice'] = array(
	'class' => 'dashicons dashicons-media-spreadsheet',
	'desc' => esc_html__( 'View Invoice', 'event_espresso' )
);

//filter the legend items
$items = apply_filters( 'FHEE__status-legend-espresso_my_events__legend_items', $items );

?>
<div class="espresso-my-events-legend-container">
	<dl class="espresso-my-events-legend-list">
		<?php foreach ( $items as $item => $details ) : ?>
		<?php if ( $per_col < $count ) : ?>
			</dl>
			<dl class="espresso-my-events-legend-list">
		<?php $count = 1; endif; ?>
		<dt class="ee-legend-item-<?php echo $item; ?>">
				<?php $class = !empty($details['class']) ? $details['class'] : 'ee-legend-no-class'; ?>
				<span class="<?php echo $class; ?>"></span>
				<span class="ee-legend-description"><?php echo $details['desc']; ?></span>
		</dt>
		<?php $count++; endforeach; ?>
	</dl>
</div>