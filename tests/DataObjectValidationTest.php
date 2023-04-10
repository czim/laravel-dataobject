<?php

namespace Czim\DataObject\Test;

use Czim\DataObject\DataObjectServiceProvider;
use Czim\DataObject\Test\Helpers\TestBrokenNestedDataObject;
use Czim\DataObject\Test\Helpers\TestDataObject;
use Czim\DataObject\Test\Helpers\TestNestedDataObject;
use Illuminate\Foundation\Application;
use InvalidArgumentException;

class DataObjectValidationTest extends TestCase
{
    /**
     * @param Application $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            DataObjectServiceProvider::class,
        ];
    }


    // ------------------------------------------------------------------------------
    //      Nested DataObject validation
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    public function it_validates_nested_data_objects_for_required_nested_invalid_data(): void
    {
        $data = new TestNestedDataObject();

        static::assertFalse($data->validate(), 'validation should fail for empty');

        $nestedData = new TestDataObject([ 'irrelevant' => 'nothing' ]);

        static::assertFalse($nestedData->validate(), 'validation should fail for empty nested (on its own)');

        $data->nested = $nestedData;

        static::assertFalse($data->validate(), 'validation should fail for empty nested (when nested)');

        // the messages should indicate that the name field is missing from the nested object
        $messages = $data->messages();

        static::assertCount(2, $messages, 'should be two messages in the bag (1 for nested, 1 for nested content');
        static::assertMatchesRegularExpression(
            '#name .*required#i',
            $messages->get('nested.name')[0],
            'Incorrect or missing message for nested name'
        );
        static::assertEquals(
            'validation.dataobject',
            $messages->get('nested')[0],
            'Incorrect or missing message for nested main'
        );

        // Note that the validation.dataobject message is the custom message for which there is
        // no translation in this test!
    }

    /**
     * @test
     */
    public function it_validates_nested_data_objects_for_required_nested_valid_data(): void
    {
        $data         = new TestNestedDataObject();
        $data->nested = new TestDataObject([ 'name' => 'no problem' ]);

        static::assertTrue($data->validate(), 'validation should pass for valid nested data');
    }

    /**
     * @test
     * @depends it_validates_nested_data_objects_for_required_nested_valid_data
     */
    public function it_it_validates_nested_data_objects_for_optional_invalid_data(): void
    {
        $data = new TestNestedDataObject();
        $data->nested = [ 'name' => 'no problem' ];
        $data->more   = new TestDataObject([ 'irrelevant' => 'nothing' ]);

        static::assertFalse($data->validate(), 'validation should fail for invalid optional nested data');
        static::assertMatchesRegularExpression('#name .*required#i', $data->messages()->get('more.name')[0]);
    }

    /**
     * @test
     * @depends it_validates_nested_data_objects_for_required_nested_valid_data
     */
    public function it_it_validates_nested_data_objects_for_optional_valid_data(): void
    {
        $data = new TestNestedDataObject();
        $data->nested = new TestDataObject([ 'name' => 'no problem' ]);

        // should pass when more is empty
        static::assertTrue($data->validate(), 'validation should pass for omitted optional nested data');

        $data->more = ['name' => 'also fine'];

        // should also pass when more is valid
        static::assertTrue($data->validate(), 'validation should pass for valid optional nested data');
    }

    /**
     * @test
     */
    public function it_fails_validation_if_value_for_nested_dataobject_rule_could_not_be_interpreted(): void
    {
        $data = new TestNestedDataObject();
        $data->nested = 'not a dataobject, or even an array';

        static::assertFalse($data->validate(), 'validation should fail for uninterpretable dataobject value');
        static::assertMatchesRegularExpression(
            '#not .*interpret.* as testdataobject#i',
            $data->messages()->first(),
            'incorrect validation message for uninterpretable dataobject value'
        );
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_a_dataobject_validation_rule_does_not_reference_a_data_object(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('# not .*validatable dataobject#i');

        $data = new TestBrokenNestedDataObject();

        $data->nested = [ 'nothing' => 'to', 'see' => 'here' ];

        $data->validate();
    }


    // ------------------------------------------------------------------------------
    //      Arrayable / JsonAble with nesting
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    public function it_is_recursively_arrayable_for_nested_attributes(): void
    {
        $data = new TestNestedDataObject([
            'nested' => new TestDataObject([
                'name' => 'beautiful name',
            ]),
            'random' => 'extra',
        ]);

        $array = $data->toArray();

        static::assertIsArray($array, 'toArray() did not return array');
        static::assertCount(2, $array, 'incorrect item count');
        static::assertArrayHasKey('random', $array);
        static::assertEquals('extra', $array['random']);

        static::assertIsArray($array['nested'], 'toArray() nested object was not an array');
        static::assertArrayHasKey('name', $array['nested']);
        static::assertEquals('beautiful name', $array['nested']['name']);
    }

    /**
     * @test
     */
    public function it_is_recursively_jsonable_for_nested_attributes(): void
    {
        $data = new TestNestedDataObject([
            'nested' => new TestDataObject([
                'name' => 'beautiful name',
            ]),
            'random' => 'extra',
        ]);

        static::assertEquals(
            '{"nested":{"name":"beautiful name"},"random":"extra"}',
            $data->toJson(),
            'incorrect nested toJson result'
        );
    }
}
