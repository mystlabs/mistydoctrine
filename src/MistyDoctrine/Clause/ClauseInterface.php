<?php

namespace MistyDoctrine\Clause;

interface ClauseInterface
{
    /**
     * Take an associative array of clause name => clause value, and if possible tranform them into
     * SQL clauses. This method MUST unset the clause(s) processed
     *
     * @param array $params
     * @return string|null The transformed clause or null if it can't do anything
     */
    function apply(array &$params);
}
