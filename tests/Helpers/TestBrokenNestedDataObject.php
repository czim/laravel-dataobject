<?php

namespace Czim\DataObject\Test\Helpers;

use Czim\DataObject\AbstractDataObject;

class TestBrokenNestedDataObject extends AbstractDataObject
{
    protected array $rules = [
        // not a DataObject!
        'nested' => 'required|dataobject:Czim\\DataObject\\Test\\Helpers\\TestNotADataObject',
    ];
}

