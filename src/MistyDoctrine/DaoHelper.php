<?php

namespace MistyDoctrine;

use MistyUtils\Validate;

class DaoHelper
{
	private $where;
	private $params;

	public function reset( array &$where, array &$params )
	{
		$this->where = &$where;
		$this->params = &$params;
	}

	public function equalTo( $key, $fieldName )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			$value = $this->params[$key];
			unset( $this->params[$key] );

			if( $value === null )
			{
				$this->where[] = " $fieldName IS NULL";
			}
			elseif( is_numeric( $value ) )
			{
				$this->where[] = " $fieldName = {$value}";
			}
			else
			{
				$this->where[] = " $fieldName = '{$value}'";
			}
		}
	}

	public function startsWith( $key, $fieldName )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			$this->where[] = " $fieldName LIKE '{$this->params[$key]}%'";
			unset( $this->params[$key] );
		}
	}


	public function lessThan( $key, $fieldName )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			$value = $this->params[$key];
			if( is_numeric( $value ) )
			{
				$this->where[] = " $fieldName < {$value}";
			}
			else
			{
				$this->where[] = " $fieldName < '{$value}'";
			}
			unset( $this->params[$key] );
		}
	}

	public function moreThan( $key, $fieldName )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			$value = $this->params[$key];
			if( is_numeric( $value ) )
			{
				$this->where[] = " $fieldName > {$value}";
			}
			else
			{
				$this->where[] = " $fieldName > '{$value}'";
			}
			unset( $this->params[$key] );
		}
	}

	public function isNull( $key, $fieldName )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			$this->where[] = " $fieldName IS NULL";
			unset( $this->params[$key] );
		}
	}

	public function isNotNull( $key, $fieldName )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			$this->where[] = " $fieldName IS NOT NULL";
			unset( $this->params[$key] );
		}
	}

	public function in( $key, $fieldName, $emptyToken=-1 )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			$values = empty( $this->params[$key] ) ? $emptyToken : $this->params[$key];
			$inStatement = $this->generateInStatement( $values );
			$this->where[] = " $fieldName IN $inStatement";
			unset( $this->params[$key] );
		}
	}

	public function notIn( $key, $fieldName )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			if( !empty( $this->params[$key] ) )
			{
				$inStatement = $this->generateInStatement( $this->params[$key] );
				$this->where[] = " $fieldName NOT IN $inStatement";
			}
			unset( $this->params[$key] );
		}
	}

	public function isTrue( $key, $fieldName )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			$this->where[] = " $fieldName = 1";
			unset( $this->params[$key] );
		}
	}

	public function boolean( $key, $fieldName )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			if( $this->params[$key] )
			{
				$this->where[] = " $fieldName = 1";
			}
			else
			{
				$this->where[] = " $fieldName = 0";
			}
			unset( $this->params[$key] );
		}
	}

	public function defaultToFalse( $key, $fieldName )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			unset( $this->params[$key] );
		}
		else
		{
			$this->where[] = " $fieldName = 1";
		}
	}

	public function isNotEmpty( $key, $fieldName )
	{
		if( array_key_exists( $key, $this->params ) )
		{
			$this->where[] = " ( $fieldName IS NOT NULL AND $fieldName != '' ) ";
			unset( $this->params[$key] );
		}
	}

	private function generateInStatement( $values )
	{
		if( !is_array( $values ) )
		{
			$values = array( $values );
		}
		Validate::notEmpty( $values );

		$typeSafeValues = array();
		foreach( $values as $value )
		{
			if( is_numeric( $value ) && $value >= 0 )
			{
				$typeSafeValues[] = $value;
			}
			else
			{
				$typeSafeValues[] = "'" . $value . "'";
			}
		}

		return "(" . implode( ",", $typeSafeValues ) . ")";
	}

}