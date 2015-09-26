<?php
namespace Czim\DataObject\Test\Helpers;

use Czim\DataObject\AbstractDataObject;

class TestArrayValidationDataObject extends AbstractDataObject
{
    protected $rules = [
        // test extended array validation
        'array'  => 'array|each:string|each:min,5',
    ];
}
