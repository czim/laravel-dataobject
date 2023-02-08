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
    public function getAttribute(string $key): mixed;

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array;

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this&DataObjectInterface
     */
    public function setAttribute(string $key, mixed $value): DataObjectInterface;

    /**
     * Mass assignment of attributes.
     *
     * @param array<string, mixed> $attributes
     */
    public function setAttributes(array $attributes): void;

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param array<string, mixed> $attributes
     */
    public function setRawAttributes(array $attributes): void;

    /**
     * @param bool $recursive
     * @return object
     */
    public function toObject(bool $recursive = true);

    /**
     * Returns list of key names for the (top level) attributes.
     *
     * @return string[]
     */
    public function getKeys(): array;

    /**
     * @return $this&DataObjectInterface
     */
    public function clear(): DataObjectInterface;

    /**
     * Returns nested content by dot notation, similar to Laravel's Arr::get()
     *
     * @param string $key       dot-notation representation of keys
     * @param mixed  $default   default value to return if nothing found, may be a callback
     * @return mixed
     */
    public function getNested(string $key, mixed $default = null): mixed;

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array;

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void;
}
