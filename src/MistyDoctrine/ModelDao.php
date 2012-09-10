<?php

namespace MistyDoctrine;

use MistyUtils\Validate;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder; //

class ModelDao extends Dao
{
	protected $model;
	protected $prefix;
	protected $idField;

	protected $daoHelper;

	public function __construct( EntityManager $entityManager, $model, $prefix, $idField='id' )
	{
		parent::__construct( $entityManager );

		$this->model = $model;
		$this->prefix = $prefix;
		$this->idField = $idField;

		$this->daoHelper = new DaoHelper();
	}

	public function reference( $id )
	{
		return $id ? $this->entityManager->getReference( $this->model, $id ) : null;
	}

	public function find( $id, array $joins=array() )
	{
		return $this->findBy( $this->idField, $id, $joins );
	}

	public function get( $id, array $joins=array() )
	{
		return $this->getBy( $this->idField, $id, $joins );
	}

	public function getBy( $field, $value, array $joins=array() )
	{
		$object = $this->findBy( $field, $value, $joins );

		return Validate::notNull( $object, "Impossibile trovare l'oggetto $field#$value in " . get_class( $this ), 'MistyDoctrine\Exception\EntityNotFoundException' );
	}

	public function findBy( $field, $value, array $joins=array() )
	{
		return $this->newQuery()
			->safeSelect( $this->model, $this->prefix, $joins )
			->where( $this->prefix . ".$field = '$value'" )
			->getQuery()
			->getOneOrNullResult();
	}

	public function getAll( array $where=array(), $orderBy=false, $start=0, $limit=false )
	{
		return $this->getAllJ( $where, array(), $orderBy, $start, $limit );
	}

	public function getAllJ( array $where=array(), array $joins=array(), $orderBy=false, $start=0, $limit=false )
	{
		return $this->newQuery()
			->safeSelect( $this->model, $this->prefix, $joins )
			->safeWhere( $this->parseWhere( $where ) )
			->safeOrderBy( $orderBy, $this->prefix . '.' . $this->idField )
			->safeLimit( $start, $limit )
			->getQuery()
			->getResult();
	}

	public function count( array $where=array(), array $joins=array() )
	{
		return $this->newQuery()
			->safeCount( $this->model, $this->prefix, $joins )
			->safeWhere( $this->parseWhere( $where ) )
			->getQuery()
			->getSingleScalarResult();
	}

	public function save( Model $entity )
	{
		// TODO this require more thoughts!
		$this->entityManager->persist( $entity );
		return $entity;
	}

	public function delete( Model $entity  )
	{
		$this->entityManager->remove( $entity );
	}

	public function deleteById( $id )
	{
		return $this->deleteBy( $this->idField, $id );
	}

	public function deleteBy( $field, $value )
	{
		// TODO siam sicuri di volere un metodo del genere? e come gestiamo le dipendenze?
		return $this->newQuery()
			->delete()
			->from( $this->model, $this->prefix )
			->where( $this->prefix . ".$field = '$value'" )
			->getQuery()
			->execute();
	}

	protected function newQuery()
	{
		return new DaoQuery( $this->entityManager );
	}

	protected function parseSelect( $select )
	{
		return strlen( $select ) > 0 ? $select : $this->prefix;
	}

	protected function parseWhere( array $params )
	{
		$where = array();
		$this->daoHelper->reset( $where, $params );
		$this->where( $where, $params );

		Validate::isTrue( is_array( $where ), "Where must be an array in " . get_class($this) );
		Validate::isTrue( empty( $params ), "Unimplemented param/s in " . get_class($this) . ": " . implode( ", ", array_keys( $params ) ) );

		return implode( " AND ", $where );
	}

	protected function where( array &$where, array &$params ){}

}
