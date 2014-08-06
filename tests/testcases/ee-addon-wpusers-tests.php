<?php


/**
 * Contains test class for ee-addon-wpusers.php
 *
 * @since  		1.0.0
 * @package 	EE WPUsers
 * @subpackage 	Tests
 */
class ee_addon_wpusers_tests extends EE_UnitTestCase {





	/**
	 * Tests the loading of the main file
	 *
	 * @since 1.0.0
	 */
	function test_load_ee_core_wpusers() {
		$this->assertEquals( has_action( 'AHEE__EE_System__load_espresso_addons', 'load_ee_core_wpusers' ), 10 );
		$this->assertTrue( class_exists( 'EE_WPUsers' ) );
		$this->assertEquals( 10, has_filter( 'FHEE__EEM_Answer__get_attendee_question_answer_value__answer_value', array( 'EE_WPUsers', 'filterAnswerForWPUser' ) ) );
		$this->assertEquals( 10, has_action( 'AHEE__EE_Single_Page_Checkout__process_attendee_information__end', array( 'EE_WPUsers', 'actionAddAttendeeAsWPUser' ) ) );

	}



}



