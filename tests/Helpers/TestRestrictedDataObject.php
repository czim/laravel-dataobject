<?php

namespace Czim\DataObject\Test\Helpers;

class TestRestrictedDataObject extends TestDataObject
{

    protected $assignable = [
        'name',
        'list',
    ];

}
