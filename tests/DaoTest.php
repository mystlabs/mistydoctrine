<?php

use MistyDoctrine\Dao;
use MistyDoctrine\Test\DoctrineTest;

class DaoTest extends DoctrineTest
{
	protected $dao;

	public function before()
	{
		$this->dao = new DaoTestImpl( $this->entityManager );
	}

	public function testActiveTransactions()
	{
		$this->assertFalse( $this->dao->hasActiveTransaction() );
		$this->dao->start();
		$this->assertTrue( $this->dao->hasActiveTransaction() );
		$this->dao->commit();
		$this->assertFalse( $this->dao->hasActiveTransaction() );
	}

	public function testFlush()
	{
		// start the transaction
		$this->dao->start();

		$car = new Car();
		$car->model = 'whatever';
		$car->speed = 100;

		// we save the object, but it should be in the db yet
		$this->entityManager->persist($car);
		$this->assertRecordCount(0, 'cars');

		// flushing
		$this->entityManager->flush();
		$this->assertRecordCount(1, 'cars');

		$this->dao->commit();
		$this->assertRecordCount(1, 'cars');
	}

	public function testRollback()
	{
		// start the transaction
		$this->dao->start();

		$car = new Car();
		$car->model = 'whatever';
		$car->speed = 100;

		// save the object
		$this->entityManager->persist($car);
		$this->entityManager->flush();

		// rollback
		$this->dao->rollback();

		// we shouldn't have an object
		$this->assertRecordCount(0, 'cars');
	}

	public function testExecuteSqlUpdate()
	{
		$this->assertRecordCount(0, 'cars');

		$this->dao->executeSqlUpdate(
			'INSERT INTO cars VALUES (?,?,?,?)',
			array(1, null, 'model', 10)
		);

		$this->assertRecordCount(1, 'cars');
	}

	public function testExecuteSqlGetScalar()
	{
		$this->createCar('A', 10);
		$this->createCar('B', 20);

		$this->assertEquals('B', $this->dao->executeSqlGetScalar(
			'SELECT model FROM cars WHERE speed > ?',
			array(10)
		));
	}

	public function testExecuteSqlGetAll()
	{
		$this->createCar('A', 10);
		$this->createCar('B', 20);

		$car1 = array(
			'id' => '1',
			'owner_id' => null,
			'model' => 'A',
			'speed' => '10'
		);

		$car2 = array(
			'id' => '2',
			'owner_id' => null,
			'model' => 'B',
			'speed' => '20'
		);

		$this->assertEquals(
			array($car1),
			$this->dao->executeSqlGetAll( 'SELECT * FROM cars', array(), 0, 1 )
		);

		$this->assertEquals(
			array($car1, $car2),
			$this->dao->executeSqlGetAll( 'SELECT * FROM cars', array() )
		);
	}

	public function testExecuteDqlUpdate()
	{
		$this->createCar('A', 10);
		$this->dao->executeDqlUpdate('UPDATE Car c SET c.speed = 5 WHERE c.id=?0', array(1));

		$this->assertEquals(5, $this->dao->executeSqlGetScalar('SELECT speed FROM cars WHERE id = ?', array(1)));
	}

	public function testExecuteDqlGetScalar()
	{
		$this->createCar('A', 10);

		$this->assertEquals(10, $this->dao->executeDqlGetScalar('SELECT c.speed FROM Car c WHERE c.id=?0', array(1)));
	}

	public function testExecuteDqlGetAll()
	{
		$this->createCar('A', 10);
		$this->createCar('B', 20);

		$car1 = array(
			'id' => 1,
			'model' => 'A',
			'speed' => 10
		);

		$car2 = array(
			'id' => 2,
			'model' => 'B',
			'speed' => 20
		);

		$this->assertEquals(
			array($car1),
			$this->dao->executeDqlGetAll( 'SELECT c FROM Car c', array(), 0, 1 )
		);

		$this->assertEquals(
			array($car1, $car2),
			$this->dao->executeDqlGetAll( 'SELECT c FROM Car c', array() )
		);
	}

	private function createCar( $model, $speed )
	{
		$car = new Car();
		$car->model = $model;
		$car->speed = $speed;

		$this->entityManager->persist($car);
		$this->entityManager->flush();
	}
}

class DaoTestImpl extends Dao
{
	public function __call($method, $args)
	{
		return call_user_func_array(array($this, $method), $args);
	}
}
