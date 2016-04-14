<?php
namespace Czim\DataObject\Contracts;

interface ValidatableInterface
{

    /**
     * Validate attributes
     *
     * @return boolean
     */
    public function validate();

    /**
     * If validation tried and failed, returns validation messages
     *
     * @return \Illuminate\Contracts\Support\MessageBag
     */
    public function messages();

    /**
     * Returns currently set validation rules
     *
     * @return array
     */
    public function getRules();

    /**
     * Sets validation rules
     *
     * @param array $rules
     */
    public function setRules(array $rules);

}
