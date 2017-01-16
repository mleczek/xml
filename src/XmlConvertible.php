<?php


namespace Mleczek\Xml;


trait XmlConvertible
{
    /**
     * Convert object to xml.
     *
     * @param array|null $meta
     * @return string
     */
    public function toXml(array $meta = null)
    {
        return toXml($this, $meta);
    }

    /**
     * Dynamic convert object to xml.
     *
     * @param $root_name
     * @return string
     */
    public function toXmlAs($root_name)
    {
        return toXmlAs($this, $root_name);
    }
}