<?php

namespace EventEspresso\WpUser\domain;

use EventEspresso\core\domain\DomainBase;

/**
 * Domain Class
 * A container for all domain data related to the EE WP User Integration add-on
 *
 * @package     Event Espresso
 * @subpackage  WpUser
 * @author      Event Espresso
 */
class Domain extends DomainBase
{
    const NAME = 'wpUser';

    /**
     * EE Core Version Required for Add-on
     */
    const CORE_VERSION_REQUIRED = EE_WPUSERS_MIN_CORE_VERSION_REQUIRED;

    public const LICENSE_PLUGIN_ID = 0;
    public const LICENSE_PLUGIN_NAME = 'WordPress User Integration';
    public const LICENSE_PLUGIN_SLUG = 'eea-wp-user-integration';
}
