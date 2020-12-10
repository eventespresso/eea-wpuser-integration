<?php

namespace EventEspresso\WpUser\domain\entities\routing;

use EventEspresso\core\domain\entities\routing\handlers\shared\GQLRequests as CoreGQLRequests;
use EventEspresso\WpUser\domain\services\graphql\RegisterSchema;

/**
 * Class GQLRequests
 *
 * @author  Brent Christensen
 * @package EventEspresso\WpUser\domain\entities\routing
 * @since   $VID:$
 */
class GQLRequests extends CoreGQLRequests
{

    protected function registerDependencies()
    {
    }


    /**
     * implements logic required to run during request
     *
     * @return bool
     */
    protected function requestHandler(): bool
    {
        /** @var RegisterSchema $schema */
        $schema = $this->loader->getShared(RegisterSchema::class);
        $schema->addHooks();
        return true;
    }
}
