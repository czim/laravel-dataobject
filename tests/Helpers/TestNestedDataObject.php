<?php
namespace Czim\DataObject\Test\Helpers;

use Czim\DataObject\AbstractDataObject;

class TestNestedDataObject extends AbstractDataObject
{
    protected $rules = [
        'nested' => 'required|dataobject:Czim\\DataObject\\Test\\Helpers\\TestDataObject',
    ];
}
