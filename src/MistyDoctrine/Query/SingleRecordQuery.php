<?php

namespace MistyDoctrine\Query;

use MistyDoctrine\Clause\ClauseDefinition;
use MistyDoctrine\Exception\EntityNotFoundException;
use MistyDoctrine\Exception\QueryException;
use MistyDoctrine\Model;

class SingleRecordQuery extends AbstractQuery
{
    /**
     * Commodity method to lookup an item by a field. It can be concatenated to lookup lookup by multifle fields
     *
     * @param mixed $value The value
     * @param string $fieldName The name of the field, default to the id of the object
     * @return SingleRecordQuery
     */
    public function by($value, $fieldName = null)
    {
        $this->assertCanBeModified();

        if ($fieldName === null ) {
            $fieldName = sprintf(
                '%s.%s',
                $this->metadata['prefix'],
                $this->metadata['idField']
            );
        }

        $this->queryBuilder->andWhere(ClauseDefinition::equalTo($fieldName, $value));
        return $this;
    }

    /**
     * Execute the query and return a single result or null
     *
     * @return Model
     * @throws QueryException If there is more than one results
     */
    public function find()
    {
        if ($this->result === false) {
            $this->result = $this->queryBuilder
                ->getQuery()
                ->getOneOrNullResult();
        }

        return $this->result;
    }

    /**
     * Execute the query and return a single result or throw an exception
     *
     * @return Model
     * @throws QueryException If there is more than one results
     * @throws EntityNotFoundException If it cannot find the entity
     */
    public function get()
    {
        $record = $this->find();
        if ($record === null ) {
            throw new EntityNotFoundException(sprintf(
                'Could not find the requested entity'
            ));
        }
        return $record;
    }

    /**
     * @param array $clauses
     * @return SingleRecordQuery
     */
    public function where(array $clauses)
    {
        return parent::where($clauses);
    }
}
