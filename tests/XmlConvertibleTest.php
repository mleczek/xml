<?php


namespace Mleczek\Xml\Tests;


use Mleczek\Xml\Xmlable;
use Mleczek\Xml\XmlConvertible;
use Mleczek\Xml\XmlElement;
use PHPUnit\Framework\TestCase;

class XmlableFixture implements Xmlable
{
    use XmlConvertible;

    public function xml()
    {
        return '<dog/>';
    }
}

class XmlConvertibleTest extends TestCase
{
    public function testXmlableWithStringXml()
    {
        $xmlable = new XmlableFixture();

        $this->assertEquals(XmlElement::XmlDeclaration . $xmlable->xml(), $xmlable->toXml());
    }

    public function testXmlDeclaration()
    {
        $declaration = '<?xml version="1.0" encoding="UTF-8"?>';

        $this->assertEquals(XmlElement::XmlDeclaration, $declaration);
    }

    public function testOverloadingXmlMeta()
    {
        $xmlable = new XmlableFixture();

        $xml = '<cat/>';
        $meta = ['cat'];

        $this->assertEquals(XmlElement::XmlDeclaration . $xml, $xmlable->toXml($meta));
    }
}