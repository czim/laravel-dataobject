<?php

namespace Czim\DataObject\Validation;

use Czim\DataObject\Contracts\DataObjectInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Validator as IlluminateValidator;
use InvalidArgumentException;
use Throwable;

class CustomValidation
{
    /**
     * @param string            $attribute
     * @param mixed             $value
     * @param array<int, mixed> $parameters
     * @param Validator&IlluminateValidator $validator
     * @return bool
     */
    public function validateDataObject(string $attribute, mixed $value, array $parameters, Validator $validator): bool
    {
        if (! count($parameters)) {
            return true;
        }

        if (empty($value)) {
            return false;
        }

        // First parameter is the full path of the DataObject class
        $dataObjectClass = $parameters[0];

        // If the value is not already a DataObject of the type set in
        // the validation rule, make it so
        if (
            ! $value instanceof DataObjectInterface
            || ! $value instanceof $dataObjectClass
        ) {
            $value = $this->createDataObjectFrom($value, $parameters[0]);

            if ($value === false) {
                $validator->messages()->add(
                    $attribute,
                    $validator->makeReplacements(
                        'Value for :attribute could not be interpreted as :friendlydataobject',
                        $attribute, 'dataobject', $parameters
                    )
                );
                return false;
            }
        }


        if (! $value->validate()) {
            // Store messages with correct relative path to failed validation messages in the Validator
            foreach ($value->messages()->toArray() as $nestedAttribute => $messages) {
                foreach ($messages as $message) {
                    $validator->messages()->add($attribute . '.' . $nestedAttribute, $message);
                }
            }

            return false;
        }

        return true;
    }

    public function replaceDataObject(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(
            [
                ':dataobject',
                ':friendlydataobject',
            ],
            [
                $parameters[0],
                class_basename($parameters[0]),
            ],
            $message
        );
    }


    /**
     * Creates a DataObject with the given class name
     *
     * @param mixed  $value
     * @param string $className DataObject class to instantiate
     * @return DataObjectInterface|bool
     */
    protected function createDataObjectFrom(mixed $value, string $className): mixed
    {
        // If the value is already the correct object, return it unaltered
        if ($value instanceof $className) {
            return $value;
        }

        // Convert object to array, so it can be used to set attributes in the DataObject
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }  elseif (is_object($value)) {
            $value = json_decode(json_encode($value), true);
        }

        // Make validation fail if value could not be converted to an array
        if (! is_array($value)) {
            return false;
        }


        // Build an instance of the DataObject
        $dataObjectClass = $className;

        try {
            $dataObject = new $dataObjectClass($value);
        } catch (Throwable $e) {
            throw new InvalidArgumentException($dataObjectClass . ' is not instantiable as a DataObject', 0, $e);
        }

        if (! ($dataObject instanceof DataObjectInterface)) {
            throw new InvalidArgumentException($dataObjectClass . ' is not a validatable DataObject');
        }

        return $dataObject;
    }
}
