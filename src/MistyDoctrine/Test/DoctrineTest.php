<?php

namespace MistyDoctrine\Test;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

use MistyDoctrine\Database;
use MistyTesting\UnitTest;

include __DIR__ . '/../../../tests/bootstrap.php';

class DoctrineTest extends UnitTest
{
	protected $entityManager;

	public function initTest()
	{
		if(!function_exists('get_db_params'))
		{
			$this->fail('You have to create the file bootstrap.php in tests/ and define the function "get_db_params"');
		}

		if(!function_exists('get_model_paths'))
		{
			$this->fail('You have to create the file bootstrap.php in tests/ and define the function "get_model_paths"');
		}

		$paths = get_model_paths();
		$params = get_db_params();

		$config = Setup::createAnnotationMetadataConfiguration($paths, true);
		$this->entityManager = EntityManager::create($params, $config);

		$database = new Database( $this->entityManager );
		$database->dropDatabase();
		$database->createFromModelFolders($paths);
	}

	public function clearTest()
	{

	}
}