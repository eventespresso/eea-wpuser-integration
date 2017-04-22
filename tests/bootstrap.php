<?php
/**
 * Bootstrap for eea-wpuser-integration tests
 */

use EETests\bootstrap\AddonLoader;

$core_tests_dir = dirname(dirname(dirname(__FILE__))) . '/event-espresso-core/tests/';
//if still don't have $core_tests_dir, then let's check tmp folder.
if (! is_dir($core_tests_dir)) {
    $core_tests_dir = '/tmp/event-espresso-core/tests/';
}
require $core_tests_dir . 'includes/CoreLoader.php';
require $core_tests_dir . 'includes/AddonLoader.php';

define('EE_WPUSERS_PLUGIN_DIR', dirname(dirname(__FILE__)) . '/');
define('EE_WPUSERS_TESTS_DIR', EE_WPUSERS_PLUGIN_DIR . 'tests/');


$addon_loader = new AddonLoader(
    EE_WPUSERS_TESTS_DIR,
    EE_WPUSERS_PLUGIN_DIR,
    'eea-wpuser-integration.php'
);
$addon_loader->init();
