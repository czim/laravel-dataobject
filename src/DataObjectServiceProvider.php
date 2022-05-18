<?php

namespace Czim\DataObject;

use Czim\DataObject\Validation\CustomValidation;
use Illuminate\Support\ServiceProvider;
use Validator;

class DataObjectServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerDataobjectRule();
    }

    public function register(): void
    {
    }


    protected function registerDataobjectRule(): void
    {
        Validator::extend('dataobject', function ($attribute, $value, $parameters, $validator) {
            return (new CustomValidation())
                ->validateDataObject($attribute, $value, $parameters, $validator);
        });

        Validator::replacer('dataobject', function ($message, $attribute, $rule, $parameters) {
            return (new CustomValidation)
                ->replaceDataObject($message, $attribute, $rule, $parameters);
        });
    }
}
