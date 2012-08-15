<?php

namespace MistyDoctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

use MistyUtils\ClassUtil;
use MistyUtils\StringUtil;

class Database
{
	protected $entityManager;
	protected $schemaTool;

	public function __construct( EntityManager $entityManager )
	{
		$this->entityManager = $entityManager;
		$this->schemaTool = new SchemaTool( $entityManager );
	}

	/**
	 * Drop the database the EntityManager is connected to
	 */
	public function dropDatabase()
	{
		$this->schemaTool->dropDatabase();
	}

	/**
	 * Create the database based on the given models
	 */
	public function createFromModels( array $models )
	{
		$metadata = array();
		foreach( $models as $model )
		{
			$metadata[] = $this->entityManager->getClassMetadata( $model );
		}

		$this->schemaTool->createSchema( $metadata );
	}

	/**
	 * Scan all the given folders (not recursive), and extract models
	 * This function expects all the php files in the folders to be models,
	 * if this is not true for your application generate the list of models
	 * and call createFromModels() instead
	 */
	public function createFromModelFolders( array $folders )
	{
		$models = array();
		foreach( $folders as $folder )
		{
			$files = scandir( $folder );
			foreach( $files as $file )
			{
				$path = "$folder/$file";
				if( is_file( $path ) && StringUtil::endsWith( $file, ".php" ) )
				{
					require_once $path;
					$models = array_merge( $models, self::extractClassFromFile( $path ) );
				}
			}
		}

		return $this->createFromModels( $models );
	}

	/**
	 * Very naive way to get the classes defined in a file
	 */
	public static function extractClassFromFile( $file )
	{
		$code = file_get_contents( $file );

		return ClassUtil::extractClassesFromText( $code );
	}
}
