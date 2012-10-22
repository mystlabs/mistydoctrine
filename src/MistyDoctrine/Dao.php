<?php

namespace MistyDoctrine;

use MistyUtils\Validate;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\AbstractQuery;

class Dao
{
    const DATE_FORMAT = "Y-m-d H:i:s";

    protected $entityManager;
    protected $connection;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->connection = $entityManager->getConnection();
    }

    /**
     * Check if there already is an active transaction
     */
    public function hasActiveTransaction()
    {
        return $this->connection->getTransactionNestingLevel() > 0;
    }

    /**
     * Start a transaction
     */
    public function start()
    {
        Validate::isTrue($this->entityManager->isOpen(), 'EntityManager is closed');
        $this->connection->beginTransaction(); // suspend auto-commit
    }

    /**
     * Write all data to the database
     */
    public function flush()
    {
        $this->entityManager->flush();
    }

    /**
     * Close the transaction
     */
    public function commit()
    {
        Validate::isTrue($this->entityManager->isOpen(), 'EntityManager is closed');
        $this->entityManager->flush();
        $this->connection->commit();
    }

    /**
     * Rollback the transaction
     */
    public function rollback()
    {
        $this->connection->rollback();
        $this->entityManager->close();
    }

    /**
     * Execute an UPDATE SQL query
     */
    protected function executeSqlUpdate($sql, array $params = array())
    {
        $this->connection->executeUpdate($sql, $params);
    }

    /**
     * Select a single scalar value
     */
    protected function executeSqlGetScalar($sql, array $params = array())
    {
        return $this->connection->fetchColumn($sql, $params);
    }

    /**
     * Select all rows and return an associative array
     */
    protected function executeSqlGetAll($sql, array $params = array(), $offset = null, $limit = null)
    {
        if ($offset !== null) $sql .= " LIMIT $offset";
        if ($offset !== null && $limit !== null) $sql .= ", $limit";

        return $this->connection->fetchAll($sql, $params);
    }

    /**
     * Execute an DQL UPDATE query
     */
    protected function executeDqlUpdate($dql, array $params = array())
    {
        $this->entityManager->createQuery($dql)->execute($params);
    }

    /**
     * Execute a SQL SELECT query and return a single scalar
     */
    protected function executeDqlGetScalar($dql, array $params = array())
    {
        return $this->entityManager->createQuery($dql)->execute($params, AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Execute a SELECT DQL query, and return an array with the results
     */
    protected function executeDqlGetAll($dql, array $params = array(), $offset = null, $limit = null)
    {
        $query = $this->entityManager->createQuery($dql);

        if ($offset !== null) {
            $query->setFirstResult($offset);
        }

        if ($limit !== null) {
            $query->setMaxResults($limit);
        }

        return $query->execute($params, AbstractQuery::HYDRATE_ARRAY);
    }
}
