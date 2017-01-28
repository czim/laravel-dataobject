<?php
namespace Czim\DataObject\Validation;

use Illuminate\Validation\Validator;

class ExtendedValidator extends Validator
{
    use Traits\ValidateAsDataObjectTrait;
}
