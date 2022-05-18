<?php

namespace Czim\DataObject\Test\Helpers;

use Czim\DataObject\AbstractDataObject;

class TestNestedDataObject extends AbstractDataObject
{
    protected array $rules = [
        'nested' => 'required|dataobject:Czim\\DataObject\\Test\\Helpers\\TestDataObject',
        'more'   => 'dataobject:Czim\\DataObject\\Test\\Helpers\\TestDataObject',
    ];
}
