<?php
namespace Czim\DataObject;

use ArrayObject;
use Czim\DataObject\Contracts\DataObjectInterface;
use Czim\DataObject\Exceptions\UnassignableAttributeException;
use Czim\DataObject\Traits\ValidatableTrait;

abstract class AbstractDataObject extends ArrayObject implements DataObjectInterface
{
    use ValidatableTrait;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * The validation rules to apply to the attributes
     *
     * @var array
     */
    protected $rules = [];

    /**
     * List of keys that limit for which keys values may be assigned
     * If empty, allows any key to be assigned.
     *
     * @var array|null
     */
    protected $assignable;

    /**
     * Whether to allow magic assignment of properties
     *
     * @var bool
     */
    protected $magicAssignment = true;

    /**
     * Construct with attributes
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setAttributes($attributes);
    }

    /**
     * Checks whether attribute key(s) may be assigned values
     *
     * @param string|array $attribute   array key name or array of key names
     * @throws UnassignableAttributeException
     */
    protected function checkAttributeAssignable($attribute)
    {
        if (empty($this->assignable)) return;

        if (is_array($attribute)) {

            foreach ($attribute as $singleAttribute) {
                $this->checkAttributeAssignable($singleAttribute);
            }

            return;
        }

        if ( ! in_array($attribute, $this->assignable)) {
            throw new UnassignableAttributeException("Not allowed to assign value for '{$attribute}'");
        }
    }

    // ------------------------------------------------------------------------------
    //      Simple Getting/Setting
    // ------------------------------------------------------------------------------

    /**
     * Get attribute
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->getAttributeValue($key);
    }

    /**
     * Get a plain attribute
     *
     * @param string $key
     * @return mixed
     */
    protected function getAttributeValue($key)
    {
        return (isset($this->attributes[$key])) ? $this->attributes[$key] : null;
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get attribute
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $this->checkAttributeAssignable($key);

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Mass assignment of attributes
     *
     * @param array $attributes associative
     */
    public function setAttributes(array $attributes)
    {
        $this->checkAttributeAssignable(array_keys($attributes));

        $this->setRawAttributes($attributes);
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param array $attributes
     */
    public function setRawAttributes(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }


    // ------------------------------------------------------------------------------
    //      Magic Getting/Setting
    // ------------------------------------------------------------------------------

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function &__get($key)
    {
        // old approach was to use getAttribute($key),
        // but that does not allow direct modification of arrays
        // by creating new entries: object->array[] = ...

        // always defaults to null if not set
        if ( ! array_key_exists($key, $this->attributes)) {
            $this->attributes[$key] = null;
        }

        return $this->attributes[$key];
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        if ( ! $this->magicAssignment) {
            throw new UnassignableAttributeException("Not allowed to assign value by magic");
        }

        $this->checkAttributeAssignable($key);

        $this->setAttribute($key, $value);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return (isset($this->attributes[$key]) && ! is_null($this->getAttributeValue($key)));
    }

    /**
     * Unset an attribute on the model.
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }


    // ------------------------------------------------------------------------------
    //      Conversion and Array Access
    // ------------------------------------------------------------------------------

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        if ( ! isset($this->attributes)) {
            return null;
        }

        // make this work recursively
        $array = [];

        foreach ($this->attributes as $key => $attribute) {

            if (is_a($attribute, DataObjectInterface::class)) {
                $attribute = $attribute->toArray();
            }

            $array[$key] = $attribute;
        }

        return $array;
    }

    /**
     * @param bool $recursive
     * @return object
     */
    public function toObject($recursive = true)
    {
        if ($recursive) {
            return $this->arrayToObject($this->attributes);
        }

        return (object) $this->toArray();
    }

    /**
     * Warning: doesn't work with empty array keys!
     *
     * @param array $array
     * @return object
     */
    protected function arrayToObject(array $array)
    {
        $obj = (object) [];

        foreach ($array as $k => $v) {

            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{$k} = $this->arrayToObject($v);
                } else {
                    $obj->{$k} = $v;
                }
            }
        }

        return $obj;
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[ $offset ]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        // let it behave like the magic getter, return null if it doesn't exist
        if ( ! $this->offsetExists($offset)) return null;

        return $this->attributes[ $offset ];
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->attributes[ $offset ] = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[ $offset ]);
    }

}

