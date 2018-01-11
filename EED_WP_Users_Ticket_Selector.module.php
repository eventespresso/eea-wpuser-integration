<?php

use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidInterfaceException;

defined('EVENT_ESPRESSO_VERSION') || exit;



/**
 * EED_WP_Users_Ticket_Selector module.  Takes care of WP Users integration with ticket selector.
 *
 * @since          1.0.0
 * @package        EE WP Users
 * @subpackage     modules
 * @author         Darren Ethier
 */
class EED_WP_Users_Ticket_Selector extends EED_Module
{

    public static function set_hooks()
    {
        add_filter(
            'FHEE__ticket_selector_chart_template__do_ticket_inside_row',
            array('EED_WP_Users_Ticket_Selector', 'maybe_restrict_ticket_option_by_cap'),
            10,
            9
        );
    }


    public static function set_hooks_admin()
    {
    }


    public static function enqueue_scripts_styles()
    {
    }


    public function run($WP)
    {
    }


    /**
     * Callback for FHEE__ticket_selector_chart_template__do_ticket_inside_row filter.
     * We use this to possibly replace the generated row for the current ticket being displayed in the
     * ticket selector if the ticket has a required cap and the viewer does not have access to that ticket
     * option.
     *
     * @param bool|string $return_value                        Either false, which means we're not doing anything
     *                                                         and let the ticket selector continue on its merry way,
     *                                                         or a string if we're replacing what get's generated.
     * @param EE_Ticket   $tkt
     * @param int         $max                                 Max tickets purchasable
     * @param int         $min                                 Min tickets purchasable
     * @param bool|string $required_ticket_sold_out            Either false for tickets not sold out, or date.
     * @param float       $ticket_price                        Ticket price
     * @param bool        $ticket_bundle                       Is this a ticket bundle?
     * @param string      $tkt_status                          The status for the ticket.
     * @param string      $status_class                        The status class for the ticket.
     * @return bool|string    @see $return value.
     * @throws EE_Error
     * @throws InvalidArgumentException
     * @throws InvalidDataTypeException
     * @throws InvalidInterfaceException
     */
    public static function maybe_restrict_ticket_option_by_cap(
        $return_value,
        EE_Ticket $tkt,
        $max,
        $min,
        $required_ticket_sold_out,
        $ticket_price,
        $ticket_bundle,
        $tkt_status,
        $status_class
    ) {
        if (EED_WP_Users_Ticket_Selector::ticketAvailableToUser($tkt)) {
            return false;
        }
        // made it here?  That means user does not have access to this ticket,
        // so let's return a filterable message for them.
        $ticket_price      = empty($ticket_price)
            ? ''
            : ' (' . EEH_Template::format_currency($ticket_price) . ')';
        $full_html_content = '<td class="tckt-slctr-tbl-td-name" colspan="3">';
        $inner_message     = apply_filters(
            'FHEE__EED_WP_Users_Ticket_Selector__maybe_restrict_ticket_option_by_cap__no_access_msg',
            sprintf(
                esc_html__(
                    'The %1$s%2$s%3$s%4$s  is available to members only. %5$s',
                    'event_espresso'
                ),
                '<strong>',
                $tkt->name(),
                $ticket_price,
                '</strong>',
                $tkt_status
            ),
            $tkt,
            $ticket_price,
            $tkt_status
        );
        $full_html_content .= $inner_message . '</td>';
        $full_html_content = apply_filters(
            'FHEE__EED_WP_Users_Ticket_Selector__maybe_restrict_ticket_option_by_cap__no_access_msg_html',
            $full_html_content,
            $inner_message,
            $tkt,
            $ticket_price,
            $tkt_status
        );
        return $full_html_content;
    }


    /**
     * @param EE_Ticket $ticket
     * @return bool
     * @throws EE_Error
     * @throws InvalidArgumentException
     * @throws InvalidDataTypeException
     * @throws InvalidInterfaceException
     */
    public static function ticketAvailableToUser(EE_Ticket $ticket)
    {
        // don't check caps if adding registrants via the admin
        if (! EE_FRONT_AJAX && is_admin()) {
            return true;
        }
        // check if any caps are required to access this ticket
        $cap_required = $ticket->get_extra_meta('ee_ticket_cap_required', true);
        if (empty($cap_required)) {
            // no cap required so user has access by default
            return true;
        }
        // return true if user is logged in has the correct caps to access this ticket,
        // otherwise return false because they are not logged in or don't have the required cap
        return is_user_logged_in()
               && EE_Registry::instance()->CAP->current_user_can(
                $cap_required,
                'wp_user_ticket_selector_check'
            );
    }
}
