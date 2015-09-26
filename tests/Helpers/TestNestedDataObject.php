<?php
namespace Czim\DataObject\Test\Helpers;

use Czim\DataObject\AbstractDataObject;

class TestNestedDataObject extends AbstractDataObject
{
    protected $rules = [
        // test nested DataObject validation
        'nested' => 'required|dataobject:Czim\\DataObject\\Test\\Helpers\\TestDataObject',
        // test extended array validation
        'array'  => 'array|each:string|each:min,5',
    ];
}
