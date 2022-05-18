<?php

namespace Czim\DataObject\Test\Helpers;

use Czim\DataObject\AbstractDataObject;

class TestDataObject extends AbstractDataObject
{
    protected array $rules = [
        'name' => 'required|string',
        'list' => 'array|min:1',
    ];
}
