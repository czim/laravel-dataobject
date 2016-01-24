<?php
namespace Czim\DataObject\Contracts;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use Serializable;

interface DataObjectInterface extends Arrayable, ArrayAccess, Countable, IteratorAggregate, Serializable
{
    /**
     * Get attribute
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key);

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Get attribute
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value);

    /**
     * Mass assignment of attributes
     *
     * @param array $attributes associative
     */
    public function setAttributes(array $attributes);

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param array $attributes
     */
    public function setRawAttributes(array $attributes);

    /**
     * @param bool $recursive
     * @return object
     */
    public function toObject($recursive = true);

    /**
     * Returns list of key names for the (top level) attributes
     *
     * @return array
     */
    public function getKeys();

    /**
     * Clears all attributes
     *
     * @return $this
     */
    public function clear();

    /**
     * Returns nested content by dot notation, similar to Laravel's Arr::get()
     *
     * @param string $key       dot-notation representation of keys
     * @param mixed  $default   default value to return if nothing found, may be a callback
     * @return mixed
     */
    public function getNested($key, $default = null);

}
