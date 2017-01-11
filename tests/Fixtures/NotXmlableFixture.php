<?php
namespace Mleczek\Xml\Tests\Fixtures;

use Mleczek\Xml\XmlConvertible;

class NotXmlableFixture
{
    use XmlConvertible;

    public $prop = 'val';
}