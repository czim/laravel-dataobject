<?php

namespace Czim\DataObject\Contracts;

use Illuminate\Contracts\Support\MessageBag;

interface ValidatableInterface
{
    public function validate(): bool;
    public function messages(): MessageBag;

    /**
     * Returns currently set validation rules.
     *
     * @return array<string, mixed>
     */
    public function getRules(): array;

    /**
     * @param array<string, mixed> $rules
     */
    public function setRules(array $rules): void;
}
