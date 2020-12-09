<?php

namespace EventEspresso\WpUser\domain\entities\routing;

use EE_Dependency_Map;
use EventEspresso\core\domain\entities\routing\handlers\admin\EspressoEventEditor as CoreEventEditor;
use EventEspresso\WpUser\domain\Domain;
use EventEspresso\WpUser\domain\services\assets\EventEditorAssetManager;

/**
 * Class EspressoEventEditor
 *
 * @author  Brent Christensen
 * @package EventEspresso\WpUser\domain\entities\routing
 * @since   $VID:$
 */
class EspressoEventEditor extends CoreEventEditor
{

    protected function registerDependencies()
    {
        $this->dependency_map->registerDependencies(
            EventEditorAssetManager::class,
            [
                'EventEspresso\WpUser\domain\Domain'                 => EE_Dependency_Map::load_from_cache,
                'EventEspresso\core\services\assets\AssetCollection' => EE_Dependency_Map::load_from_cache,
                'EventEspresso\core\services\assets\Registry'        => EE_Dependency_Map::load_from_cache,
            ]
        );
    }


    /**
     * implements logic required to run during request
     *
     * @return bool
     */
    protected function requestHandler()
    {
        $this->initializeBaristaForDomain(Domain::class);
        /** @var EventEditorAssetManager $asset_manager */
        $asset_manager = $this->loader->getShared(
            EventEditorAssetManager::class,
            [getWpUserDomain()]
        );
        add_action('admin_enqueue_scripts', [$asset_manager, 'enqueueEventEditor'], 3);
        return true;
    }
}
