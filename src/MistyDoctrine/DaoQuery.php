<?php

namespace MistyDoctrine;

use Doctrine\ORM\QueryBuilder;

class DaoQuery extends QueryBuilder
{
	public function safeSelect( $table, $alias, array $joins = array())
	{
		return $this->selectOrCount( 'select', $table, $alias, $joins );
	}

	public function safeCount( $table, $alias, array $joins = array() )
	{
		return $this->selectOrCount( 'count', $table, $alias, $joins );
	}

	public function safeWhere( $where )
	{
		if( strlen( $where ) > 0 )
		{
			$this->where( $where );
		}
		return $this;
	}

	public function safeOrderBy( $orderBy, $defaulOrderBy )
	{
		$orderBy = $orderBy ? $orderBy : $defaulOrderBy;
		if( $orderBy )
		{
			$fields = explode( ",", $orderBy );
			foreach( $fields as $field )
			{
				$field = trim( $field );
				$direction = 'ASC';
				if( ( $index = strpos( $field, ' ' ) ) !== false )
				{
					$direction = substr( $field, $index+1 );
					$field = substr( $field, 0, $index );
				}

				$this->addOrderBy( $field, $direction );
			}
		}

		return $this;
	}

	public function safeLimit( $start=0, $limit )
	{
		if( $limit > 0 && $start >= 0 )
		{
			$this->setFirstResult( $start );
			$this->setMaxResults( $limit );
		}
		return $this;
	}

	private function selectOrCount( $action, $table, $alias, array $joins )
	{
		switch ( $action )
		{
			case 'select':
				$select = array( $alias );
				for( $i=0; $i< count( $joins ); $i=$i+2 )
				{
					$select[] = $joins[$i+1];
				}
				$this->select( $select );
				break;
			case 'count':
				$this->select( "COUNT( $alias )" );
				break;
			default:
				throw new \Exception( "Unknown action '$action' in " . get_class() );
		}

		$this->from( $table, $alias );

		$select = array( $alias );
		for( $i=0; $i< count( $joins ); $i=$i+2 )
		{
			$joinModel = $joins[$i];
			$joinAlias = $joins[$i+1];

			$select[] = $joinAlias;
			$this->leftJoin( $joinModel, $joinAlias );
		}

		return $this;
	}

}