<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of EE_WPUsersTest
 *
 * @author sidney
 */
class EE_WPUsersTest extends EE_UnitTestCase {


	public function test_loading_ee_wpusers() {
		$this->assertEquals( has_action( 'AHEE__EE_System__load_espresso_addons', 'load_ee_core_wpusers' ), 10 );
		$this->assertTrue( class_exists( 'EE_WPUsers' ) );
	}


}



