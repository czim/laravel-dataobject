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
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * The validation rules to apply to the attributes
     *
     * @var array<string, mixed>
     */
    protected array $rules = [];

    /**
     * List of keys that limit for which keys values may be assigned
     * If empty, allows any key to be assigned.
     *
     * @var string[]|null
     */
    protected ?array $assignable = null;

    /**
     * Whether to allow magic assignment of properties
     *
     * @var bool
     */
    protected bool $magicAssignment = true;


    /**
     * @param array<string, mixed> $attributes
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

        if (! in_array($attribute, $this->assignable)) {
            throw new UnassignableAttributeException("Not allowed to assign value for '{$attribute}'");
        }
    }

    // ------------------------------------------------------------------------------
    //      Simple Getting/Setting
    // ------------------------------------------------------------------------------

    public function &getAttribute(string $key): mixed
    {
        return $this->getAttributeValue($key);
    }

    protected function &getAttributeValue(string $key): mixed
    {
        if (isset($this->attributes[$key])) {

            return $this->attributes[ $key ];
        }

        $null = null;

        return $null;
    }

    /**
     * Get all currently set attributes on the model.
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this&DataObjectInterface
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
     * @param array<string, mixed> $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->checkAttributeAssignable(array_keys($attributes));

        $this->setRawAttributes($attributes);
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param array<string, mixed> $attributes
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
     * @return $this&DataObjectInterface
     */
    public function clear(): DataObjectInterface
    {
        $this->attributes = [];

        return $this;
    }

    // ------------------------------------------------------------------------------
    //      Magic Getting/Setting
    // ------------------------------------------------------------------------------

    public function &__get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        if (! $this->magicAssignment) {
            throw new UnassignableAttributeException("Not allowed to assign value by magic");
        }

        $this->checkAttributeAssignable($key);

        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return (
            isset($this->attributes[$key])
            && $this->getAttributeValue($key) !== null
        );
    }

    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    /**
     * Counts the attributes (overrides ArrayObject).
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

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if (! count($this->attributes)) {
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
     * @return array|string|null
     */
    protected function recursiveToArray(mixed $item): mixed
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
     * @param array<string, mixed> $array
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
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[ $offset ]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        // let it behave like the magic getter, return null if it doesn't exist
        if (! $this->offsetExists($offset)) return null;

        return $this->attributes[ $offset ];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (! $this->magicAssignment) {
            throw new UnassignableAttributeException("Not allowed to assign value by magic with array access");
        }

        $this->checkAttributeAssignable($offset);

        $this->attributes[ $offset ] = $value;
    }

    public function offsetUnset(mixed $offset): void
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
    public function getNested(?string $key, mixed $default = null): mixed
    {
        if ($key === null) {
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

            if (! is_array($part) || ! array_key_exists($segment, $part)) {
                return value($default);
            }

            $part = $part[ $segment ];
        }

        return $part;
    }


    // ------------------------------------------------------------------------------
    //      Iterator
    // ------------------------------------------------------------------------------

    /**
     * @return ArrayIterator<string, mixed>
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->attributes);
    }
}

