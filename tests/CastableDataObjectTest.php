<?php
namespace Czim\DataObject\Test;

use Czim\DataObject\Test\Helpers\TestDataObject;

class CastableDataObjectTest extends TestCase
{
    /**
     * @test
     */
    function it_casts_an_attribute_as_a_boolean()
    {
        $data = new Helpers\TestCastDataObject();

        $this->assertSame(false, $data->bool);

        $data->bool = 'boolean value';
        $this->assertSame(true, $data->bool);
    }

    /**
     * @test
     */
    function it_casts_an_attribute_as_an_integer()
    {
        $data = new Helpers\TestCastDataObject();

        $this->assertSame(0, $data->int);

        $data->int = 'integer value';
        $this->assertSame(0, $data->int);

        $data->int = 40.0;
        $this->assertSame(40, $data->int);
    }

    /**
     * @test
     */
    function it_casts_an_attribute_as_a_float()
    {
        $data = new Helpers\TestCastDataObject();

        $this->assertSame(0.0, $data->float);

        $data->float = 'float value';
        $this->assertSame(0.0, $data->float);

        $data->float = 40;
        $this->assertSame(40.0, $data->float);
    }

    /**
     * @test
     */
    function it_casts_an_attribute_as_a_string()
    {
        $data = new Helpers\TestCastDataObject();

        $this->assertSame('', $data->string);

        $data->string = 'string value';
        $this->assertSame('string value', $data->string);

        $data->string = 40;
        $this->assertSame('40', $data->string);
    }

    /**
     * @test
     */
    function it_casts_an_attribute_as_a_data_object()
    {
        $data = new Helpers\TestCastDataObject();

        $this->assertNull($data->object);

        $data->object = [
            'test' => 'testing',
        ];

        $object = $data->object;

        $this->assertInstanceOf(TestDataObject::class, $object);
        $this->assertEquals('testing', $object->test);
    }

    /**
     * @test
     */
    function it_casts_an_attribute_as_a_list_of_data_objects()
    {
        $data = new Helpers\TestCastDataObject();

        $this->assertEquals([], $data->objects);

        $data->objects = [
            ['test' => 'testing a'],
            ['test' => 'testing b'],
        ];

        $objects = $data->objects;

        $this->assertInternalType('array', $objects);
        $this->assertCount(2, $objects);
        $this->assertInstanceOf(TestDataObject::class, $objects[0]);
        $this->assertInstanceOf(TestDataObject::class, $objects[1]);
        $this->assertEquals('testing a', $objects[0]->test);
        $this->assertEquals('testing b', $objects[1]->test);
    }

}