<?php

namespace MistyDoctrine\Clause;

class FieldClause implements ClauseInterface
{
    // The name of the param that will trigger this clause
    private $paramName;

    // The name of the field this clause applys to
    private $fieldName;

    // The clause definition (e.g. equal to, not equal to, etc.)
    private $clauseDefinition;

    // The value used when the paramName is NOT present
    private $defaultValue;

    /**
     * Create a new FieldClause
     *
     * @param string $paramName The name of the param
     */
    public function __construct($paramName)
    {
        $this->paramName = $paramName;
    }

    /**
     * @see ClauseInterface
     */
    public function apply(array &$params)
    {
        if (array_key_exists($this->paramName, $params)) {

            $paramValue = $params[$this->paramName];
            unset($params[$this->paramName]);

            return call_user_func_array(
                array('MistyDoctrine\Clause\ClauseDefinition', $this->clauseDefinition),
                array($this->fieldName, $paramValue)
            );
        } elseif ($this->defaultValue !== null) {
            return call_user_func_array(
                array('MistyDoctrine\Clause\ClauseDefinition', $this->clauseDefinition),
                array($this->fieldName, $this->defaultValue)
            );
        }

        return null;
    }

    /**
     * Set the field name
     *
     * @param string $fieldName The name of the field this clause apply to
     * @return $this
     */
    public function field($fieldName)
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    /**
     * Set the default value
     *
     * @param string $defaultValue
     * @return $this
     */
    public function defaultTo($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * Check that the requested clause exist in ClauseDefinition
     *
     * @return FieldClause
     * @throws \BadMethodCallException
     */
    public function __call($clauseDefinition, $args)
    {
        if (!method_exists('MistyDoctrine\Clause\ClauseDefinition', $clauseDefinition)) {
            throw new \BadMethodCallException(sprintf(
                "Unknown method '%s' on class %s",
                $clauseDefinition,
                get_class()
            ));
        }

        $this->clauseDefinition = $clauseDefinition;
        return $this;
    }
}
