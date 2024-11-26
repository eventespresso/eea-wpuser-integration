<?php

/**
 * Template for the "status legend" box for the [ESPRESSO_MY_EVENTS] shortcode output.
 * Available template args:
 *
 * @type    string $template_slug The slug for the called template. eg. 'simple_list_table', or 'event_section'.
 */

$reg_statuses = EEM_Registration::reg_status_array(array(), true);
$per_col      = 5;
$count        = 1;


$approved_reg_status = EEM_Registration::status_id_approved;
$awaiting_review_reg_status = EEM_Registration::status_id_not_approved;
$cancelled_reg_status = EEM_Registration::status_id_cancelled;
$declined_reg_status = EEM_Registration::status_id_declined;
$incomplete_reg_status = EEM_Registration::status_id_incomplete;
$pending_payment_reg_status = EEM_Registration::status_id_pending_payment;
if (class_exists('EventEspresso\core\domain\services\registration\RegStatus')) {
    $approved_reg_status = \EventEspresso\core\domain\services\registration\RegStatus::APPROVED;
    $awaiting_review_reg_status = \EventEspresso\core\domain\services\registration\RegStatus::AWAITING_REVIEW;
    $cancelled_reg_status = \EventEspresso\core\domain\services\registration\RegStatus::CANCELLED;
    $declined_reg_status = \EventEspresso\core\domain\services\registration\RegStatus::DECLINED;
    $incomplete_reg_status = \EventEspresso\core\domain\services\registration\RegStatus::INCOMPLETE;
    $pending_payment_reg_status = \EventEspresso\core\domain\services\registration\RegStatus::PENDING_PAYMENT;
}

// let's set up the legend items
$items = array();
foreach ($reg_statuses as $status_code => $status_label) {
    if ($template_slug == 'event_section') {
        // include event statuses
        switch ($status_code) {
            case $pending_payment_reg_status:
                $event_status = EEH_Template::pretty_status(EE_Datetime::upcoming, false, 'sentence');
                break;
            case $cancelled_reg_status:
                $event_status = EEH_Template::pretty_status(EE_Datetime::expired, false, 'sentence');
                break;
            case $declined_reg_status:
                $event_status = EEH_Template::pretty_status(EE_Datetime::cancelled, false, 'sentence');
                break;
            case $approved_reg_status:
                $event_status = EEH_Template::pretty_status(EE_Datetime::active, false, 'sentence');
                break;
            case $incomplete_reg_status:
                $event_status = EEH_Template::pretty_status(EE_Datetime::sold_out, false, 'sentence');
                break;
            case $awaiting_review_reg_status:
                $event_status = '';
                break;
        }

        $status_label = $event_status
            ? sprintf(
                esc_html__('%s Registration, %s Event', 'event_espresso'),
                $status_label,
                $event_status
            )
            : sprintf(esc_html__('%s Registration', 'event_espresso'), $status_label);
    }
    $items[ $status_code ] = array(
        'class' => 'ee-status-legend-box ee-status-' . $status_code,
        'desc'  => $status_label,
    );
}

if ($template_slug == 'event_section') {
    // add additional event status labels
    $items[ EE_Datetime::inactive ]  = array(
        'class' => 'ee-status-legend-box ee-status-' . EE_Datetime::inactive,
        'desc'  => sprintf(
            esc_html__('%s Event', 'event_espresso'),
            EEH_Template::pretty_status(EE_Datetime::inactive, false, 'sentence')
        ),
    );
    $items[ EE_Datetime::postponed ] = array(
        'class' => 'ee-status-legend-box ee-status-' . EE_Datetime::postponed,
        'desc'  => sprintf(
            esc_html__('%s Event', 'event_espresso'),
            EEH_Template::pretty_status(EE_Datetime::postponed, false, 'sentence')
        ),
    );
}

// add action icons
$items['edit_registration']   = array(
    'class' => 'dashicons dashicons-groups',
    'desc'  => esc_html__('Edit the registration details.', 'event_espresso'),
);
$items['resend_notification'] = array(
    'class' => 'dashicons dashicons-email-alt',
    'desc'  => esc_html__('Resend registration notification.', 'event_espresso'),
);
$items['transaction']         = array(
    'class' => 'dashicons dashicons-cart',
    'desc'  => esc_html__('Make a payment', 'event_espresso'),
);
$items['receipt']             = array(
    'class' => 'dashicons dashicons-media-default',
    'desc'  => esc_html__('View Receipt', 'event_espresso'),
);
$items['invoice']             = array(
    'class' => 'dashicons dashicons-media-spreadsheet',
    'desc'  => esc_html__('View Invoice', 'event_espresso'),
);

// filter the legend items
$items = apply_filters('FHEE__status-legend-espresso_my_events__legend_items', $items);

?>
<div class="espresso-my-events-legend-container">
    <dl class="espresso-my-events-legend-list">
        <?php foreach ($items as $item => $details) : ?>
            <?php if ($per_col < $count) : ?>
    </dl>
    <dl class="espresso-my-events-legend-list">
                <?php $count = 1;
            endif; ?>
        <dt class="ee-legend-item-<?php echo $item; ?>">
            <?php $class = ! empty($details['class']) ? $details['class'] : 'ee-legend-no-class'; ?>
            <span class="<?php echo $class; ?>"></span>
            <span class="ee-legend-description"><?php echo $details['desc']; ?></span>
        </dt>
            <?php $count++;
        endforeach; ?>
    </dl>
</div>
