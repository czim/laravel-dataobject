<?php

namespace Czim\DataObject\Traits;

use Illuminate\Contracts\Support\MessageBag as MessageBagContract;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator;

/**
 * Allow a class to be validated with validate()
 * use $attributes to set validatable data and
 * use $rules to set the validation rules
 *
 * Note that this requires a getAttributes() method.
 *
 * @property \Illuminate\Validation\Validator|null $validator
 * @property array<string, mixed>|null             $rules
 */
trait ValidatableTrait
{
    /**
     * @var ValidatorContract
     */
    protected $validator = null;


    public function validate(): bool
    {
        $this->validator = Validator::make($this->getAttributes(), $this->getRules());

        return ! $this->validator->fails();
    }

    public function messages(): MessageBagContract
    {
        if ($this->validator === null) {
            $this->validate();
        }

        if ( ! $this->validator->fails()) {
            return new MessageBag();
        }

        return $this->validator->messages();
    }

    /**
     * Accessor method to check for validation data set
     *
     * @return array<string, mixed>
     */
    public function getRules(): array
    {
        return $this->rules ?? [];
    }

    /**
     * @param array<string, mixed> $rules
     */
    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }
}
