<?php

namespace Czim\DataObject;

use Czim\DataObject\Validation\CustomValidation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class DataObjectServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerDataobjectRule();
    }

    protected function registerDataobjectRule(): void
    {
        Validator::extend(
            'dataobject',
            static fn ($attribute, $value, $parameters, $validator): bool => (new CustomValidation())
                ->validateDataObject($attribute, $value, $parameters, $validator)
        );

        Validator::replacer(
            'dataobject',
            static fn ($message, $attribute, $rule, $parameters) => (new CustomValidation)
                ->replaceDataObject($message, $attribute, $rule, $parameters)
        );
    }
}
