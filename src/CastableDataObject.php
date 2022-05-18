<?php

namespace Czim\DataObject;

use Czim\DataObject\Contracts\DataObjectInterface;
use Illuminate\Contracts\Support\Arrayable;
use UnexpectedValueException;

/**
 * Extended data object with the possibility to add casts, similar
 * to Eloquent models. Also allows 'casting' to another data object,
 * which provides lazy-loading data object trees.
 */
class CastableDataObject extends AbstractDataObject
{
    protected const SCALAR_CASTS = [
        'boolean',
        'integer',
        'float',
        'string',
        'array',
    ];

    /**
     * If true, returns an empty dataobject instance for unset or null values.
     *
     * @var bool
     */
    protected bool $castUnsetObjects = false;

    /**
     * If true, performs casts on toArray.
     *
     * @var bool
     */
    protected bool $castToArray = true;

    /**
     * Returns cast types per attribute key.
     *
     * Cast types may include: 'boolean', 'integer', 'float', 'date',
     * or the FQN of another data object, or an FQN followed by '[]' for
     * an array of data objects:
     *
     * Ex.:
     *  'some_boolean' => 'boolean',
     *  'some_object'  => YourDataObject::class,
     *  'some_objects' => YourDataObject::class . '[]',
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }


    /**
     * Returns value, casting attributes to indicated types or objects.
     *
     * @param string $key
     * @return mixed|DataObjectInterface
     */
    public function &getAttributeValue(string $key): mixed
    {
        $this->applyCast($key);

        return parent::getAttributeValue($key);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $this->applyCasts(true);

        return parent::toArray();
    }

    /**
     * Applies casts to currently set attributes.
     *
     * This updates the values stored for the attributes with a cast type.
     *
     * @param bool $scalarOnly
     */
    protected function applyCasts(bool $scalarOnly = false): void
    {
        // @codeCoverageIgnoreStart
        if (! $scalarOnly) {
            foreach (array_keys($this->casts()) as $key) {
                $this->applyCast($key);
            }

            return;
        }
        // @codeCoverageIgnoreEnd

        foreach ($this->casts() as $key => $type) {
            if (! in_array($type, static::SCALAR_CASTS)) {
                continue;
            }

            $this->applyCast($key);
        }
    }

    /**
     * Applies cast for a given attribute key.
     *
     * @param string $key
     */
    protected function applyCast(string $key): void
    {
        $casts = $this->casts();

        if (! count($casts) || ! array_key_exists($key, $casts)) {
            return;
        }

        if (! isset($this->attributes[ $key ])) {
            $value = null;
        } else {
            $value = $this->attributes[ $key ];
        }

        // If the cast type is a simple scalar, apply it and return
        if (in_array($casts[ $key ], static::SCALAR_CASTS)) {
            $this->attributes[ $key ] = call_user_func([$this, 'castValueAs' . ucfirst($casts[ $key ])], $value);
            return;
        }

        // Otherwise, attempt a data object cast
        $dataObjectClass = $casts[ $key ];
        $dataObjectArray = false;

        // If the model is postfixed with [], an array of models is expected
        if (str_ends_with($dataObjectClass, '[]')) {
            $dataObjectClass = substr($dataObjectClass, 0, -2);
            $dataObjectArray = true;
        }

        if ($value === null) {
            if ($dataObjectArray) {
                $this->attributes[ $key ] = [];
                return;
            }

            if ($this->castUnsetObjects) {
                $this->attributes[ $key ] = $this->makeNestedDataObject($dataObjectClass, [], $key);
            }
            return;
        }

        if ($dataObjectArray) {
            if (is_array($this->attributes[ $key ])) {
                foreach ($this->attributes[ $key ] as $index => &$item) {
                    if ($item === null && ! $this->castUnsetObjects) {
                        continue;
                    }

                    if (! ($item instanceof $dataObjectClass)) {
                        $item = $this->makeNestedDataObject($dataObjectClass, $item ?: [], $key . '.' . $index);
                    }
                }
            }

            unset($item);

            return;
        }

        // Single data object
        if (! ($this->attributes[ $key ] instanceof $dataObjectClass)) {
            $this->attributes[ $key ] = $this->makeNestedDataObject(
                $dataObjectClass,
                $this->attributes[ $key ],
                $key
            );
        }
    }

    /**
     * Makes a new nested data object for a given class and data.
     *
     * @param string $class
     * @param mixed  $data
     * @param string $key
     * @return DataObjectInterface
     */
    protected function makeNestedDataObject(string $class, mixed $data, string $key): DataObjectInterface
    {
        $data = ($data instanceof Arrayable) ? $data->toArray() : $data;

        if (! is_array($data)) {

            throw new UnexpectedValueException(
                "Cannot instantiate data object '{$class}' with non-array data for key '{$key}'"
                . (is_scalar($data) || is_object($data) && method_exists($data, '__toString')
                    ? ' (data: ' . (string) $data . ')'
                    : null)
            );
        }

        /** @var DataObjectInterface $data */
        return new $class($data);
    }

    protected function castValueAsBoolean(mixed $value): bool
    {
        return (bool) $value;
    }

    protected function castValueAsInteger(mixed $value): int
    {
        return (int) $value;
    }

    protected function castValueAsFloat(mixed $value): float
    {
        return (float) $value;
    }

    protected function castValueAsString(mixed $value): string
    {
        return (string) $value;
    }

    protected function castValueAsArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        return (array) $value;
    }
}
