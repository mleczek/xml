# Convert PHP objects to XML

[![Latest Stable Version](https://poser.pugx.org/mleczek/xml/v/stable)](https://packagist.org/packages/mleczek/xml)
[![Build Status](https://travis-ci.org/mleczek/xml.svg)](https://travis-ci.org/mleczek/xml)
[![Coverage Status](https://coveralls.io/repos/github/mleczek/xml/badge.svg?branch=1.0)](https://coveralls.io/github/mleczek/xml?branch=1.0)
[![License](https://poser.pugx.org/mleczek/xml/license)](https://packagist.org/packages/mleczek/xml)

The goal of the this library is to provide an easy way to respond XML by REST API.

- [Installation](#installation)
- [Basic concepts](#basic-concepts)
- [XML Body](#xml-body)
  - [Array](#array)
    - [Elements](#elements)
    - [Attributes](#attributes)
  - [String](#string)
  - [XmlElement](#xmlelement)
- [XML Declaration](#xml-declaration)
- [Contributing](#contributing)
- [License](#license)

## Installation

Require this package with composer:

```
composer require mleczek/xml
```

## Basic concepts

Class may be converted to XML using `Mleczek\Xml\XmlConverter` which implements the `__toString`, `outerXml` and `innerXml` methods returning XML as string:

```php
$dog = new Dog();
$converter = new XmlConverter($dog);
$outerXml = (string)$converter;
$outerXml = $converter->outerXml(); // equals __toString
$innerXml = $converter->innerXml();
```

Library contains also the shorthand to cast objects to XML string using `Mleczek\Xml\XmlConvertible` trait (or helper functions):

```php
use Mleczek\Xml\Xmlable;
use Mleczek\Xml\XmlConvertible;

class Dog
{
    use XmlConvertible;
    
    public $id = 5;
}
```

`Mleczek\Xml\XmlConvertible` implements the `toXml` method (also available as a helper function) which returns the outer XML string (skipped `<?xml version="1.0" encoding="UTF-8"?>` part in examples for better readability):

```php
$dog = new Dog();
$xml = $dog->toXml(); // returns <result><id>5</id></result>
$xml = toXml($dog);   // without using XmlConvertible trait
```

By default root elements is `<result>`, you can change it calling `toXmlAs` method:

```php
$dog = new Dog();
$xml = $dog->toXmlAs('dog'); // returns <dog><id>5</id></dog>
$xml = toXmlAs($dog, 'dog'); // without using XmlConvertible trait
```

Above examples use `Mleczek\Xml\StructureAnalyser` class to determine the output XML structure, to get more control you can implement the `Mleczek\Xml\Xmlable` interface with `xml` method:

```php
use Mleczek\Xml\Xmlable;

class Dog implements Xmlable
{
    public function xml()
    {
        // XML Body...
    }
}
```

The [XML body](#xml-body) describes the ouput XML and will be described in the next chapter. Also the `toXml` method accepts optionally one argument ([array](#array)) which can be used to control the [XML body](#xml-body) structure:

```php
$dog = new Dog();
$xml = $dog->toXml(['cat']); // returns <cat/>
```

## XML Body

This chapter describe the data which should by returned by the `xml` method.

XML Body can be implemented using 3 ways:

- [Array](#array) - meta description
- [String](#string) - plain XML string
- [XmlElement](#xmlelement)

### Array

The meta language which allow defining XML body, including:

- [Elements](#elements)
- [Attributes](#attributes)

#### Elements

The basic example return self-closing root element:

```php
public function xml()
{
    // <dog/>
    return ['dog'];
}
```

You can add `<name>` node to the `<dog>` root using:

```php
public function xml()
{
    // <dog><name>{$this->full_name}</name></dog>
    return [
        'dog' => [
            'name' => 'full_name'
        ]
    ];
}
```

As you can see above if you define value equal `full_name` then **`XmlConverter` will look for `full_name` property in the object** (casted to string). If property stores other object or array then you can **retrieve nested property/key using dot notation** `address.city` (equals `$this->address->city` or `$this->address['city']`).

You can also define more elements, **self-closing elements** and **constant values**:

```php
public function xml()
{
    // <dog><hau/><hau2/><id>5</id></dog>
    return [
        'dog' => [
            'hau',          // self-closing element
            'hau2' => null, // self-closing element #2
            'id' => '=5',   // use "=" prefix for constant values
        ]
    ];
}
```

You can also **merge arrays** (without using PHP functions) and use **conditional output**:

```php
public function xml()
{
    $extra_elements = [];
    if($this->isSuperDog()) {
        $extra_elements = ['hau_power' => 5];
    }

    // $this->isSuperDog():
    // true: <dog><hau/><hau_power>5</hau_power></dog>
    // false: <dog><hau/></dog>
    return [
        'dog' => [
            'hau' => true,   // true -> self-closing element
            'miau' => false, // false -> skip element
            $extra_elements, // merge arrays
        ]
    ];
}
```

#### Attributes

Attributes works like the elements, the only difference is that you have to **prepend the name with *"@"* prefix**.

```php
public function xml()
{
    // <dog name="{$this->full_name}" canHau id="5"/>
    return [
        'dog' => [
            '@name' => 'full_name' 
            '@canHau',            // without value
            '@id' => '=5',        // use "=" prefix for constant values
        ]
    ];
}
```

Of course you can mix elements with attributes:

```php
public function xml()
{
    // <dog canHau><id>5</id></dog>
    return [
        'dog' => [
            '@canHau',
            'id' => '=5'
        ]
    ];
}
```

### String

Build and return plain XML string:

```php
use Mleczek\Xml\Xmlable;

class Dog implements Xmlable
{
    public function xml()
    {
        return '<dog/>';
    }
}
```

### XmlElement
 
**Not recommended** and not documented. See [source code](https://github.com/mleczek/xml/blob/master/src/XmlElement.php) for more information.

## XML Declaration

Using `toXml` method provided with `Mleczek\Xml\XmlConvertible` trait (or equivalent helper functions) automatically add XML declaration:

```xml
<?xml version="1.0" encoding="UTF-8"?>
```

Above declaration is available as constant value at `Mleczek\Xml\XmlElement::XmlDeclaration`.

## Contributing

Thank you for considering contributing! If you would like to fix a bug or propose a new feature, you can submit a Pull Request.

## License

The library is licensed under the [MIT license](https://opensource.org/licenses/MIT).