<?php

namespace MistyDoctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use MistyDoctrine\Query\SingleRecordQuery;
use MistyDoctrine\Query\MultipleRecordsQuery;
use MistyDoctrine\Clause\ClauseParser;
use MistyDoctrine\Exception\QueryException;

abstract class ModelDao extends Dao
{
    /** @var string */
	protected $model;

    /** @var string */
    protected $prefix;

    /** @var string */
    protected $idField;

    /** @var ClauseParser */
	protected $clauseParser;

	/**
	 * Create a new ModelDao
	 *
	 * @param EntityManager $entityManager The doctrine entity manager
	 * @param string $model The name of the model. If missing it will be inferred by the name of the dao
	 * @param string $prefix The prefix to use in the queries. If missing it will be inferred by the model
	 * @param string $idField The name of the ID field, default 'id'
	 */
	public function __construct(EntityManager $entityManager, $model=null, $prefix=null, $idField='id')
	{
		parent::__construct($entityManager);

		$this->model = $model ? $model : $this->getModelName();
		$this->prefix = $prefix ? $prefix : $this->getPrefix();
		$this->idField = $idField;

		$this->clauseParser = $this->getClauseParser();
	}

    /**
     * Create a new Reference object for the given id, this will save a query to the db
     *
     * @param int $id
     * @return Model
     */
    public function reference($id)
	{
		return $this->entityManager->getReference($this->model, $id);
	}

    /**
     * Create a new query to select multiple results
     *
     * @return Query\MultipleRecordsQuery
     */
    public function all()
	{
        return new MultipleRecordsQuery(
			$this->newQueryBuilder(),
			$this->clauseParser,
            $this->buildMetadata()
		);
	}

    /**
     * Create a new query to select a single record
     *
     * @return Query\SingleRecordQuery
     */
    public function one()
	{
        return new SingleRecordQuery(
            $this->newQueryBuilder(),
            $this->clauseParser,
            $this->buildMetadata()
        );
	}

    /**
     * Save an entity. The database record will be created at the next flush/commit
     *
     * @param Model $entity
     * @return Model
     * @throws \MistyDoctrine\Exception\QueryException
     */
	public function save($entity)
	{
        if (!$entity instanceof $this->model) {
            throw new QueryException(sprintf(
                'Cannot save entity, expected %s but got %',
                $this->model,
                get_class($entity)
            ));
        }

		$this->entityManager->persist($entity);
		return $entity;
	}

    /**
     * Remove an entity. The database record will be deleted at the next flush/commit
     *
     * @param Model $entity
     * @throws Exception\QueryException
     */
    public function delete($entity)
	{
		if (!$entity instanceof $this->model) {
			throw new QueryException(sprintf(
                'Cannot delete entity, expected %s but got %',
                $this->model,
                get_class($entity)
            ));
		}

		$this->entityManager->remove($entity);
	}

	/**
	 * Create a new query builder, and default FROM to the model of this Dao
	 *
	 * @return QueryBuilder
	 */
	protected function newQueryBuilder()
	{
		$queryBuilder = new QueryBuilder($this->entityManager);
        $queryBuilder->select($this->prefix);
		$queryBuilder->from($this->model, $this->prefix);

		return $queryBuilder;
	}

	/**
	 * Get the ClauseParser for this Dao. If not overridden by a child class, this will return an empty
	 * ClauseParser, which means that this Dao won't accept any WHERE clause
	 *
	 * @return ClauseParser
	 */
	protected function getClauseParser()
	{
		return new ClauseParser();
	}

	/**
	 * Extract the name of the model from the name of this Dao
	 *
	 * @return string The name of the model
	 */
	private function getModelName()
	{
		return str_replace(
			'\Dao\\',
			'\Model\\',
			substr(get_class($this), 0, -3)
		);
	}

	/**
	 * Extract the prefix from the name of the model
	 *
	 * @return string The prefix to use in queries
	 */
	private function getPrefix()
	{
		$matches = array();
		preg_match_all(
			'/([A-Z])/',
			substr($this->model, strrpos($this->model, '\\')),
			$matches
		);

		return strtolower(implode('', $matches[0]));
	}

    private function buildMetadata()
    {
        return array(
            'prefix' => $this->prefix,
            'model' => $this->model,
            'idField' => $this->idField,
        );
    }
}
