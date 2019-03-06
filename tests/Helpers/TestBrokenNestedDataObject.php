<?php

namespace Czim\DataObject\Test\Helpers;

use Czim\DataObject\AbstractDataObject;

class TestBrokenNestedDataObject extends AbstractDataObject
{

    protected $rules = [
        // not a DataObject!
        'nested' => 'required|dataobject:Czim\\DataObject\\Test\\Helpers\\TestNotADataObject',
    ];

}

