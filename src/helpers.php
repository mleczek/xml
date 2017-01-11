<?php

use Mleczek\Xml\StructureAnalyser;
use Mleczek\Xml\Xmlable;
use Mleczek\Xml\XmlConverter;
use Mleczek\Xml\XmlElement;

if (!function_exists('toXml')) {
    /**
     * @param Xmlable|object|array $object
     * @param array|null $meta
     * @return string
     */
    function toXml($object, array $meta = null)
    {
        if (!($object instanceof Xmlable) && is_null($meta)) {
            $meta = (new StructureAnalyser())->analyse($object);
        }

        return XmlElement::XmlDeclaration . (string)(new XmlConverter($object, $meta));
    }

    /**
     * @param object|array $object
     * @param string $root_name
     * @return string
     */
    function toXmlAs($object, $root_name)
    {
        if($object instanceof Xmlable) {
            throw new InvalidArgumentException("For objects implementing the Xmlable interface use toXml method.");
        }

        $meta = (new StructureAnalyser())->analyse($object, $root_name);
        return XmlElement::XmlDeclaration . (string)(new XmlConverter($object, $meta));
    }
}