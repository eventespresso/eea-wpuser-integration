<?php

namespace EventEspresso\WpUser\domain\services\assets;

use DomainException;
use EventEspresso\core\domain\Domain;
use EventEspresso\core\domain\services\assets\ReactAssetManager;
use EventEspresso\WpUser\domain\Domain as WpUserDomain;

/**
 * Class EventEditorAssetManager
 *
 * @author  Brent Christensen
 * @package EventEspresso\WpUser\domain\services\assets
 * @since   $VID:$
 */
class EventEditorAssetManager extends ReactAssetManager
{

    const ASSET_HANDLE = Domain::ASSET_NAMESPACE . '-' . WpUserDomain::NAME;


    /**
     * @throws DomainException
     */
    public function enqueueEventEditor()
    {
        if ($this->verifyAssetIsRegistered(EventEditorAssetManager::ASSET_HANDLE)) {
            wp_enqueue_script(EventEditorAssetManager::ASSET_HANDLE);
            wp_enqueue_style(EventEditorAssetManager::ASSET_HANDLE);
        }
    }
}
