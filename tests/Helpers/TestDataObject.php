<?php
namespace Czim\DataObject\Test\Helpers;

use Czim\DataObject\AbstractDataObject;

class TestDataObject extends AbstractDataObject
{

    protected $rules = [
        'name' => 'required|string',
        'list' => 'array|min:1',
    ];

}
