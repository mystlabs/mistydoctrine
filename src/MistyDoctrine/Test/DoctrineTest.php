<?php

namespace MistyDoctrine\Test;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

use MistyDoctrine\Dao;
use MistyDoctrine\Database;
use MistyTesting\UnitTest;

class DoctrineTest extends UnitTest
{
    protected $entityManager;

    /** @var \Doctrine\DBAL\Connection */
    protected $connection;
    protected $dao;

    public function initTest()
    {
        $this->loadConfigurationFile();

        $paths = get_model_paths();
        $params = get_db_params();

        $config = Setup::createAnnotationMetadataConfiguration($paths, true);
        $this->entityManager = EntityManager::create($params, $config);
        $this->connection = $this->entityManager->getConnection();
        $this->dao = new Dao($this->entityManager);

        $database = new Database( $this->entityManager );
        $database->dropDatabase();
        $database->createFromModelFolders($paths);
    }

    public function clearTest()
    {

    }

    protected function assertRecordCount($expectedCount, $table)
    {
        $this->assertEquals(
            $expectedCount,
            $this->connection->fetchColumn(
                'SELECT COUNT(*) FROM ' . $table
            )
        );
    }

    private function loadConfigurationFile()
    {
        $object = new \ReflectionObject($this);
        $classFilename = $object->getFilename();
        $parentFolder = dirname($classFilename);

        do {
            if (file_exists($parentFolder . '/bootstrap.php')) {
                require_once $parentFolder . '/bootstrap.php';
                break;
            }

            $scannedFolder = $parentFolder;
            $parentFolder = dirname($scannedFolder);

        } while ($scannedFolder !== $parentFolder);

        if(!function_exists('get_db_params'))
        {
            $this->fail('You have to create the file bootstrap.php in tests/ and define the function "get_db_params"');
        }

        if(!function_exists('get_model_paths'))
        {
            $this->fail('You have to create the file bootstrap.php in tests/ and define the function "get_model_paths"');
        }
    }
}
