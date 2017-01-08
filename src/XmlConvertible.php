<?php


namespace Mleczek\Xml;


trait XmlConvertible
{
    public function toXml(array $meta = null)
    {
        return XmlElement::XmlDeclaration . (string)(new XmlConverter($this, $meta));
    }
}