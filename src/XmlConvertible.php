<?php


namespace Mleczek\Xml;


trait XmlConvertible
{
    public function toXml()
    {
        return (string)(new XmlConverter($this));
    }
}