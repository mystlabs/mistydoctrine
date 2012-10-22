<?php

namespace MistyDoctrine\Query;

use Doctrine\ORM\QueryBuilder;
use MistyDoctrine\Clause\ClauseParser;
use MistyDoctrine\Exception\QueryException;
use MistyDoctrine\Model;

class AbstractQuery
{
    /** @var QueryBuilder */
    protected $queryBuilder;

    /** @var ClauseParser */
    protected $clauseParser;

    /** @var array */
    protected $metadata;

    /** @var object|array false indicates that the db hasn't been queried yet */
    protected $result = false;

    /**
     * Create a new query
     *
     * @param QueryBuilder $queryBuilder
     * @param ClauseParser $clauseParser
     * @param array $metadata
     */
    public function __construct($queryBuilder, $clauseParser, array $metadata)
    {
        $this->queryBuilder = $queryBuilder;
        $this->clauseParser = $clauseParser;
        $this->metadata = $metadata;
    }

    /**
     * @param $models
     * @return AbstractQuery
     */
    public function select($models)
    {
        $this->assertCanBeModified();

        $this->queryBuilder->select($models);

        return $this;
    }

    /**
     * @param array $joins
     * @return AbstractQuery
     */
    public function join(array $joins)
    {
        $this->assertCanBeModified();

        foreach ($joins as $attribute => $prefix) {
            $this->queryBuilder->join($attribute, $prefix);
        }

        return $this;
    }

    /**
     * Parse the clauses, and add them to the query builder
     *
     * @param array $clauses
     * @return AbstractQuery
     * @throws QueryException If the results have already been materialized
     */
    public function where(array $clauses)
    {
        $this->assertCanBeModified();

        $this->queryBuilder->andWhere(
            $this->clauseParser->parse($clauses)
        );

        return $this;
    }

    /**
     * Assert that the query hasn't been materialized yet, or throw an exception if it has
     *
     * @throws QueryException
     */
    protected function assertCanBeModified()
    {
        if ($this->result !== false) {
            throw new QueryException(
                'The query cannot be modified after it has been executed. Clone the object if you want to reuse the query'
            );
        }
    }
}
