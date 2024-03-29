# Laravel Data Object

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/czim/laravel-dataobject.svg?branch=master)](https://travis-ci.org/czim/laravel-dataobject)
[![Coverage Status](https://coveralls.io/repos/github/czim/laravel-dataobject/badge.svg?branch=master)](https://coveralls.io/github/czim/laravel-dataobject?branch=master)
[![Latest Stable Version](http://img.shields.io/packagist/v/czim/laravel-dataobject.svg)](https://packagist.org/packages/czim/laravel-dataobject)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/369fd4d7-b2d1-4438-9e08-e7ad586b81c4/mini.png)](https://insight.sensiolabs.com/projects/369fd4d7-b2d1-4438-9e08-e7ad586b81c4)

Basic framework for making standardized but flexible data objects.
This provides a class (and interfaces) to use to standardize your dependencies or return values with a generic dataobject.

All this really is, is a data storage class with magic getters and setters.
It is Arrayable, Jsonable and validatable.

Also provided are the means to make nested validation possible for data objects (containing futher data objects).


## Version Compatibility

| Laravel    | Package  | PHP                     |
|:-----------|:---------|:------------------------|
| 5.1        | 1.0      |                         |
| 5.2        | 1.0      |                         |
| 5.3        | 1.3      |                         |
| 5.4        | 1.4      |                         |
| 5.5 and up | 1.4, 2.0 |                         |
| 9 and up   | 3.0      | 8.0 and up              |

## Install

Via Composer

``` bash
$ composer require czim/laravel-dataobject
```

## Usage

Simply create your own extension of the base dataobject, and:


### Optionally add your own getters and setters

Basic stuff of course, but if you want your IDE to know what your objects contain, you can simply write getters and setters like this:

```php
class TestDataObject extends \Czim\DataObject\AbstractDataObject
{
    public function getName($value)
    {
        $this->getAttribute('name');
    }

    public function setName($value)
    {
        $this->setAttribute('name', $value);
    }
}
```

Additionally, if you want to block any type of magic assignment (meaning all assignment would have to be done through the `setAttribute()` or `setAttributes()` methods, or your own setters), you can disable magic assignment as follows:

```php
class TestDataObject extends \Czim\DataObject\AbstractDataObject
{
    protected $magicAssignment = false;

    ...
```

Attempts to set attributes on the DataObject by magic or array access will then throw an `UnassignableAttributeException`.


### Optionally add validation rules for attributes

```php
class YourDataObject extends \Czim\DataObject\AbstractDataObject
{
    protected $rules = [
        'name' => 'required|string',
        'list' => 'array|min:1',
    ];
}
```

Validating the data can be done as follows:

```php
    $dataObject = new YourDataObject();

    // validate() returns a boolean, false if it does not follow the rules
    if ( ! $dataObject->validate()) {

        $messages = $dataObject->messages();

        // messages() returns a standard Laravel MessageBag
        dd( $messages->first() );
    }
```

Messages are a `MessageBag` object generated by the Validator (whatever is behind the Laravel Facade `Validator`).


## Validation

To use the extra Validation features for nested DataObject validation rules and better array validation, load the ServiceProvider for this package.
This is the only reason to load the ServiceProvider; this package does not itself require the Provider to function.

Add this line of code to the providers array located in your `config/app.php` file:

```php
    Czim\DataObject\DataObjectServiceProvider::class,
```

Note that this will rebind the `Validator` facade, so if you have done this yourself, you may instead want to use the provided validation Traits to add to your own extended validator class.

Read more information about [the validation (traits) here](VALIDATION.md).


## Castable Data Object

You may opt to extend the `Czim\DataObject\CastableDataObject`.
Besides the standard features, this also includes the possibility of casting its properties to scalar values or (nested) data objects. This works similarly to Eloquent's `$casts` property (with some minor differences).

By overriding a protected `casts()` method, it is possible to set a cast type per attribute key:

```php
<?php
protected function casts()
{
    return [
        'check' => 'boolean',
        'count' => 'integer',
        'price' => 'float',
    ];
}
```

This has the effect of casting each property to its set type before returning it.

```php
<?php
$object = new YourDataObject([
    'check' => 'truthy value',
    'price' => 45,
]);

$object->check;     // true
$object['price'];   // 45.0
$object->count;     // 0 (instead of null)
```

### toArray Casting

Cast types are also applied for the `toArray()` method of the data object.
This means that unset properties will be present in the array as their default value (`false` for boolean, `0.0` for float, etc.).

To disable this behaviour, set `$castToArray` to `false`.
This will entirely disable casting values on `toArray()`.


### Nested Object Casting

More useful than scalar-casting, is object casting. This will allow you to create a tree of nested objects that, if set, can be invoked fluently.

Given casts that are set as follows:

```php
<?php
class RootDataObject extends \Czim\DataObject\CastableDataObject
{
    protected function casts()
    {
        return [
            'some_object' => YourDataObject::class,
            'object_list' => YourDataObject::class . '[]',
        ];
    }
}
```

And with the following data example, you can access the data by property:

```php
<?php
$data = [
    'some_object' => [
        'type' => 'peaches',
    ],
    'object_list' => [
        ['type' => 'cucumbers'],
        ['type' => 'sherry'],
    ],
];

$object = new RootDataobject($data);

$object->some_object;           // instance of YourDataObject
$object->some_object->type;     // peaches
$object->object_list[1]->type;  // sherry
```

Note that unset or `null` values will return `null`, not an empty data object. Non-array values will cause exceptions to be thrown on being interpreted as data objects.

This behaviour can be changed by setting the `$castUnsetObjects` property to `true`: unset attributes with an object cast will then be cast as an empty instance of that object class.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Credits

- [Coen Zimmerman][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/czim/laravel-dataobject.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/czim/laravel-dataobject.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/czim/laravel-dataobject
[link-downloads]: https://packagist.org/packages/czim/laravel-dataobject
[link-author]: https://github.com/czim
[link-contributors]: ../../contributors
