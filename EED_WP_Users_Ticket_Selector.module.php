<?php
use EventEspresso\core\domain\services\factories\EmailAddressFactory;
use EventEspresso\core\domain\services\validation\email\EmailValidationException;
use EventEspresso\core\domain\values\Url;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidInterfaceException;
use EventEspresso\core\exceptions\UnexpectedEntityException;
use EventEspresso\core\libraries\form_sections\form_handlers\FormHandler;
use EventEspresso\WaitList\domain\services\forms\WaitListForm;
use EventEspresso\WaitList\domain\services\forms\WaitListFormHandler;
use EventEspresso\WpUser\domain\entities\exceptions\WpUserLogInRequiredException;

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
    const META_KEY_LOGIN_REQUIRED_NOTICE = 'login_required_notice';

    public static function set_hooks()
    {
        add_filter(
            'FHEE__ticket_selector_chart_template__do_ticket_inside_row',
            array('EED_WP_Users_Ticket_Selector', 'maybe_restrict_ticket_option_by_cap'),
            10,
            9
        );
        // maybe display WP User related notices on the wait list form
        add_filter(
            'FHEE__EventEspresso_WaitList_domain_services_event_WaitListMonitor__getWaitListFormForEvent__redirect_params',
            array('EED_WP_Users_Ticket_Selector', 'displayWaitListFormUserNotices'),
            10, 2
        );
        // hook into wait list form submission to check for users
        add_filter(
            'FHEE__EventEspresso_core_libraries_form_sections_form_handlers_FormHandler__process__valid_data',
            array('EED_WP_Users_Ticket_Selector', 'verifyWaitListUserAccess'),
            10, 2
        );
        // convert login required exceptions into user displayable notices
        add_filter(
            'FHEE__EventEspresso_WaitList_domain_services_event_WaitListMonitor__processWaitListFormForEvent__redirect_params',
            array('EED_WP_Users_Ticket_Selector', 'catchWaitListWpUserLogInRequiredException'),
            10, 3
        );
        add_filter(
            'FHEE__EventEspresso_WaitList_domain_services_forms__WaitListFormHandler__generate__tickets',
            array('EED_WP_Users_Ticket_Selector', 'checkWaitListTicketCaps'),
            10,
            9
        );
        add_filter(
            'FHEE__EventEspresso_core_libraries_form_sections_form_handlers_FormHandler__process__valid_data',
            array('EED_WP_Users_Ticket_Selector', 'checkSubmittedWaitListTicketCaps'),
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
        $inner_message     = EED_WP_Users_Ticket_Selector::getMembersOnlyTicketMessage(
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
     * maybe display WP User related notices on the wait list form
     *
     * @param string   $wait_list_form
     * @param EE_Event $event
     * @return string
     * @throws EE_Error
     */
    public static function displayWaitListFormUserNotices($wait_list_form, EE_Event $event)
    {
        if (isset($_REQUEST[ EED_WP_Users_Ticket_Selector::META_KEY_LOGIN_REQUIRED_NOTICE ])) {
            $login_notice_id = sanitize_text_field(
                $_REQUEST[ EED_WP_Users_Ticket_Selector::META_KEY_LOGIN_REQUIRED_NOTICE ]
            );
            $login_notice    = $event->get_extra_meta($login_notice_id, true);
            if ($login_notice) {
                $login_notice = EEH_HTML::div(
                    EEH_HTML::h4(
                        esc_html__('Login Required', 'event_espresso'),
                        'ee-login-notice-h4-' . $event->ID(),
                        'ee-login-notice-h4 important-notice huge-text'
                    )
                    . $login_notice,
                    'ee-login-notice-id-' . $event->ID(),
                    'ee-login-notice ee-attention'
                );
                $wait_list_form = $login_notice . $wait_list_form;
                $event->delete_extra_meta($login_notice_id);
            }
        }
        return $wait_list_form;
    }


    /**
     * hook into wait list form submission to check for users
     *
     * @param array       $form_data
     * @param FormHandler $form
     * @return array
     * @throws WpUserLogInRequiredException
     * @throws EmailValidationException
     * @throws RuntimeException
     * @throws EE_Error
     * @throws InvalidArgumentException
     * @throws InvalidDataTypeException
     * @throws InvalidInterfaceException
     * @throws UnexpectedEntityException
     */
    public static function verifyWaitListUserAccess(array $form_data, FormHandler $form)
    {
        // only process the wait list form
        if (! $form instanceof WaitListFormHandler) {
            return $form_data;
        }
        $event_id     = $form->event()->ID();
        $login_notice = EED_WP_Users_SPCO::verifyWpUserEmailAddress(
            EmailAddressFactory::create($form_data['hidden_inputs']['registrant_email']),
            new Url(get_permalink($event_id))
        );
        if ($login_notice !== '') {
            throw new WpUserLogInRequiredException($login_notice);
        }
        return $form_data;
    }


    /**
     * convert login required exceptions into user displayable notices
     *
     * @param array     $redirect_params
     * @param Exception $exception
     * @param EE_Event  $event
     * @return array
     * @throws EE_Error
     */
    public static function catchWaitListWpUserLogInRequiredException(
        array $redirect_params,
        Exception $exception,
        EE_Event $event
    ) {
        if ($exception instanceof WpUserLogInRequiredException) {
            $login_notice_id = md5($event->ID() . $event->name() . time());
            $event->add_extra_meta($login_notice_id, $exception->getMessage(), true);
            $redirect_params[ EED_WP_Users_Ticket_Selector::META_KEY_LOGIN_REQUIRED_NOTICE ] = $login_notice_id;
        }
        return $redirect_params;
    }
    /**
     * @param EE_Ticket $ticket
     * @param string    $ticket_price
     * @param string    $ticket_status
     * @return string
     * @throws EE_Error
     */
    private static function getMembersOnlyTicketMessage(EE_Ticket $ticket, $ticket_price, $ticket_status)
    {
        return (string) apply_filters(
            'FHEE__EED_WP_Users_Ticket_Selector__maybe_restrict_ticket_option_by_cap__no_access_msg',
            sprintf(
                esc_html__(
                    'The %1$s%2$s%3$s%4$s  is available to members only. %5$s',
                    'event_espresso'
                ),
                '<strong>',
                $ticket->name(),
                $ticket_price,
                '</strong>',
                $ticket_status
            ),
            $ticket,
            $ticket_price,
            $ticket_status
        );
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


    /**
     * @param array       $tickets
     * @param EE_Ticket[] $active_tickets
     * @param EE_Event    $event
     * @return array
     * @throws EE_Error
     * @throws InvalidArgumentException
     * @throws InvalidDataTypeException
     * @throws InvalidInterfaceException
     */
    public static function checkWaitListTicketCaps(array $tickets, array $active_tickets, EE_Event $event)
    {
        foreach ($active_tickets as $active_ticket) {
            if (! EED_WP_Users_Ticket_Selector::ticketAvailableToUser($active_ticket)) {
                unset($tickets[ $active_ticket->ID() ]);
                if(is_user_logged_in()) {
                    $option_id = 'members_only';
                    $login_notice = '';
                } else {
                    $option_id = 'login_required';
                    $login_notice = esc_html__('Please login.', 'event_espresso');
                }
                $tickets[ $option_id ] = esc_html__('Members Only Ticket Option.', 'event_espresso');
                $tickets[ $option_id ] .= " {$login_notice}";
            }
        }
        return $tickets;
    }


    public static function checkSubmittedWaitListTicketCaps(array $valid_form_data, FormHandler $form_handler)
    {
        if(! $form_handler instanceof WaitListFormHandler) {
            return $valid_form_data;
        }
        if(isset($valid_form_data['hidden_inputs']['ticket'])){
            if($valid_form_data['hidden_inputs']['ticket'] === 'members_only'){
                throw new RuntimeException('NO TICKET FOR YOU!!!');
            } else if ($valid_form_data['hidden_inputs']['ticket'] === 'login_required') {
                throw new RuntimeException('LOGIN BRUH!!!');
            }
        }
        return $valid_form_data;
    }
}

