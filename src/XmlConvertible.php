<?php


namespace Mleczek\Xml;


trait XmlConvertible
{
    public function toXml(array $meta = null)
    {
        return toXml($this, $meta);
    }

    public function toXmlAs($root_name)
    {
        return toXmlAs($this, $root_name);
    }
}