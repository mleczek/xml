<?php


namespace Mleczek\Xml\Tests;


use Mleczek\Xml\Xmlable;
use Mleczek\Xml\XmlConvertible;
use Mleczek\Xml\XmlElement;
use PHPUnit\Framework\TestCase;

class XmlConvertibleTest extends TestCase
{
    public function testXmlableWithStringXml()
    {
        $xmlable = new Fixtures\XmlableFixture();

        $this->assertEquals(XmlElement::XmlDeclaration . $xmlable->xml(), $xmlable->toXml());
    }

    public function testXmlDeclaration()
    {
        $declaration = '<?xml version="1.0" encoding="UTF-8"?>';

        $this->assertEquals(XmlElement::XmlDeclaration, $declaration);
    }

    public function testOverloadingXmlMeta()
    {
        $xmlable = new Fixtures\XmlableFixture();

        $xml = '<cat/>';
        $meta = ['cat'];

        $this->assertEquals(XmlElement::XmlDeclaration . $xml, $xmlable->toXml($meta));
    }

    public function testToXmlWithoutXmlableClass()
    {
        $obj = new Fixtures\NotXmlableFixture();
        $xml = '<result><prop>val</prop></result>';

        $this->assertEquals(XmlElement::XmlDeclaration . $xml, $obj->toXml());
    }

    public function testToXmlAs()
    {
        $obj = new Fixtures\NotXmlableFixture();
        $root = 'data';
        $xml = "<$root><prop>val</prop></$root>";

        $this->assertEquals(XmlElement::XmlDeclaration . $xml, $obj->toXmlAs($root));
    }
}