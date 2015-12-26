<?php


/**
 * Contains test class for eea-wpuser-integration.php
 *
 * @since  		1.0.0
 * @package 	EE WPUsers
 * @subpackage 	Tests
 */
class eea_wpuser_integration_tests extends EE_UnitTestCase {

	/**
	 * Tests the loading of the main file
	 *
	 * @since 1.0.0
	 */
	function test_load_ee_core_wpusers() {
		$this->assertEquals( 10, has_action( 'AHEE__EE_System__load_espresso_addons', 'load_ee_core_wpusers' ) );
		$this->assertTrue( class_exists( 'EE_WPUsers' ) );
	}



}