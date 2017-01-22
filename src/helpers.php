<?php

use Mleczek\Xml\StructureAnalyser;
use Mleczek\Xml\Xmlable;
use Mleczek\Xml\XmlConverter;
use Mleczek\Xml\XmlElement;

if (!function_exists('toXml')) {
    /**
     * Convert object to XML.
     *
     * @param Xmlable|object|array $object
     * @param array|null $meta
     * @param boolean $xml_declaration
     * @return string
     */
    function toXml($object, array $meta = null, $xml_declaration = true)
    {
        if (!($object instanceof Xmlable) && is_null($meta)) {
            $meta = (new StructureAnalyser())->analyse($object);
        }

        $converter = new XmlConverter($object, $meta);
        if($xml_declaration) {
            return XmlElement::XmlDeclaration . $converter->asString();
        }

        return $converter->asString();
    }

    /**
     * Dynamic converts object to XML.
     *
     * @param object|array $object
     * @param string $root_name
     * @param boolean $xml_declaration
     * @return string
     */
    function toXmlAs($object, $root_name, $xml_declaration = true)
    {
        $meta = (new StructureAnalyser())->analyse($object, $root_name);

        return toXml($object, $meta, $xml_declaration);
    }
}