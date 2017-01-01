<?php


namespace Mleczek\Xml;


trait XmlTransformation
{
    public function toXml()
    {
        return (string)(new XmlTransformer($this));
    }
}