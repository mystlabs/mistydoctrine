<?php

class DatabaseTest extends MistyDoctrine\Test\DoctrineTest
{
	public function testCreateFromModelFolders()
	{
		// createFromModelFolders() is called by DoctrineTest, all we need to do
		// is check that the table was actually created

		$this->assertEquals(0, $this->connection->fetchColumn('SELECT COUNT(*) FROM cars'));

		$car = new Car();
		$car->model = 'whatever';
		$car->speed = 100;

		$this->entityManager->persist($car);
		$this->entityManager->flush();

		$this->assertEquals(1, $this->connection->fetchColumn('SELECT COUNT(*) FROM cars'));
	}

	public function testDropDatabase()
	{
		// dropDatabase() is called by DoctrineTest, all we need to do
		// is check that the tables have been empied
		$this->assertEquals(0, $this->connection->fetchColumn('SELECT COUNT(*) FROM cars'));
	}
}
