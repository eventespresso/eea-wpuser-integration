<?php
/**
 * Bootstrap for eea-wpuser-integration tests
 */

use EETests\bootstrap\AddonLoader;

$core_tests_dir = dirname(dirname(dirname(__FILE__))) . '/event-espresso-core/tests/';
require $core_tests_dir . 'includes/CoreLoader.php';
require $core_tests_dir . 'includes/AddonLoader.php';

define('EE_WPUSERS_PLUGIN_DIR', dirname(dirname(__FILE__)) . '/');
define('EE_WPUSERS_TESTS_DIR', EE_WPUSERS_PLUGIN_DIR . 'tests/');


$addon_loader = new AddonLoader(
    EE_WPUSERS_TESTS_DIR,
    EE_WPUSERS_PLUGIN_DIR,
    'eea-people-addon.php'
);
$addon_loader->init();
