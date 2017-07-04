<?php
namespace Czim\DataObject;

use Czim\DataObject\Contracts\DataObjectInterface;
use Illuminate\Contracts\Support\Arrayable;
use UnexpectedValueException;

/**
 * Class CastableDataObject
 *
 * Extended data object with the possibility to add casts, similar
 * to Eloquent models. Also allows 'casting' to another data object,
 * which provides lazy-loading data object trees.
 */
class CastableDataObject extends AbstractDataObject
{

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
     * @return array    associative
     */
    protected function casts()
    {
        return [];
    }


    /**
     * Returns value, casting attributes to indicated types or objects.
     *
     * @param string $key
     * @return mixed|DataObjectInterface
     */
    public function &getAttributeValue($key)
    {
        $casts = $this->casts();

        if ( ! count($casts) || ! array_key_exists($key, $casts)) {
            return parent::getAttribute($key);
        }

        if ( ! isset($this->attributes[$key])) {
            $value = null;
        } else {
            $value = $this->attributes[$key];
        }

        if (in_array($casts[ $key ], ['boolean', 'integer', 'float', 'string', 'array'])) {
            $value = call_user_func([$this, 'castValueAs' . ucfirst($casts[ $key ])], $value);
            return $value;
        }

        // Fallback is to attempt a data object cast
        $dataObjectClass = $casts[ $key ];
        $dataObjectArray = false;

        // If the model is postfixed with [], an array of models is expected
        if (substr($dataObjectClass, -2) === '[]') {
            $dataObjectClass = substr($dataObjectClass, 0, -2);
            $dataObjectArray = true;
        }

        if (null === $value) {
            if ($dataObjectArray) {
                $value = [];
                return $value;
            }
            return $value;
        }

        if ($dataObjectArray) {

            if (is_array($this->attributes[$key])) {

                foreach ($this->attributes[$key] as $index => &$item) {

                    if (null === $item) {
                        continue;
                    }

                    if ( ! ($item instanceof $dataObjectClass)) {
                        $item = $this->makeNestedDataObject($dataObjectClass, $item, $key . '.' . $index);
                    }
                }
            }

            unset($item);

        } else {

            if ( ! ($this->attributes[ $key ] instanceof $dataObjectClass)) {
                $this->attributes[ $key ] = $this->makeNestedDataObject(
                    $dataObjectClass,
                    $this->attributes[ $key ],
                    $key
                );
            }
        }

        return $this->attributes[$key];
    }

    /**
     * Makes a new nested data object for a given class and data.
     *
     * @param string $class
     * @param mixed  $data
     * @param string $key
     * @return mixed
     */
    protected function makeNestedDataObject($class, $data, $key)
    {
        $data = ($data instanceof Arrayable) ? $data->toArray() : $data;

        if ( ! is_array($data)) {
            throw new UnexpectedValueException(
                "Cannot instantiate data object '{$class}' with non-array data for key '{$key}'"
                . (is_scalar($data) || is_object($data) && method_exists($data, '__toString')
                    ?   ' (data: ' . (string) $data . ')'
                    :   null)
            );
        }

        /** @var DataObjectInterface $data */
        return new $class($data);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function castValueAsBoolean($value)
    {
        return (bool) $value;
    }

    /**
     * @param mixed $value
     * @return int
     */
    protected function castValueAsInteger($value)
    {
        return (int) $value;
    }

    /**
     * @param mixed $value
     * @return int
     */
    protected function castValueAsFloat($value)
    {
        return (float) $value;
    }

    /**
     * @param mixed $value
     * @return int
     */
    protected function castValueAsString($value)
    {
        return (string) $value;
    }

    /**
     * @param mixed $value
     * @return array
     */
    protected function castValueAsArray($value)
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
