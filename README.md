# Laravel Data Object

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/czim/laravel-dataobject.svg?branch=master)](https://travis-ci.org/czim/laravel-dataobject)
[![Latest Stable Version](http://img.shields.io/packagist/v/czim/laravel-dataobject.svg)](https://packagist.org/packages/czim/laravel-dataobject)

Basic framework for making standardized but flexible data objects.
This provides a class (and interfaces) to use to standardize your dependencies or return values with a generic dataobject.

All this really is, is a data storage class with magic getters and setters.
It is Arrayable, Jsonable and validatable.


## To Do

- Add means to add nested validation (trait, service provider + validation class)
- Add way to vendor:publish some standard validation messages for nested validation


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


### Optionally add validation rules that its attributes will be checked against  

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
        
        // messages() returns a standard Laravel ErrorBag
        dd( $messages->first() );
    }
```

Messages are a `MessageBag` object generated by the Validator (whatever is behind the Laravel Facade `Validator`).


## Validation

- To Do: Describe default/simple validation overwrite using ServiceProvider registration
- To Do: Describe separate trait to use with your own custom validator


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
