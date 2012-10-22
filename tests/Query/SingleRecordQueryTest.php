<?php

use MistyTesting\UnitTest;
use MistyDoctrine\Clause\ClauseParser;
use MistyDoctrine\Query\SingleRecordQuery;

class SingleRecordQueryTest extends UnitTest
{
    /** @var SingleRecordQuery */
    private $query;

    /** @var \Mockery\MockInterface */
    private $queryBuilder;

    /** @var ClauseParser */
    private $clauseParser;

    public function before()
    {
        $this->queryBuilder = Mockery::mock('QueryBuilder');
        $this->clauseParser = new ClauseParser();
        $this->query = new SingleRecordQuery($this->queryBuilder, $this->clauseParser, array(
            'prefix' => 'm',
            'idField' => 'id',
        ));

        $this->clauseParser->add('id', 'm.id')->equalTo();
        $this->clauseParser->add('name', 'm.name')->startsWith();
        $this->clauseParser->add('users', 'm.user_id')->in();
    }

    public function testWhere()
    {
        $this->queryBuilder->shouldReceive('andWhere')->with('m.id = 1');
        $this->query->where(array(
            'id' => 1
        ));
    }

    public function testSequentialWhere()
    {
        $this->queryBuilder->shouldReceive('andWhere')->with('m.id = 1');
        $this->queryBuilder->shouldReceive('andWhere')->with('m.name LIKE \'name%\'');
        $this->query->where(array(
            'id' => 1,
        ));
        $this->query->where(array(
            'name' => 'name',
        ));
    }

    public function testBy()
    {
        $this->queryBuilder->shouldReceive('andWhere')->with('m.id = 1');
        $this->queryBuilder->shouldReceive('andWhere')->with('m.name = \'name\'');
        $this->query->by(1);
        $this->query->by('name', 'm.name');
    }

    public function testFind_null()
    {
        $this->setupQueryReturn(null);

        $object = $this->query->find();
        $this->assertNull($object);
    }

    public function testFind_object()
    {
        $expected = new StdClass;
        $this->setupQueryReturn($expected);

        $object = $this->query->find();
        $this->assertEquals($expected, $object);
    }

    public function testFind_cache()
    {
        $this->setupQueryReturn(null);
        $this->query->find();
        $this->query->find();
    }

    /**
     * @expectedException MistyDoctrine\Exception\EntityNotFoundException
     */
    public function testGet_null()
    {
        $this->setupQueryReturn(null);
        $this->query->get();
    }

    /**
     * @expectedException MistyDoctrine\Exception\QueryException
     */
    public function testWhere_whenAlreadyExecuted()
    {
        $this->setupQueryReturn(null);

        $this->query->find();
        $this->query->by(1);
    }

    public function testJoin()
    {
        $this->queryBuilder
            ->shouldReceive('join')
            ->once()
            ->andReturn($this->queryBuilder);

        $this->query
            ->join(array(
                'm.pro' => 'p'
            ));
    }

    /**
     * @expectedException MistyDoctrine\Exception\QueryException
     */
    public function testJoin_whenAlreadyExecuted()
    {
        $this->setupQueryReturn(null);

        $this->query->find();
        $this->query->join(array(
            'm.pro' => 'p'
        ));
    }

    private function setupQueryReturn($return)
    {
        $this->queryBuilder->shouldReceive('getQuery')
            ->once()
            ->andReturn($this->queryBuilder);

        $this->queryBuilder->shouldReceive('getOneOrNullResult')
            ->once()
            ->andReturn($return);
    }
}
