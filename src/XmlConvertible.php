<?php


namespace Mleczek\Xml;


trait XmlConvertible
{
    public function toXml()
    {
        return XmlElement::XmlDeclaration . (string)(new XmlConverter($this));
    }
}