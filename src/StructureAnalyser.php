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
    /**
     * @param array|object $data
     * @param string $root_name
     * @return array
     */
    public function analyse($data, $root_name = 'result')
    {
        return $this->parse($data, $root_name);
    }

    protected function parse($data, $root_name, $prefix = '')
    {
        $xml_lang = [];

        foreach ($data as $key => $value) {
            // Used to create self-closing elements.
            if (is_int($key)) {
                $key = $value;
                $value = null;
            }

            $xml_lang[] = $this->parseKeyValue($key, $value, $prefix);
        }

        return [$root_name => $xml_lang];
    }

    private function parseKeyValue($key, $value, $prefix = '')
    {
        if (!is_string($key)) {
            return [];
        }

        if (is_bool($value) || is_null($value)) {
            return [$key => $value];
        }

        if (is_array($value) || is_object($value)) {
            // A $prefix is always used with $key ($prefix . $key)
            // so it must ends with the "." symbol or be an empty string.
            return $this->parse($value, $key, "$prefix$key.");
        }

        return [$key => $prefix . $key];
    }
}