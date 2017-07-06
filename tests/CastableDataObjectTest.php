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

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_it_cannot_cast_to_an_object()
    {
        $data = new Helpers\TestCastDataObject();

        $data->object = 'not an array';

        $data->object;
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_it_cannot_cast_to_an_object_in_a_list()
    {
        $data = new Helpers\TestCastDataObject();

        $data->objects = [ ['type' => 'an array'], 444];

        $data->objects;
    }

    /**
     * @test
     */
    function it_returns_null_for_an_unset_attribute_cast_as_an_object()
    {
        $data = new Helpers\TestCastDataObject();

        $this->assertNull($data->object);
    }

    /**
     * @test
     */
    function it_returns_empty_objects_for_an_unset_attribute_cast_as_an_object_if_configured_to()
    {
        $data = new Helpers\TestCastDataObjectCastNull();

        $this->assertInstanceOf(TestDataObject::class, $data->object);
    }

    /**
     * @test
     */
    function it_returns_null_for_an_unset_attribute_in_an_array_cast_as_a_list_of_objects()
    {
        $data = new Helpers\TestCastDataObject();

        $data->objects = [
            null,
            ['type' => 'test'],
            null,
        ];

        $objects = $data->objects;

        $this->assertInternalType('array', $objects);
        $this->assertCount(3, $objects);
        $this->assertNull($objects[0]);
        $this->assertNull($objects[2]);

        $this->assertInstanceOf(TestDataObject::class, $objects[1]);
        $this->assertEquals('test', $objects[1]->type);
    }

    /**
     * @test
     */
    function it_casts_attributes_on_toArray()
    {
        $data = new Helpers\TestCastDataObject();

        $data->int     = '6';
        $data->object  = new TestDataObject(['type' => 'object']);
        $data->objects = [
            ['test' => 'testing a'],
            ['test' => 'testing b'],
        ];

        $array = $data->toArray();

        $this->assertInternalType('array', $array);
        $this->assertCount(7, $array);
        $this->assertArrayHasKey('int', $array);
        $this->assertSame(6, $array['int']);
        $this->assertArrayHasKey('object', $array);
        $this->assertEquals(['type' => 'object'], $array['object']);
        $this->assertArrayHasKey('objects', $array);
        $this->assertEquals([['test' => 'testing a'], ['test' => 'testing b']], $array['objects']);
        $this->assertArrayHasKey('bool', $array);
        $this->assertSame(false, $array['bool']);
        $this->assertArrayHasKey('float', $array);
        $this->assertSame(0.0, $array['float']);
        $this->assertArrayHasKey('string', $array);
        $this->assertSame('', $array['string']);
        $this->assertArrayHasKey('array', $array);
        $this->assertSame([], $array['array']);
    }

    // ------------------------------------------------------------------------------
    //      Use without casts
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_works_normally_without_casts_set()
    {
        $data = new Helpers\TestCastDataObjectWithoutCasts();

        $data->object = ['test' => 'testing'];
        $data->float = 'not a float';

        $this->assertSame(['test' => 'testing'], $data->object);
        $this->assertSame('not a float', $data['float']);

        $array = $data->toArray();
        $this->assertCount(2, $array);
        $this->assertSame(['test' => 'testing'], $array['object']);
        $this->assertSame('not a float', $array['float']);
    }

}
