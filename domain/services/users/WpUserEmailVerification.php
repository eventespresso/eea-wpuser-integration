<?php

namespace EventEspresso\WpUser\domain\services\users;

use DomainException;
use EE_Error;
use EventEspresso\core\domain\values\EmailAddress;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidInterfaceException;
use InvalidArgumentException;
use WP_User;

/**
 * Class WpUserEmailVerification
 * Service class for determining whether an email address supplied by the current user:
 *  - belongs to them, another registered user, or is not registered at all
 *  - can be used by them for contact information
 *  - requires login for proof of ownership
 *
 * @package EventEspresso\WpUser\domain\services\users
 * @author  Brent Christensen
 * @since   2.0.15.p
 */
class WpUserEmailVerification
{

    /**
     * the provided email address does not belong to any registered users
     */
    const EMAIL_ADDRESS_NOT_REGISTERED = 'email-address-not-registered';

    /**
     * the provided email address belongs to a registered user - login is required to prove ownership
     */
    const EMAIL_ADDRESS_REGISTERED_LOGIN_REQUIRED = 'email-address-registered-login-required';

    /**
     * current user is logged in, but the provided email address belongs to another registered user
     */
    const EMAIL_ADDRESS_REGISTERED_USER_MISMATCH = 'email-address-registered-user-mismatch';

    /**
     * current user is logged in and the provided email address belongs to them
     */
    const EMAIL_ADDRESS_REGISTERED_TO_CURRENT_USER = 'email-address-registered-to-current-user';


    /**
     * @param string $user_email_verification one of the WpUserEmailVerification::EMAIL_ADDRESS_* constants
     * @return void
     * @throws DomainException
     */
    public function validateUserEmailVerificationOption($user_email_verification)
    {
        if (empty($user_email_verification)
            || ! in_array(
                $user_email_verification,
                array(
                    WpUserEmailVerification::EMAIL_ADDRESS_NOT_REGISTERED,
                    WpUserEmailVerification::EMAIL_ADDRESS_REGISTERED_LOGIN_REQUIRED,
                    WpUserEmailVerification::EMAIL_ADDRESS_REGISTERED_USER_MISMATCH,
                    WpUserEmailVerification::EMAIL_ADDRESS_REGISTERED_TO_CURRENT_USER,
                ),
                true
            )
        ) {
            throw new DomainException(
                esc_html__(
                    'Invalid email address verification option. Please use one of the "EMAIL_ADDRESS_*" constants on WpUserEmailVerification.',
                    'event_espresso'
                )
            );
        }
    }

    /**
     * @param EmailAddress $registrant_email
     * @return string
     * @throws EE_Error
     * @throws InvalidArgumentException
     * @throws InvalidDataTypeException
     * @throws InvalidInterfaceException
     */
    public function verifyWpUserEmailAddress(EmailAddress $registrant_email)
    {
        $user = get_user_by('email', $registrant_email->get());
        if (! $user instanceof WP_User) {
            return WpUserEmailVerification::EMAIL_ADDRESS_NOT_REGISTERED;
        }
        // is the current user logged in?
        if (! is_user_logged_in()) {
            return WpUserEmailVerification::EMAIL_ADDRESS_REGISTERED_LOGIN_REQUIRED;
        }
        // we have a user for that email address.
        // let's verify that this email address matches theirs.
        $current_user = get_userdata(get_current_user_id());
        if ($current_user->user_email !== $user->user_email) {
            return WpUserEmailVerification::EMAIL_ADDRESS_REGISTERED_USER_MISMATCH;
        }
        return WpUserEmailVerification::EMAIL_ADDRESS_REGISTERED_TO_CURRENT_USER;
    }


    /**
     * @param string $user_email_verification            [required] one of the four scenarios defined by the
     *                                                   WpUserEmailVerification::EMAIL_ADDRESS_* constants
     * @param string $login_required_message             [optional] default: no message
     * @param string $user_mismatch_message              [optional] default: no message
     * @param string $not_registered_message             [optional] default: no message
     * @param string $registered_to_current_user_message [optional] default: no message
     * @return string
     * @throws DomainException
     */
    public function getWpUserEmailVerificationNotice(
        $user_email_verification,
        $login_required_message = '',
        $user_mismatch_message = '',
        $not_registered_message = '',
        $registered_to_current_user_message = ''
    ) {
        $this->validateUserEmailVerificationOption($user_email_verification);
        switch ($user_email_verification) {
            case WpUserEmailVerification::EMAIL_ADDRESS_NOT_REGISTERED:
                return $not_registered_message;
                break;
            case WpUserEmailVerification::EMAIL_ADDRESS_REGISTERED_LOGIN_REQUIRED:
                return $login_required_message;
                break;
            case WpUserEmailVerification::EMAIL_ADDRESS_REGISTERED_USER_MISMATCH:
                return $user_mismatch_message;
                break;
            case WpUserEmailVerification::EMAIL_ADDRESS_REGISTERED_TO_CURRENT_USER:
            default:
                return $registered_to_current_user_message;
        }
    }
}
