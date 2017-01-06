# Convert PHP objects to XML

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

Classes that are convertible to XML must implement the `Mleczek\Xml\Xmlable` interface with `xml` method:

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

The [XML body](#xml-body) will be described in the next chapter.

Class may be converted to XML using `Mleczek\Xml\XmlConverter` which implements the `__toString`, `outerXml` and `innerXml` methods returning XML as string:

```php
$dog = new Dog();
$converter = new XmlConverter($dog);
$xml = (string)$converter;
// $xml = $converter->outerXml(); // equals __toString
// $xml = $converter->innerXml();
```

Library contains also the shorthand to cast `Mleczek\Xml\Xmlable` class to XML string using `Mleczek\Xml\XmlConvertible` trait:

```php
use Mleczek\Xml\Xmlable;
use Mleczek\Xml\XmlConvertible;

class Dog implements Xmlable
{
    use XmlConvertible;

    public function xml()
    {
        // XML Body...
    }
}
```

`Mleczek\Xml\XmlConvertible` implements the `toXml` method which returns the outer XML string:

```php
$dog = new Dog();
$xml = $dog->toXml();
```

## XML Body

This chapter describe the data which should by returned by the `xml` method.

XML Body can be implemented using 3 ways:

- [Array](#array) - meta description
- [String](#string) - plain XML string
- [XmlElement](#xmlelement)

### Array

The meta language allow defining XML body, including:

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

As you can see above if you define value equal `full_name` then **`XmlConverter` will look for `full_name` property in the object**.

You can also define more elements, self-closing elements and **constant values**:

```php
public function xml()
{
    // <dog><hau/><id>5</id></dog>
    return [
        'dog' => [
            'hau',        // self-closing elements
            'id' => '=5', // use "=" prefix for constant values
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

Using `toXml` method provided with `Mleczek\Xml\XmlConvertible` trait automatically add XML declaration:

```xml
<?xml version="1.0" encoding="UTF-8"?>
```

Above declaration is available as constant value at `Mleczek\Xml\XmlElement::XmlDeclaration`.

## Contributing

Thank you for considering contributing! If you would like to fix a bug or propose a new feature, you can submit a Pull Request.

## License

The library is licensed under the [MIT license](https://opensource.org/licenses/MIT).