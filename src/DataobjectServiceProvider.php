<?php
namespace Czim\DataObject;

use Illuminate\Support\ServiceProvider;
use Validator;

class DataObjectServiceProvider extends ServiceProvider
{

    public function boot()
    {
        // resolve Validator facade to ExtendedValidator
        // this means that you should only use this service provider
        // if you don't have custom validation in your app already!

        Validator::resolver(function ($translator, $data, $rules, $messages) {

            return new Validation\ExtendedValidator($translator, $data, $rules, $messages);
        });
    }

    public function register()
    {
    }

}
