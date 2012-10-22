<?php

use MistyDoctrine\Clause\ClauseDefinition;
use MistyTesting\UnitTest;

class ClauseDefinitionTest extends UnitTest
{
	public function testEqualTo()
	{
		$this->assertEquals('field IS NULL', ClauseDefinition::equalTo('field', null));
		$this->assertEquals('field = 1', ClauseDefinition::equalTo('field', 1));
		$this->assertEquals("field = '\\'; SELECT'", ClauseDefinition::equalTo('field', "'; SELECT"));
	}

	public function testNotEqualTo()
	{
		$this->assertEquals('field IS NOT NULL', ClauseDefinition::notEqualTo('field', null));
		$this->assertEquals('field != 1', ClauseDefinition::notEqualTo('field', 1));
		$this->assertEquals("field != '\\'; SELECT'", ClauseDefinition::notEqualTo('field', "'; SELECT"));
	}

	public function testNotEmpty()
	{
		$this->assertEquals("( field IS NOT NULL AND field != '' )", ClauseDefinition::notEmpty('field'));
	}

	public function testStartsWith()
	{
		$this->assertEquals("field LIKE 'name%'", ClauseDefinition::startsWith('field', 'name'));
		$this->assertEquals("field LIKE 'name\_\%\'%'", ClauseDefinition::startsWith('field', "name_%'"));
	}

	public function testLessThan()
	{
		$this->assertEquals('field < 5', ClauseDefinition::lessThan('field', '5'));
		$this->assertEquals("field < 'value'", ClauseDefinition::lessThan('field', 'value'));
	}

	public function testMoreThan()
	{
		$this->assertEquals('field > 5', ClauseDefinition::moreThan('field', '5'));
		$this->assertEquals("field > 'value'", ClauseDefinition::moreThan('field', 'value'));
	}

	public function testIsNull()
	{
		$this->assertEquals("field IS NULL", ClauseDefinition::isNull('field'));
	}

	public function testIsNotNull()
	{
		$this->assertEquals("field IS NOT NULL", ClauseDefinition::isNotNull('field'));
	}

	public function testIn()
	{
		$this->assertEquals("field = 'value'", ClauseDefinition::in('field', 'value'));
		$this->assertEquals("field IN (1,2,3)", ClauseDefinition::in('field', array(1,2,3)));
		$this->assertEquals("field IN ('a','b','c')", ClauseDefinition::in('field', array('a','b','c')));
	}

	public function testNotIn()
	{
		$this->assertEquals("field != 'value'", ClauseDefinition::notIn('field', 'value'));
		$this->assertEquals("field NOT IN (1,2,3)", ClauseDefinition::notIn('field', array(1,2,3)));
		$this->assertEquals("field NOT IN ('a','b','c')", ClauseDefinition::notIn('field', array('a','b','c')));
	}

	public function testIsTrue()
	{
		$this->assertEquals('field = 1', ClauseDefinition::isTrue('field'));
	}

	public function testIsFalse()
	{
		$this->assertEquals('field = 0', ClauseDefinition::isFalse('field'));
	}

	public function testBoolean()
	{
		$this->assertEquals('field = 1', ClauseDefinition::boolean('field', true));
		$this->assertEquals('field = 1', ClauseDefinition::boolean('field', 1));
		$this->assertEquals('field = 0', ClauseDefinition::boolean('field', false));
		$this->assertEquals('field = 0', ClauseDefinition::boolean('field', 0));
	}
}



