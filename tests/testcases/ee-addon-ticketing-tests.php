<?php
/**
 * Contains test class for ee-addon-ticketing.php
 *
 * @since  		1.0.0
 * @package 		EE Ticketing
 * @subpackage 	tests
 */


/**
 * Test class for ee-addon-ticketing.php
 *
 * @since 		1.0.0
 * @package 		EE Ticketing
 * @subpackage 	tests
 */
class ee_addon_ticketing_tests extends EE_UnitTestCase {

	/**
	 * Tests the loading of the main file
	 *
	 * @since 1.0.0
	 */
	function test_load_ee_core_ticketing() {
		$this->assertEquals( has_action('AHEE__EE_System__load_espresso_addons', 'load_ee_core_ticketing'), 10 );
		$this->assertTrue( class_exists( 'EE_Ticketing' ) );
	}
}
