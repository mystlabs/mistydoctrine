<?php

use MistyTesting\UnitTest;
use MistyDoctrine\Clause\ClauseParser;
use MistyDoctrine\Query\MultipleRecordsQuery;

class MultipleRecordsQueryTest extends UnitTest
{
    /** @var MultipleRecordsQuery */
    private $query;

    /** @var \Mockery\MockInterface */
    private $queryBuilder;

    /** @var ClauseParser */
    private $clauseParser;

    public function before()
    {
        $this->queryBuilder = Mockery::mock('QueryBuilder');
        $this->clauseParser = new ClauseParser();
        $this->query = new MultipleRecordsQuery($this->queryBuilder, $this->clauseParser, array(
            'idField' => 'm.id'
        ));

        $this->clauseParser->add('id', 'm.id')->equalTo();
        $this->clauseParser->add('name', 'm.name')->startsWith();
        $this->clauseParser->add('users', 'm.user_id')->in();
    }

    public function testToArray_cache()
    {
        $this->setupQueryReturn(array(1));
        $this->query->toArray();
        $this->query->toArray();
    }

    public function testCount_cache()
    {
        $this->setupCountReturn(5);

        $this->assertEquals(5, $this->query->count());
        $this->assertEquals(5, $this->query->count());
    }

    public function testCount_fromResultWhenNoRange()
    {
        $this->setupLimit();
        $this->setupQueryReturn(array(1));
        $this->query->toArray();

        // no limiting, we can use the cache
        $this->assertEquals(1, $this->query->count());
    }

    public function testCount_fromResultWhenRange()
    {
        $this->setupLimit(0, 10);
        $this->setupQueryReturn(array(1));
        $this->query->toArray();

        // limiting start at 0 and request more elements than in the result, we can use the cache
        $this->assertEquals(1, $this->query->count());
    }

    public function testCount_notFromResult()
    {
        $this->setupLimit(1, 10);
        $this->setupQueryReturn(array(1));
        $this->setupCountReturn(1);
        $this->query->toArray();

        // limiting should invalidate the cache
        $this->assertEquals(1, $this->query->count());
    }

    public function testCount_notFromResultSameCount()
    {
        $this->setupLimit(0, 2);
        $this->setupQueryReturn(array(1, 2));
        $this->setupCountReturn(1);
        $this->query->toArray();

        // size and max size are the same, this should invalidate the cache
        $this->assertEquals(1, $this->query->count());
    }

    /**
     * @param array $return
     */
    private function setupQueryReturn($return)
    {
        $this->queryBuilder->shouldReceive('getQuery')
            ->once()
            ->andReturn($this->queryBuilder);

        $this->queryBuilder->shouldReceive('getResult')
            ->once()
            ->andReturn($return);
    }

    /**
     * @param int $return
     */
    private function setupCountReturn($return)
    {
        $this->queryBuilder->shouldReceive('select')
            ->once()
            ->andReturn($this->queryBuilder);

        $this->queryBuilder->shouldReceive('getQuery')
            ->once()
            ->andReturn($this->queryBuilder);

        $this->queryBuilder->shouldReceive('getSingleScalarResult')
            ->once()
            ->andReturn($return);
    }

    private function setupLimit($start=null, $limit=null)
    {
        $this->queryBuilder->shouldReceive('getFirstResult')
            ->zeroOrMoreTimes()
            ->andReturn($start);

        $this->queryBuilder->shouldReceive('getMaxResults')
            ->zeroOrMoreTimes()
            ->andReturn($limit);
    }


}
