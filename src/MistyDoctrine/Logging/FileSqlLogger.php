<?php

namespace Mist\DataAccess;

use Doctrine\DBAL\Logging\SQLLogger;

class FileSqlLogger implements SQLLogger
{
    private $file;
    private $handler;

    private $start = null;
    private $sql = null;
    private $params;
    private $count = 0;

    /**
     * @param string $logFolder
     * @throws \InvalidArgumentException If the folder doesn't exist or is not writable
     */
    public function __construct($logFolder)
    {
        $this->file = sprintf(
            '%s/%s.queries',
            $logFolder,
            date("Y-m-d")
        );
    }

    /**
     * Register that the query is going to be executed
     *
     * @param string $sql
     * @param array $params
     * @param array $types
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->initialize();

        $this->start = microtime(true);
        $this->sql = $sql;
        $this->params = $params;
        $this->count++;
    }

    /**
     * Write the query to disk
     */
    public function stopQuery()
    {
        fwrite($this->handler, sprintf(
            '[%d] %ss - %s - %s',
            $this->count,
            round(microtime(true) - $this->start, 6),
            $this->sql,
            json_encode($this->params)
        ));

        $this->sql = null;
    }

    /**
     * Deconstruct the object. If we have a query reference it means that it failed
     */
    public function __destruct()
    {
        if ($this->sql) {
            fwrite($this->handler, sprintf(
                '[%d] FAILED QUERY - %s - %s',
                $this->count,
                $this->sql,
                json_encode($this->params)
            ));
        }
    }

    /**
     * Open the log file only when we have to write something
     *
     * @throws \InvalidArgumentException
     */
    private function initialize()
    {
        if ($this->handler === null) {
            $this->handler = fopen($this->file, 'a+');

            if (!$this->handler) {
                throw new \InvalidArgumentException(sprintf(
                    'Cannot open the queries log file: %s',
                    $this->file
                ));
            }
        }

        fwrite($this->handler, "\n\n");
    }
}