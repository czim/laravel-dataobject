<?php

namespace Czim\DataObject\Contracts;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use Serializable;

/**
 * @extends IteratorAggregate<string,mixed>
 */
interface DataObjectInterface extends Arrayable, ArrayAccess, Countable, IteratorAggregate, Serializable, ValidatableInterface
{
    /**
     * Get attribute.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key);

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes(): array;

    /**
     * Get attribute
     *
     * @param string $key
     * @param mixed  $value
     * @return $this|DataObjectInterface
     */
    public function setAttribute(string $key, $value): DataObjectInterface;

    /**
     * Mass assignment of attributes
     *
     * @param mixed[] $attributes associative
     */
    public function setAttributes(array $attributes): void;

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param mixed[] $attributes
     */
    public function setRawAttributes(array $attributes): void;

    /**
     * @param bool $recursive
     * @return object
     */
    public function toObject(bool $recursive = true);

    /**
     * Returns list of key names for the (top level) attributes
     *
     * @return string[]
     */
    public function getKeys(): array;

    /**
     * Clears all attributes
     *
     * @return $this|DataObjectInterface
     */
    public function clear(): DataObjectInterface;

    /**
     * Returns nested content by dot notation, similar to Laravel's Arr::get()
     *
     * @param string $key       dot-notation representation of keys
     * @param mixed  $default   default value to return if nothing found, may be a callback
     * @return mixed
     */
    public function getNested(string $key, $default = null);
}
