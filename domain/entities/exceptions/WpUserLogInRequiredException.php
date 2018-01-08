<?php

namespace EventEspresso\WpUser\domain\entities\exceptions;

use RuntimeException;

defined('EVENT_ESPRESSO_VERSION') || exit;



/**
 * Class WpUserLogInRequiredException
 * thrown when an email address for an existing WP User is used in a form
 * but login is required first in order to do so
 *
 * @package EventEspresso\WpUser\domain\entities\exceptions
 * @author  Brent Christensen
 * @since   $VID:$
 */
class WpUserLogInRequiredException extends RuntimeException
{

    /**
     * InvalidFormSubmissionException constructor
     *
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
