<?php
namespace Czim\DataObject\Contracts;

interface DataObjectInterface
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

}
