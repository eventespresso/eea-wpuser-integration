<?php
/**
 * Template for the "simple_list_table" loop template for the [ESPRESSO_MY_EVENTS] shortcode
 *
 * Available template args:
 * @type    string  $object_type  The type of object for objects in the 'object' array. It's expected for this template
 *                                that the type is 'Registration'
 * @type    EE_Registration[] $objects
 * @type    int     $object_count       Total count of all objects
 * @type    string  $your_events_title  The default label for the Events section
 * @type    string  $your_tickets_title The default label for the Tickets section
 * @type    string  $template_slug      The slug for the template.  For this template it will be 'simple_list_table'
 * @type    int     $per_page           What items are shown per page
 * @type    string  $template_path      The full path to this template
 * @type    int     $page               What the current page is (for the paging html).
 * @type    string  $with_wrapper       Whether to include the wrapper containers or not.
 */
$url = EES_Espresso_My_Events::get_current_page();
$pagination_html = EEH_Template::get_paging_html(
	$object_count,
	$page,
	$per_page,
	$url,
	false,
	'ee_mye_page',
	array(
		'single' => __( 'event', 'event_espresso' ),
		'plural' => __( 'events', 'event_espresso' )
	));
?>
<?php if ( $with_wrapper ) : ?>
<div class="espresso-my-events <?php echo $template_slug;?>_container">
	<?php do_action( 'AHEE__loop-espresso_my_events__before', $object_type, $objects, $template_slug ); ?>
	<h3><?php echo $your_events_title; ?></h3>
	<div class="espresso-my-events-inner-content">
<?php endif; //$with_wrapper check ?>
		<?php if ( $objects && reset( $objects ) instanceof EE_Registration ) : ?>
		<table class="espresso-my-events-table <?php echo $template_slug;?>_table">
			<thead>
				<tr>
					<th scope="col" class="espresso-my-events-reg-status ee-status-strip">
					</th>
					<th scope="col" class="espresso-my-events-event-th">
						<?php echo apply_filters(
							'FHEE__loop-espresso_my_events__table_header_event',
							esc_html__( 'Event', 'event_espresso' ),
							$object_type,
							$objects,
							$template_slug
						); ?>
					</th>
					<th scope="col" class="espresso-my-events-ticket-th">
						<?php echo apply_filters(
							'FHEE__loop-espresso_my_events__table_header_ticket',
							esc_html__( 'Ticket', 'event_espresso' ),
							$object_type,
							$objects,
							$template_slug
						); ?>
					</th>
					<th scope="col" class="espresso-my-events-location-th">
						<?php echo apply_filters(
							'FHEE__loop-espresso_my_events__location_table_header',
							esc_html__( 'Location', 'event_espresso' ),
							$object_type,
							$objects,
							$template_slug
						); ?>
					</th>
					<th scope="col" class="espresso-my-events-actions-th">
						<?php echo apply_filters(
							'FHEE__loop-espresso_my_events__actions_table_header',
							esc_html__( 'Actions', 'event_espresso' ),
							$object_type,
							$objects,
							$template_slug
						); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $objects as $object ) :
					if ( ! $object instanceof EE_Registration ) {
						continue;
					}
					$template_args = array( 'registration' => $object );
					$template = EE_WPUSERS_TEMPLATE_PATH . 'content-espresso_my_events-simple_list_table.template.php';
					EEH_Template::locate_template( $template, $template_args, true, false );
					?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="espresso-my-events-footer">
			<div class="espresso-my-events-pagination-container <?php echo $template_slug;?>-pagination">
				<span class="spinner"></span>
				<?php echo $pagination_html; ?>
				<div style="clear:both"></div>
			</div>
			<div style="clear:both"></div>
			<?php EEH_Template::locate_template( EE_WPUSERS_TEMPLATE_PATH . 'status-legend-espresso_my_events.template.php', array(), true, false ); ?>
		</div>
		<?php else : ?>
			<div class="no-events-container">
				<p><?php echo apply_filters(
					         'FHEE__loop-espresso_my_events__no_events_message',
							 esc_html__( 'You have no events yet', 'event_espresso' ),
				             $object_type,
				             $objects,
				             $template_slug
				         ); ?>
		         </p>
			</div>
		<?php endif; ?>
	</div>
<?php if ( $with_wrapper ) : ?>
	<?php do_action( 'AHEE__loop-espresso_my_events__after', $object_type, $objects, $template_slug ); ?>
</div>
<?php endif; //end $wrapper check?>