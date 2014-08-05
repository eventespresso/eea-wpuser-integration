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
	public function testFilterAnswerForWPUser() {
		assertTrue('some answer' === EE_WPUsers::filterAnswerForWPUser( 'some answer', EE_Registration::new_instance(), 1) );
	}
}
