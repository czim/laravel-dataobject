<?php
namespace Czim\DataObject\Validation\Traits;

/**
 * For ExtendedValidator
 *
 * Add 'each' validation syntax:
 *
 * Usage:
 *      each:rule,parameter,parameter
 */
trait ValidateArraysTrait
{

    /**
     * Handles validation for: each
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     * @return bool
     */
    public function validateEach($attribute, $value, $parameters)
    {
        if ( ! is_array($value)) return false;

        // Transform the each rule
        // For example, `each:exists,users,name` becomes `exists:users,name`

        $ruleName = array_shift($parameters);
        $rule     = $ruleName . (count($parameters) > 0
                    ? ':' . implode(',', $parameters)
                    : null );

        foreach ($value as $arrayKey => $arrayValue) {

            $this->validate($attribute . '.' . $arrayKey, $rule);
        }

        // Always return true, since the errors occur for individual elements.
        return true;
    }

    /**
     * Overrides getAttribute to make it compatible with array syntax
     *
     * @param string $attribute
     * @return mixed
     */
    protected function getAttribute($attribute)
    {
        // Get the second to last segment in singular form for arrays.
        // For example, `group.names.0` becomes `name`.

        if (str_contains($attribute, '.')) {

            $segments  = explode('.', $attribute);
            $attribute = str_singular($segments[ count($segments) - 2 ]);
        }

        return parent::getAttribute($attribute);
    }

}

