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
 * @since   2.1.0.p
 */
class EspressoEventEditor extends CoreEventEditor
{
    /**
     * @var WpUserData $data_node
     */
    protected $data_node;


    /**
     * called just before matchesCurrentRequest()
     * and allows Route to perform any setup required such as calling setSpecification()
     *
     * @return void
     */
    public function initialize()
    {
        $this->initializeBaristaForDomain(Domain::class);
    }


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
        $this->dependency_map->registerDependencies(
            WpUserData::class,
            [
                'EventEspresso\core\services\json\JsonDataNodeValidator' => EE_Dependency_Map::load_from_cache,
                'EventEspresso\core\domain\services\graphql\Utilities'   => EE_Dependency_Map::load_from_cache,
            ]
        );
    }


    /**
     * @return string
     */
    protected function dataNodeClass(): string
    {
        return WpUserData::class;
    }


    /**
     * implements logic required to run during request
     *
     * @return bool
     */
    protected function requestHandler(): bool
    {
        /** @var EventEditorAssetManager $asset_manager */
        $asset_manager = $this->loader->getShared(
            EventEditorAssetManager::class,
            [getWpUserDomain()]
        );
        add_action('admin_enqueue_scripts', [$asset_manager, 'enqueueEventEditor']);
        return true;
    }
}
