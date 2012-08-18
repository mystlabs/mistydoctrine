<?php

use MistyDoctrine\Transaction\Transactional;
use MistyTesting\UnitTest;

class TransactionalTest extends UnitTest
{
	protected $mockDao;
	protected $transactional;

	public function before()
	{
		$this->mockDao = Mockery::mock('MistyDoctrine\Dao');
		$this->transactional = new TransactionalTestImpl( $this->mockDao );
	}

	public function testNewTransaction()
	{
		$this->_setupExpectation( false, true, true, false );
		$this->transactional->t(function(){});
	}

	public function testNewTransactionFailure()
	{
		$this->_setupExpectation( false, true, false, true );
		$this->transactional->t(function(){
			throw new RuntimeException();
		});
	}

	/**
    * @expectedException RuntimeException
    */
	public function testNewTransactionFailureWithCallback()
	{
		$this->_setupExpectation( false, true, false, true );
		$this->transactional->t(function(){
			throw new RuntimeException();
		}, function(){
			throw new RuntimeException();
		});
	}

	public function testInnerTransaction()
	{
		$this->_setupExpectation( true, false, false, false );
		$this->transactional->t(function(){});
	}

	/**
    * @expectedException RuntimeException
    */
	public function testInnerTransactionFailure()
	{
		$this->_setupExpectation( true, false, false, false );
		$this->transactional->t(function(){
			throw new RuntimeException();
		});
	}

	public function testCanCallPrivateMethods()
	{
		$this->_setupExpectation( false, true, true, false );
		$this->transactional->t(function($self){
			$self->privateMethod();
		});
	}

	public function testCanReadPrivateFields()
	{
		$this->_setupExpectation( false, true, true, false );
		$this->transactional->t(function($self){
			$self->privateField;
		});
	}

	public function testNestedTransactions()
	{
		$this->_setupExpectation(array(false, true), true, true, false);
		$transactional = $this->transactional;
		$this->transactional->t(function() use ($transactional){
			// outer
			$transactional->t(function(){
				// inner
			});
		});
	}

	public function testNestedTransactionsFailure()
	{
		$this->_setupExpectation(array(false, true), true, false, true);
		$transactional = $this->transactional;
		$this->transactional->t(function() use ($transactional){
			// outer
			$transactional->t(function(){
				throw new \Exception();
			});
		});
	}

	private function _setupExpectation($active, $shouldStart, $shouldCommit, $shouldRollback)
	{
		$activeReturns = is_array($active) ? $active : array($active, null);

		$this->mockDao
			->shouldReceive('hasActiveTransaction')
			->times(count($active))
			->andReturn($activeReturns[0], $activeReturns[1]);

		if( $shouldStart )
		{
			$this->mockDao
				->shouldReceive('start')
				->once();
		}

		if( $shouldCommit )
		{
			$this->mockDao
				->shouldReceive('commit')
				->once();
		}

		if( $shouldRollback )
		{
			$this->mockDao
				->shouldReceive('rollback')
				->once();
		}
	}
}

class TransactionalTestImpl extends Transactional
{
	private $privateField;

	public function t($func, $callback=null)
	{
		return parent::t($func, $callback);
	}

	private function privateMethod()
	{

	}
}
