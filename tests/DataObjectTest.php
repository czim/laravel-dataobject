<?php
namespace Czim\DataObject\Test;

use Illuminate\Contracts\Support\MessageBag;

class DataObjectTest extends TestCase
{
    /**
     * @test
     */
    function it_returns_null_for_unassigned_attributes()
    {
        $data = new Helpers\TestDataObject();

        $this->assertNull($data->getAttribute('unset_key'), 'unassigned attribute was not null (getAttribute)');

        $this->assertNull($data->unset_attribute, 'unassigned attribute was not null (magic)');
    }

    /**
     * @test
     */
    function it_stores_and_retrieves_attributes_individually()
    {
        $data = new Helpers\TestDataObject();

        // method assignment
        $data->setAttribute('name', 'some test value');
        $this->assertEquals('some test value', $data->getAttribute('name'), 'method assignment failed');

        // magic assignment
        $data->name = 'some test value';
        $this->assertEquals('some test value', $data->name, 'magic assignment failed');
    }
    
    /**
     * @test
     */
    function it_mass_stores_and_retrieves_attributes()
    {
        $data = new Helpers\TestDataObject();

        $data->setAttributes([
            'mass'       => 'testing',
            'assignment' => 2242,
        ]);

        $this->assertEquals('testing', $data->mass, 'mass assignment failed (1)');
        $this->assertEquals(2242, $data->assignment, 'mass assignment failed (2)');
    }

    /**
     * @test
     */
    function it_initializes_attributes_through_its_constructor()
    {
        $data = new Helpers\TestDataObject([
            'mass'       => 'testing',
            'assignment' => 2242,
        ]);

        $this->assertEquals('testing', $data->mass, 'constructor assignment failed (1)');
        $this->assertEquals(2242, $data->assignment, 'constructor assignment failed (2)');
    }

    /**
     * @test
     */
    function it_validates_attributes()
    {
        $data = new Helpers\TestDataObject();

        // validate empty data against single required rule
        $this->assertFalse($data->validate(), 'empty should not pass validation');

        $messages = $data->messages();
        $this->assertInstanceOf(MessageBag::class, $messages, 'validation messages not of correct type');
        $this->assertCount(1, $messages, 'validation messages should have 1 message');
        $this->assertRegExp('#name .*is required#i', $messages->first(), 'validation message not as expected for empty data');

        // validate partially incorrect data
        $data->name = 'Valid name';
        $data->list = 'not an array';

        $this->assertFalse($data->validate(), 'incorrect data should not pass validation');

        $messages = $data->messages();
        $this->assertCount(1, $messages, 'validation messages should have 1 message');
        $this->assertRegexp('#list .*must be an array#i', $messages->first(), 'validation message not as expected for incorrect data');

        // validate correct data should be okay
        $data->name = 'Valid name';
        $data->list = [ 'one' => 'present' ];

        $this->assertTrue($data->validate(), 'Correct data should pass validation');
    }

    /**
     * @test
     * @expectedException \Czim\DataObject\Exceptions\UnassignableAttributeException
     * @expectedExceptionMessageRegExp #not allowed .*does_not_exist#i
     */
    function it_throws_an_exception_when_assigning_to_disallowed_keys()
    {
        $data = new Helpers\TestRestrictedDataObject();

        // allow normal assignment that is listed in $assignable
        $data->name = 'pietje';
        $data->setAttribute('list', [ 'some', 'list' ]);
        $data->setAttributes([
            'name' => 'pietje weer',
            'list' => [ 'other', 'items' ],
        ]);

        // exception on disallowed
        $data->does_not_exist = 'exception';
    }

    /**
     * @test
     * @depends it_throws_an_exception_when_assigning_to_disallowed_keys
     * @expectedException \Czim\DataObject\Exceptions\UnassignableAttributeException
     * @expectedExceptionMessageRegExp #not allowed .*does_not_exist#i
     */
    function it_throws_an_exception_when_assigning_to_disallowed_keys_for_mass_assignment()
    {
        $data = new Helpers\TestRestrictedDataObject();

        $data->setAttributes([
            'does_not_exist' => 'exception',
        ]);
    }

}
