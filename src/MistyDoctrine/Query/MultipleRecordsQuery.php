<?php

namespace MistyDoctrine\Query;

class MultipleRecordsQuery extends AbstractQuery
{
    protected $count = false;

    protected $hasRange = false;

    /**
     * @param array $clauses
     * @return MultipleRecordsQuery
     */
    public function where(array $clauses)
    {
        return parent::where($clauses);
    }

    /**
     * Apply ordering to the query
     *
     * @param string $field The name of the field
     * @param string $direction ASC or DESC
     * @return MultipleRecordsQuery
     */
    public function orderBy($field, $direction)
    {
        $this->assertCanBeModified();

        $this->queryBuilder->addOrderBy($field, $direction);

        return $this;
    }

    /**
     * Set a limit on the query
     *
     * @param int $start
     * @param int $limit
     * @return MultipleRecordsQuery
     */
    public function limit($start, $limit = null)
    {
        $this->assertCanBeModified();

        $this->hasRange = true;
        $this->queryBuilder->setFirstResult($start);

        if ($limit) {
            $this->queryBuilder->setMaxResults($limit);
        }

        return $this;
    }

    /**
     * Return an array of results
     * This will issue a new query only if strictly necessary
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->result === false ){
            $this->result = $this->queryBuilder
                ->getQuery()
                ->getResult();
        }

        return $this->result;
    }

    /**
     * Count the number of results after applying where
     * This will issue a new query only if strictly necessary
     *
     * @return int
     */
    public function count()
    {
        if ($this->count === false) {

            $this->count = $this->countFromResult();
            if ($this->count === null ){
                $this->count = $this->queryBuilder
                    ->select(sprintf(
                        'COUNT (%s.%s)',
                        $this->metadata['prefix'],
                        $this->metadata['idField']
                    ))
                    ->getQuery()
                    ->getSingleScalarResult();
            }
        }

        return $this->count;
    }

    /**
     * Transform the result set using array_map
     *
     * @param callable $callable
     * @return array
     */
    public function map($callable)
    {
        return array_map($callable, $this->toArray());
    }

    /**
     * Transform the result set using array_filter
     *
     * @param callable $callable
     * @return array
     */
    public function filter($callable)
    {
        return array_filter($this->toArray(), $callable);
    }

    /**
     * Try to extract the count from the result instead of doing a new query
     *
     * @return int|null
     */
    private function countFromResult()
    {
        if ($this->result === false) {
            // We haven't executed the query yet
            return null;
        }

        if ($this->queryBuilder->getFirstResult() > 0) {
            // We have executed the query, but we only have a subset of the results
            return null;
        }

        if ((int)$this->queryBuilder->getMaxResults() === count($this->result)) {
            // We have executed the query, but we (probably) only have a subset of the results
            return null;
        }

        return count($this->result);
    }

}
