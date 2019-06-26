<?php
/**
 * Template for the "event_section" content template for each ticket/registration row via [ESPRESSO_MY_EVENTS] shortcode
 * Available template args:
 *
 * @type $registration EE_Registration registration object
 */
$ticket = $registration->ticket();
?>
<tr>
    <td class="ee-status-strip reg-status-<?php echo $registration->status_ID(); ?>"></td>
    <td>
        <?php echo $ticket instanceof EE_Ticket ? $ticket->name() : ''; ?>
    </td>
    <td>
        <?php echo $ticket instanceof EE_Ticket ? $ticket->date_range() : ''; ?>
    </td>
    <td>
        <?php
        $actions = array();
        $link_to_edit_registration_text = esc_html__('Link to edit registration.', 'event_espresso');
        $link_to_resend_registration_message_text = esc_html__('Link to resend registration message', 'event_espresso');
        $link_to_make_payment_text = esc_html__('Link to make payment', 'event_espresso');
        $link_to_view_receipt_text = esc_html__('Link to view receipt', 'event_espresso');
        $link_to_view_invoice_text = esc_html__('Link to view invoice', 'event_espresso');
        // only show the edit registration link IF the registration has question groups.
        $actions['edit_registration'] = $registration->count_question_groups()
            ? '<a aria-label="' . $link_to_edit_registration_text
              . '" title="' . $link_to_edit_registration_text
              . '" href="' . $registration->edit_attendee_information_url() . '">'
              . '<span class="ee-icon ee-icon-user-edit ee-icon-size-16"></span></a>'
            : '';
        // resend confirmation email.
        $resend_registration_link = add_query_arg(
            array('token' => $registration->reg_url_link(), 'resend' => true),
            null
        );
        if ($registration->is_primary_registrant() ||
            (! $registration->is_primary_registrant()
             && $registration->status_ID() === EEM_Registration::status_id_approved)
        ) {
            $actions['resend_registration'] = '<a aria-label="'
                . $link_to_resend_registration_message_text
                . '" title="' . $link_to_resend_registration_message_text
                . '" href="' . $resend_registration_link . '">'
                . '<span class="dashicons dashicons-email-alt"></span></a>';
        }

        // make payment?
        if ($registration->is_primary_registrant()
            && $registration->transaction() instanceof EE_Transaction
            && $registration->transaction()->remaining()) {
            $actions['make_payment'] = '<a aria-label="' . $link_to_make_payment_text
                                       . '" title="' . $link_to_make_payment_text
                                       . '" href="' . $registration->payment_overview_url() . '">'
                                       . '<span class="dashicons dashicons-cart"></span></a>';
        }

        // receipt link?
        if ($registration->is_primary_registrant() && $registration->receipt_url()) {
            $actions['receipt'] = '<a aria-label="' . $link_to_view_receipt_text
                                  . '" title="' . $link_to_view_receipt_text
                                  . '" href="' . $registration->receipt_url() . '">'
                                  . '<span class="dashicons dashicons-media-default ee-icon-size-18"></span></a>';
        }

        // invoice link?
        if ($registration->is_primary_registrant() && $registration->invoice_url()) {
            $actions['invoice'] = '<a aria-label="' . $link_to_view_invoice_text
                                  . '" title="' . $link_to_view_invoice_text
                                  . '" href="' . $registration->invoice_url() . '">'
                                  . '<span class="dashicons dashicons-media-spreadsheet ee-icon-size-18"></span></a>';
        }

        // filter actions
        $actions = apply_filters(
            'FHEE__EES_Espresso_My_Events__actions',
            $actions,
            $registration
        );

        // ...and echo the actions!
        echo implode('&nbsp;', $actions);
        ?>
    </td>
</tr>