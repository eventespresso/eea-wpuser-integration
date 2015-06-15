<?php
/**
 * Template for the "status legend" box for the [ESPRESSO_MY_EVENTS] shortcode output.
 */
$reg_statuses = EEM_Registration::reg_status_array( array(), true );
$per_col = 5;
$count = 1;

//let's setup the legend items
$items = array();
foreach ( $reg_statuses as $status_code => $status_label ) {
	$items[$status_code] = array(
		'class' => 'ee-status-legend-box ee-status-' . $status_code,
		'desc' => $status_label
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
$items['invoice'] = array(
	'class' => 'dashicons dashicons-media-default',
	'desc' => esc_html__( 'View Receipt', 'event_espresso' )
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