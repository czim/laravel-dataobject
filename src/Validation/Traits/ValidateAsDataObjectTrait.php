<?php
namespace Czim\DataObject\Validation\Traits;

use Czim\DataObject\Contracts\DataObjectInterface;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

/**
 * For ExtendedValidator
 *
 * Adds 'dataobject' validation rule for nested DataObject validation
 *
 * Usage:
 *      dataobject:\Class\Path\To\DataObject
 *
 * This will instantiate the value if it is not already a DataObject and
 * set the contents for its attributes, then call its validation method.
 *
 * This means that you can validate nested arrays as nested DataObjects
 * without converting them beforehand.
 */
trait ValidateAsDataObjectTrait
{
    /**
     * Handles validation for: dataobject
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validateDataObject($attribute, $value, $parameters)
    {
        if ( ! count($parameters)) return true;

        if (empty($value)) return false;

        // First parameter is the full path of the DataObject class
        $dataObjectClass = $parameters[0];

        // If the value is not already a DataObject of the type set in
        // the validation rule, make it so
        if (   ! ($value instanceof DataObjectInterface)
            || ! ($value instanceof $dataObjectClass)
        ) {
            $value = $this->createDataObjectFrom($value, $parameters[0]);

            if ($value === false) {

                $this->messages->add(
                    $attribute,
                    $this->doReplacements(
                        "Value for :attribute could not be interpreted as :friendlydataobject",
                        $attribute, 'dataobject', $parameters
                    )
                );
                return false;
            }
        }

        // Perform validation on the DataObject
        if ( ! $value->validate()) {

            // Store messages with correct relative path to failed validation messages
            // in the Validator
            foreach ($value->messages()->toArray() as $nestedAttribute => $messages) {

                foreach ($messages as $message) {

                    $this->messages->add($attribute . '.' . $nestedAttribute, $message);
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Creates a DataObject with the given class name
     *
     * @param mixed  $value
     * @param string $className DataObject class to instantiate
     * @return DataObjectInterface|bool
     */
    protected function createDataObjectFrom($value, $className)
    {
        // If the value is already the correct object, return it unaltered
        if ($value instanceof $className) {
            return $value;
        }

        // Convert object to array so it can be used to set
        // attributes in the DataObject

        if ($value instanceof Arrayable) {

            $value = $value->toArray();

        }  elseif (is_object($value)) {

            $value = json_decode(json_encode($value), true);
        }

        // Make validation fail if value could not be converted to an array
        if ( ! is_array($value)) return false;


        // Build an instance of the DataObject
        $dataObjectClass = $className;

        try {

            $dataObject = new $dataObjectClass($value);

        } catch (Exception $e) {

            throw new InvalidArgumentException($dataObjectClass . ' is not instantiable as a DataObject', 0, $e);
        }

        if ( ! ($dataObject instanceof DataObjectInterface)) {

            throw new InvalidArgumentException($dataObjectClass . ' is not a validatable DataObject');
        }

        return $dataObject;
    }


    /**
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     * @return string
     */
    protected function replaceDataObject($message, $attribute, $rule, $parameters)
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

}
