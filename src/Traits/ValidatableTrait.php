<?php

namespace Czim\DataObject\Traits;

use Illuminate\Contracts\Support\MessageBag as MessageBagContract;
use Illuminate\Support\MessageBag;
use Validator;

/**
 * Allow a class to be validated with validate()
 * use $attributes to set validatable data and
 * use $rules to set the validation rules
 *
 * Note that this requires a getAttributes() method.
 *
 * @property \Illuminate\Validation\Validator|null $validator
 * @property array|null                            $rules
 */
trait ValidatableTrait
{
    /**
     * Validator instance
     *
     * @var \Illuminate\Contracts\Validation\Validator
     */
    protected $validator = null;


    /**
     * Validates the filter data
     *
     * @return boolean
     */
    public function validate(): bool
    {
        $this->validator = Validator::make($this->getAttributes(), $this->getRules());

        return ! $this->validator->fails();
    }

    /**
     * Returns validation errors, if any
     *
     * @return MessageBagContract
     */
    public function messages(): MessageBagContract
    {
        if ($this->validator === null) {
            $this->validate();
        }

        if ( ! $this->validator->fails()) {

            return new MessageBag;
        }

        return $this->validator->messages();
    }

    /**
     * Accessor method to check for validation data set
     *
     * @return array
     */
    public function getRules(): array
    {
        return (isset($this->rules)) ? $this->rules : [];
    }

    /**
     * Setter for $rules
     *
     * @param array $rules
     */
    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }

}
