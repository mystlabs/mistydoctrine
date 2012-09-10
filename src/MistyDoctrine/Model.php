<?php

namespace MistyDoctrine;

use MistyUtils\StringUtil;
use MistyDoctrine\Exception\ModelException;

abstract class Model
{
    /**
     * Get the value of the property, given priority to the getter
     * e.g. getFirstName for the property first_name
     */
    public function __get( $name )
    {
        $getterMethod = "get" . self::functionify( $name );
        if( method_exists( $this, $getterMethod ) )
        {
            return $this->$getterMethod();
        }

        if( property_exists( $this, $name ) )
        {
            return $this->$name;
        }

        throw new ModelException(sprintf(
            'The property %s does not exist on the model %s',
            $name,
            get_class($this)
        ));
    }

    /**
     * Set the value of the property, given priority to the setter
     * e.g. setFirstName for the property first_name
     * Cannot be used to set object references
     */
    public function __set( $name, $value )
    {
        if( StringUtil::endsWith( $name, '_id' ) )
        {
            throw new ModelException(sprintf(
                'Object references should not be updated directly: %s',
                $name
            ));
        }

        $setterMethod = "set" . self::functionify( $name );
        if( method_exists( $this, $setterMethod ) )
        {
            return $this->$setterMethod( $value );
        }

        if( property_exists( $this, $name ) )
        {
            $this->$name = $value;
            return;
        }

        throw new ModelException(sprintf(
            'The property %s does not exist on the model %s',
            $name,
            get_class($this)
        ));
    }

    public function __call( $name, $args )
    {
        if (StringUtil::startsWith($name, 'set') && !StringUtil::endsWith($name, 'Id'))
        {
            $property = strtolower(preg_replace('/([a-z])([A-Z])/', '${1}_${2}', substr($name, 3)));
            if (property_exists( $this, $property ) && count($args) == 1)
            {
                $this->$property = $args[0];
                return $this;
            }
        }

        throw new ModelException(sprintf(
            'The method %s does not exist on the model %s, and cannot be magically created',
            $name,
            get_class($this)
        ));
    }

    /**
     * Returns an array with all the properties of this object
     */
    public function toArray()
    {
        return get_object_vars( $this );
    }

    /**
     * Overrides all the properties with the values of the array
     */
    public function setArray( array $values )
    {
        foreach( $values as $key => $value )
        {
            $this->__set( $key, $value );
        }
    }

    /**
     * Generic static constructor, useful for chainability
     */
    public static function create()
    {
        $className = get_called_class();
        return new $className();
    }

    /**
     * Uppercase the first letters, and remove _
     * e.g. first_name to FirstName
     */
    public static function functionify( $name )
    {
        $pieces = explode( "_", $name );
        foreach( $pieces as $key => $piece )
        {
            $pieces[$key] = ucfirst( $piece );
        }

        return implode( "", $pieces );
    }
}
