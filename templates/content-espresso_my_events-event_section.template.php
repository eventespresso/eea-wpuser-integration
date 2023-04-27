<?php

use EventEspresso\core\services\request\sanitizers\AllowedTags;

/**
 * Template for the "event_section" content template for the [ESPRESSO_MY_EVENTS] shortcode
 * Available template args:
 *
 * @type    EE_Event $event              event object
 * @type    string   $your_tickets_title title for the ticket.
 * @type    int      $att_id             The id of the EE_Attendee related to the displayed data.
 */

EE_Registry::instance()->load_Helper('Venue_View');

$registrations = $event->get_many_related('Registration', [['ATT_ID' => $att_id]]);
$venues        = $event->venues();
$venue_content = [];
?>
<tr class="ee-my-events-event-section-summary-row">
    <td class="ee-status-strip event-status-<?php echo esc_attr($event->get_active_status()); ?>"></td>
    <td>
        <a aria-label="<?php printf(esc_html__('Link to %s', 'event_espresso'), $event->name()); ?>"
           href="<?php echo esc_url_raw(get_permalink($event->ID())); ?>"
        >
            <?php echo esc_html($event->name()); ?>
        </a>
    </td>
    <td>
        <?php
        foreach ($venues as $venue) :
            $venue_content[] = '
            <a aria-label="' . esc_attr(sprintf(__('Link to %s', 'event_espresso'), $venue->name())) . '"
                href="' . esc_url_raw(get_permalink($venue->ID())) . '">
                ' . esc_html($venue->name()) . '
            </a>';
        endforeach;
        echo wp_kses(implode('<br>', $venue_content), AllowedTags::getAllowedTags());
        ?>
    </td>
    <td>
        <?php espresso_event_date_range('', '', '', '', $event->ID()); ?>
    </td>
    <td>
        <?php echo absint(count($registrations)); ?>
    </td>
    <td>
        <span class="dashicons dashicons-admin-generic js-ee-my-events-toggle-details"></span>
    </td>
</tr>
<tr class="ee-my-events-event-section-details-row">
    <td colspan="6">
        <div class="ee-my-events-event-section-details-inner-container">
            <section class="ee-my-events-event-section-details-event-description">
                <div class="ee-my-events-right-container">
                    <span class="dashicons dashicons-admin-generic js-ee-my-events-toggle-details"></span>
                </div>
                <h3><?php echo esc_html($event->name()); ?></h3>
                <?php
                /**
                 * There is a ticket for EE core: https://events.codebasehq.com/projects/event-espresso/tickets/8405
                 * that hopefully will remove the necessity for the apply_filters() here.
                 */

                ?>
                <?php echo esc_html(apply_filters('the_content', $event->description())); ?>
            </section>
            <?php
            /**
             * For now this will just grab the first venue related to the event.  However when we move to multiple
             * venues per event and/or datetime, this could be modified to have the map show all venues and then list
             * them in the right section
             */
            $venue = reset($venues);
            if ($venue instanceof EE_Venue) :
                ?>
                <section class="ee-my-events-event-section-location-map ee-my-events-one-third">
                    <?php
                    echo wp_kses(
                        EEH_Venue_View::espresso_google_static_map($venue),
                        AllowedTags::getAllowedTags()
                    );
                    ?>
                </section>
                <section class="ee-my-events-event-section-location-details ee-my-events-two-thirds">
                    <strong><?php echo esc_html($venue->name()); ?></strong>
                    <?php echo wp_kses(
                        EEH_Venue_View::venue_address('multiline', $venue->ID()),
                        AllowedTags::getAllowedTags()
                    ); ?>
                </section>
                <div style="clear:both"></div>
            <?php endif; // end venue check ?>
            <section class="ee-my-events-event-section-tickets-list-table-container">
                <h3><?php echo wp_kses($your_tickets_title, AllowedTags::getAllowedTags()); ?></h3>
                <?php if ($registrations) : ?>
                    <table class="espresso-my-events-table simple-list-table">
                        <thead>
                            <tr>
                                <th scope="col" class="espresso-my-events-reg-status ee-status-strip">
                                </th>
                                <th scope="col" class="espresso-my-events-ticket-th">
                                    <?php echo esc_html(
                                        apply_filters(
                                            'FHEE__content-espresso_my_events__table_header_ticket',
                                            __('Ticket', 'event_espresso'),
                                            $event
                                        )
                                    ); ?>
                                </th>
                                <th scope="col" class="espresso-my-events-datetimes-th">
                                    <?php echo esc_html(
                                        apply_filters(
                                            'FHEE__content-espresso_my_events__table_header_datetimes',
                                            __('Dates & Times', 'event_espresso'),
                                            $event
                                        )
                                    ); ?>
                                </th>
                                <th scope="col" class="espresso-my-events-actions-th">
                                    <?php echo esc_html(
                                        apply_filters(
                                            'FHEE__content-espresso_my_events__actions_table_header',
                                            esc_html__('Actions', 'event_espresso'),
                                            $event
                                        )
                                    ); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registrations as $registration) {
                                if (! $registration instanceof EE_Registration) {
                                    continue;
                                }
                                $template_args = ['registration' => $registration];
                                $template      = 'content-espresso_my_events-event_section_tickets.template.php';
                                EEH_Template::locate_template($template, $template_args, true, false);
                            }
                            ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="no-tickets-container">
                        <p>
                            <?php echo esc_html(
                                apply_filters(
                                    'FHEE__content-espresso_my_events-no_tickets_message',
                                    __('You have no tickets for this event', 'event_espresso'),
                                    $event
                                )
                            ); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </td>
</tr>
