<?php

namespace MistyDoctrine\Clause;

use MistyDoctrine\Exception\QueryException;

class ClauseParser
{
    /** @var ClauseInterface[] */
    private $clauses;

    /**
     * @param ClauseInterface[] $clauses
     */
    public function __construct($clauses = array())
    {
        $this->clauses = $clauses;
    }

    /**
     * Create a new FieldClause, add it to the list of the supported clauses and
     * return the definition object
     *
     * @param string $paramName The name of the param that will trigger this clause
     * @return FieldClause
     */
    public function param($paramName)
    {
        $clause = new FieldClause($paramName);
        $this->clauses[] = $clause;

        return $clause;
    }

    /**
     * Shortcut for setting param name and field name in a single go
     *
     * @param string $paramName
     * @param string $fieldName
     * @return FieldClause
     */
    public function add($paramName, $fieldName)
    {
        return $this->param($paramName)->field($fieldName);
    }

    /**
     * Create a new CustomClause and add it to the list of the supported clauses
     *
     * @param Callable $callable The function to apply
     */
    public function custom($callable)
    {
        $this->clauses[] = new CustomClause($callable);
    }

    /**
     * Convert a list of clause params to an SQL where statement
     *
     * @param array $params List of clause params
     * @return string SQL statement or empty string if there are no clauses
     * @throws QueryException If one or more of the clauses cannot be parsed
     */
    public function parse(array $params)
    {
        $sqlClauses = array();
        foreach ($this->clauses as $clause) {
            $sqlClause = $clause->apply($params);
            if ($sqlClause !== null) {
                $sqlClauses[] = $sqlClause;
            }
        }

        if (count($params) !== 0) {
            // If we are here it means that one of the requested clause param has not been defined
            throw new QueryException(sprintf(
                'Unsupposed clause(s): %s',
                implode(', ', array_keys($params))
            ));
        }

        if (count($sqlClauses) > 0) {
            return implode(' AND ', $sqlClauses);
        }

        return '';
    }
}
