<?php

use MistyDoctrine\Clause\CustomClause;
use MistyTesting\UnitTest;

class CustomClauseTest extends UnitTest
{
	public function testCustomFunction()
	{
		$clause = new CustomClause(function(&$params){
			unset($params['field']);
			return 'field = 1';
		});

		$params = array(
			'field' => 1,
			'name' => 1,
		);

		$sql = $clause->apply($params);

		$this->assertEquals('field = 1', $sql);
		$this->assertEquals(1, count($params));
		$this->assertTrue(isset($params['name']));
	}
}
