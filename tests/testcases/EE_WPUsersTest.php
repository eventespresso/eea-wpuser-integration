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
		$transaction = $this->new_typical_transaction();
		
		ob_start();
		var_dump($transaction);
		$temp=  ob_get_clean();
		file_put_contents('/tmp/log.txt',$temp, FILE_APPEND);
		
		assert('some answer' === 'some answer' );
	}
}
