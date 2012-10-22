<?php

use MistyDoctrine\Clause\FieldClause;
use MistyTesting\UnitTest;

class FieldClauseTest extends UnitTest
{
	public function testApply()
	{
		$params = array(
			'field' => 'test',
			'field2' => 1,
		);
		$clause = new FieldClause('field');
        $sql = $clause->field('c.field')
		    ->equalTo()
            ->defaultTo('def')
            ->apply($params);

		$this->assertEquals("c.field = 'test'", $sql);
		$this->assertEquals(1, count($params));
		$this->assertFalse(isset($params['field']));
	}

    public function testApply_default()
    {
        $params = array(
            'field' => 'test',
        );
        $clause = new FieldClause('missing-param');
        $sql = $clause->field('c.other')
            ->equalTo()
            ->defaultTo('def')
            ->apply($params);

        $this->assertEquals("c.other = 'def'", $sql);
        $this->assertEquals(1, count($params));
    }
}
