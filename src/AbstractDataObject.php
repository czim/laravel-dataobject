<?php

namespace Czim\DataObject;

use ArrayIterator;
use ArrayObject;
use Czim\DataObject\Contracts\DataObjectInterface;
use Czim\DataObject\Exceptions\UnassignableAttributeException;
use Czim\DataObject\Traits\ValidatableTrait;
use Illuminate\Contracts\Support\Arrayable;
use Iterator;

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
    protected function checkAttributeAssignable($attribute): void
    {
        if (empty($this->assignable)) {
            return;
        }

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
    public function &getAttribute(string $key)
    {
        return $this->getAttributeValue($key);
    }

    /**
     * Get a plain attribute
     *
     * @param string $key
     * @return mixed
     */
    protected function &getAttributeValue(string $key)
    {
        if (isset($this->attributes[$key])) {

            return $this->attributes[ $key ];
        }

        $null = null;

        return $null;
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get attribute
     *
     * @param string $key
     * @param mixed  $value
     * @return $this|DataObjectInterface
     */
    public function setAttribute(string $key, $value): DataObjectInterface
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
    public function setAttributes(array $attributes): void
    {
        $this->checkAttributeAssignable(array_keys($attributes));

        $this->setRawAttributes($attributes);
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param array $attributes
     */
    public function setRawAttributes(array $attributes): void
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /**
     * Returns list of key names for the (top level) attributes
     *
     * @return string[]
     */
    public function getKeys(): array
    {
        return array_keys($this->attributes);
    }

    /**
     * Clears all attributes
     *
     * @return $this|DataObjectInterface
     */
    public function clear(): DataObjectInterface
    {
        $this->attributes = [];

        return $this;
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
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value): void
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
        return (isset($this->attributes[$key]) && null !== $this->getAttributeValue($key));
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

    /**
     * Counts the attributes (overrides ArrayObject)
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->attributes);
    }

    // ------------------------------------------------------------------------------
    //      Conversion and Array Access
    // ------------------------------------------------------------------------------

    public function toArray(): array
    {
        if ( ! count($this->attributes)) {
            return [];
        }

        // make this work recursively
        $array = [];

        foreach ($this->attributes as $key => $attribute) {

            if ($attribute instanceof Arrayable) {

                $attribute = $attribute->toArray();

            } elseif (is_array($attribute)) {

                $attribute = $this->recursiveToArray($attribute);
            }

            $array[$key] = $attribute;
        }

        return $array;
    }

    /**
     * Recursively converts parameter to array
     *
     * @param mixed $item
     * @return array|string
     */
    protected function recursiveToArray($item)
    {
        if (is_array($item)) {

            foreach ($item as &$subitem) {
                $subitem = $this->recursiveToArray($subitem);
            }

            unset($subitem);

            return $item;
        }

        if ($item instanceof Arrayable) {
            return $item->toArray();
        }

        return $item;
    }

    /**
     * @param bool $recursive
     * @return object
     */
    public function toObject(bool $recursive = true)
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

    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
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
        if ( ! $this->magicAssignment) {
            throw new UnassignableAttributeException("Not allowed to assign value by magic with array access");
        }

        $this->checkAttributeAssignable($offset);

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


    // ------------------------------------------------------------------------------
    //      Dot Notation
    // ------------------------------------------------------------------------------

    /**
     * Returns nested content by dot notation, similar to Laravel's Arr::get()
     *
     * Works with nested arrays and data objects
     *
     * @param string|null $key  dot-notation representation of keys, null to return self
     * @param mixed  $default   default value to return if nothing found, may be a callback
     * @return mixed
     */
    public function getNested(?string $key, $default = null)
    {
        if (null === $key) {
            return $this;
        }

        if (isset($this->attributes[$key])) {
            return $this->getAttribute($key);
        }

        $keys = explode('.', $key);
        $part = $this->getAttribute( array_shift($keys) );


        foreach ($keys as $index => $segment) {

            if ($part instanceof DataObjectInterface) {
                return $part->getNested(implode('.', array_slice($keys, $index)), $default);
            }

            if ( ! is_array($part) || ! array_key_exists($segment, $part)) {
                return value($default);
            }

            $part = $part[ $segment ];
        }

        return $part;
    }


    // ------------------------------------------------------------------------------
    //      Iterator
    // ------------------------------------------------------------------------------

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->attributes);
    }

}

