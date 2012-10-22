<?php

namespace MistyDoctrine\Clause;

class ClauseDefinition
{
    /**
     * Generate field = value SQL statement
     *
     * @param string $fieldName The name of the field
     * @param mixed $value String, number of null
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function equalTo($fieldName, $value)
    {
        if ($value === null)
        {
            return self::isNull($fieldName, $value);
        }
        else
        {
            return sprintf(
                '%s = %s',
                $fieldName,
                self::escapeAndQuote($value)
            );
        }
    }

    /**
     * Generate field != value SQL statement
     *
     * @param string $fieldName The name of the field
     * @param mixed $value String, number of null
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function notEqualTo($fieldName, $value)
    {
        if ($value === null)
        {
            return self::isNotNull($fieldName, $value);
        }
        else
        {
            return sprintf(
                '%s != %s',
                $fieldName,
                self::escapeAndQuote($value)
            );
        }
    }

    /**
     * Generate a field is not NULL or '' SQL statement
     *
     * @param string $fieldName The name of the field
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function notEmpty($fieldName)
    {
        return "( $fieldName IS NOT NULL AND $fieldName != '' )";
    }

    /**
     * Generate a field LIKE 'value%' SQL statement
     *
     * @param string $fieldName The name of the field
     * @param string $value The substring we want to match
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function startsWith($fieldName, $value)
    {
        return sprintf(
            "%s LIKE '%s%%'",
            $fieldName,
            self::escapeForLike($value)
        );
    }

    /**
     * Generate a field < value SQL statement
     *
     * @param string $fieldName The name of the field
     * @param mixed $value String or number
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function lessThan($fieldName, $value)
    {
        return sprintf(
            '%s < %s',
            $fieldName,
            self::escapeAndQuote($value)
        );
    }

    /**
     * Generate field > value SQL statement
     *
     * @param string $fieldName The name of the field
     * @param mixed $value String or number
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function moreThan($fieldName, $value)
    {
        return sprintf(
            '%s > %s',
            $fieldName,
            self::escapeAndQuote($value)
        );
    }

    /**
     * Generate field IS NULL SQL statement
     *
     * @param string $fieldName The name of the field
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function isNull($fieldName)
    {
        return "$fieldName IS NULL";
    }

    /**
     * Generate a field IS NOT NULL SQL statement
     *
     * @param string $fieldName The name of the field
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function isNotNull($fieldName)
    {
        return "$fieldName IS NOT NULL";
    }

    /**
     * Generate a field IN (...) SQL statement
     *
     * @param string $fieldName The name of the field
     * @param array $values Array of allowed values
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function in($fieldName, $values)
    {
        if (!is_array($values)) {
            return self::equalTo($fieldName, $values);
        }

        if (empty($values)) {
            // IN (empty-list) is the same as a always false clause
            return 'FALSE';
        }

        return sprintf(
            '%s IN (%s)',
            $fieldName,
            self::generateInOptions($values)
        );
    }

    /**
     * Generate a field NOT IN (...) SQL statement
     *
     * @param string $fieldName The name of the field
     * @param mixed[] $values Array of disallowed values
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function notIn($fieldName, $values)
    {
        if (!is_array($values)) {
            return self::notEqualTo($fieldName, $values);
        }

        if (empty($values)) {
            // NOT IN (empty-list) is the same as not having the clause
            return null;
        }

        return sprintf(
            '%s NOT IN (%s)',
            $fieldName,
            self::generateInOptions($values)
        );
    }

    /**
     * Generate a field = 1 SQL statement
     *
     * @param string $fieldName The name of the field
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function isTrue($fieldName)
    {
        return self::boolean($fieldName, true);
    }

    /**
     * Generate a field = 0 SQL statement
     *
     * @param string $fieldName The name of the field
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function isFalse($fieldName)
    {
        return self::boolean($fieldName, false);
    }

    /**
     * Generate a field = 1/0 SQL statement
     *
     * @param string $fieldName The name of the field
     * @param boolean $value
     * @return string SQL statement to be used in the WHERE clause
     */
    public static function boolean($fieldName, $value)
    {
        if ($value) {
            return "$fieldName = 1";
        } else {
            return "$fieldName = 0";
        }
    }

    /**
     * Take a list of values, and concatenate them with a comma
     *
     * @param mixed $values List of values
     * @return string value1,value2,value3...
     */
    private static function generateInOptions($values)
    {
        return implode(',', array_map(function($value){
            return self::escapeAndQuote($value);
        }, $values));
    }

    /**
     * Quote the value, if required
     *
     * @param mixed $value String or number
     * @return string Return the value, quoted if it's a string
     */
    private static function quote($value)
    {
        // addind quotes to non-numeric values
        if (!is_numeric($value)) {
            $value = "'$value'";
        }

        return $value;
    }

    /**
     * Escape a value to be used in an SQL query
     *
     * @param string $value
     * @return string SQL-safe value
     */
    private static function escape($value)
    {
        return mysql_real_escape_string($value);
    }

    /**
     * Escape and then quote a value
     *
     * @param string $value
     * @return string SQL-safe and quoted value
     */
    private static function escapeAndQuote($value)
    {
        return self::quote(
            self::escape($value)
        );
    }

    /**
     * Apply standard escaping and also replace % with \% and _ with \%
     *
     * @param string $value
     * @return string SQL-safe value for LIKE statements
     */
    private static function escapeForLike($value)
    {
        return str_replace('_', "\_",
            str_replace('%', "\%", self::escape($value))
        );
    }
}
