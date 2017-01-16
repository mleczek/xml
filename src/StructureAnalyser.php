<?php


namespace Mleczek\Xml;

/**
 * Generate XML body structure from any object or array
 * using the default predefined rules (automatic generation).
 *
 * @package Mleczek\Xml
 * @see https://github.com/mleczek/xml#array
 */
class StructureAnalyser
{
    const ROOT_NAME = 'result';

    /**
     * Get object's xml meta description in array format
     * (see docs for array format of Mleczek\Xml\Xmlable::xml method).
     *
     * @param array|object $data
     * @param string $root_name
     * @return array
     */
    public function analyse($data, $root_name = self::ROOT_NAME)
    {
        return $this->parse($data, $root_name);
    }

    /**
     * @param array|object $data
     * @param string $root_name
     * @param string $prefix
     * @return array
     */
    protected function parse($data, $root_name, $prefix = '')
    {
        $xml_lang = [];
        foreach ($data as $key => $value) {
            $xml_lang[] = $this->parseKeyValue($key, $value, $prefix);
        }

        return [$root_name => $xml_lang];
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @param string $prefix
     * @return array
     */
    private function parseKeyValue($key, $value, $prefix = '')
    {
        // Array inside array ([[...], [...], ...])
        // and nested elements (array or object).
        if(is_array($value) || is_object($value)) {
            $root_name = is_string($key) ? $key : self::ROOT_NAME;
            return $this->parse($value, $root_name, "$prefix$key.");
        }

        // Support self-closing elements
        if(is_int($key)) {
            $key = $value;
            $value = null;
        }

        // Skip parsing
        if(!is_string($key)) {
            return [];
        }

        // Conditional output and self-closing elements
        if (is_bool($value) || is_null($value)) {
            return [$key => $value];
        }

        return [$key => $prefix . $key];
    }
}