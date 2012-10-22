<?php

namespace MistyDoctrine\Clause;

class CustomClause implements ClauseInterface
{
    private $callable;

    /**
     * Create a clause that will apply a function to the params. This can be used to impose the presence
     * of a param, create complex clauses or apply some default clause to all queries
     *
     * @param Callable $callable A callable function to apply to the param
     */
    public function __construct($callable)
    {
        $this->callable = $callable;
    }

    /**
     * @see ClauseDefinition
     */
    public function apply(array &$params)
    {
        $func = $this->callable;

        return $func($params);
    }
}
