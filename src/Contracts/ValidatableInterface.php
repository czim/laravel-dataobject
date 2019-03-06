<?php

namespace Czim\DataObject\Contracts;

use Illuminate\Contracts\Support\MessageBag;

interface ValidatableInterface
{

    /**
     * Validate attributes
     *
     * @return bool
     */
    public function validate(): bool;

    /**
     * If validation tried and failed, returns validation messages
     *
     * @return MessageBag
     */
    public function messages(): MessageBag;

    /**
     * Returns currently set validation rules
     *
     * @return array
     */
    public function getRules(): array;

    /**
     * Sets validation rules
     *
     * @param array $rules
     */
    public function setRules(array $rules): void;

}
