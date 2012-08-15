<?php

use MistyDoctrine\Model;
use MistyTesting\UnitTest;

class ModelTest extends UnitTest
{
	public function testSetAndGet()
	{
		$value = 'value1';

		$model = new TestModel();
		$model->prop1 = $value;

		$this->assertEquals($value, $model->prop1);
	}

	/**
    * @expectedException MistyDoctrine\Exception\ModelException
    */
	public function testSet_invalidKey()
	{
		$model = new TestModel();
		$model->invalid = 'value';
	}

	/**
    * @expectedException TestModelException
    */
    public function testSet_useSetter()
    {
    	$model = new TestModel();
		$model->with_setter = 'value';
    }

	/**
    * @expectedException MistyDoctrine\Exception\ModelException
    */
	public function testGet_invalidKey()
	{
		$model = new TestModel();
		echo $model->invalid;
	}

	/**
    * @expectedException TestModelException
    */
    public function testGet_useGetter()
    {
		$model = new TestModel();
		echo $model->with_getter;
    }

	public function testSetArray()
	{
		$model = new TestModel();
		$model->setArray(array(
			'prop1' => 1,
			'prop2' => 'two'
		));

		$this->assertEquals(1, $model->prop1);
		$this->assertEquals('two', $model->prop2);
		$this->assertNull($model->prop3);
	}

	/**
    * @expectedException MistyDoctrine\Exception\ModelException
    */
	public function testSetArray_invalidKey()
	{
		$model = new TestModel();
		$model->setArray(array(
			'invalid' => 1
		));
	}

	/**
    * @expectedException TestModelException
    */
	public function testSetArray_callsSetter()
	{
		$model = new TestModel();
		$model->setArray(array(
			'with_setter' => 1
		));
	}

	public function testToArray()
	{
		$model = new TestModel();
		$model->setArray(array(
			'prop1' => 1,
			'prop2' => 'two'
		));

		$array = $model->toArray();

		$this->assertEquals(1, $array['prop1']);
		$this->assertEquals('two', $array['prop2']);
	}

	public function testFunctionify()
	{
		$this->assertEquals('Name', Model::functionify('name'));
		$this->assertEquals('FirstName', Model::functionify('first_name'));
		$this->assertEquals('UserId', Model::functionify('user_id'));
	}
}

class TestModel extends Model
{
	protected $prop1;
	protected $prop2;
	protected $prop3;
	protected $with_setter;
	protected $with_getter;

	public function setWithSetter()
	{
		throw new TestModelException();
	}

	public function getWithGetter()
	{
		throw new TestModelException();
	}
}

class TestModelException extends \Exception
{

}
