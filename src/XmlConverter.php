<?php


namespace Mleczek\Xml;


use Mleczek\Xml\Exceptions\InvalidXmlFormatException;
use Mleczek\Xml\Exceptions\MissingXmlFormatException;
use Traversable;

/**
 * Convert object/array to XML using custom array description.
 * Objects implementing Xmlable interface provide own XML body structure description.
 *
 * @package Mleczek\Xml
 * @see https://github.com/mleczek/xml#xml-body
 */
class XmlConverter
{
    const ATTR_PREFIX = '@';
    const CONST_PREFIX = '=';

    /**
     * @var Xmlable|object|array
     */
    protected $object;

    /**
     * @var string|XmlElement
     */
    protected $xml;

    /**
     * XmlConverter constructor.
     *
     * @param Xmlable|object|array $object
     * @param array|null $meta
     * @throws MissingXmlFormatException
     */
    public function __construct($object, array $meta = null)
    {
        if(!($object instanceof Xmlable) && is_null($meta)) {
            $xmlable = Xmlable::class;
            throw new MissingXmlFormatException("XmlConverter require second argument to be xml array format if first argument doesn't implement the $xmlable interface.");
        }

        $this->object = $object;
        $this->xml = $meta;

        $this->refresh();
    }

    /**
     * Build record for each $meta row ([$key => $value]).
     *
     * @param XmlElement $root
     * @param array|Traversable $meta
     * @return XmlElement
     */
    protected function build(XmlElement $root, $meta)
    {
        foreach ($meta as $key => $value) {
            // Used to create self-closing tags, attributes without value or merging array,
            // eq. ['dog' => ['@can_hau']] (equals ['dog' => [0 => '@can_hau']]).
            //                                      is_int ---^
            //
            // Merging array example:
            // ['dog' => ['@attr', ['@other']]] (equals ['dog' => ['@attr', '@other']]).
            if (is_int($key)) {
                $key = $value;
                $value = null;
            }

            $this->buildFor($root, $key, $value);
        }

        return $root;
    }

    /**
     * If $key is:
     * 1. array|Traversable: continue building using extended metadata (merge array)
     * 2. string: attribute or element name
     * 3. Xmlable: convert to xml text value
     *
     * @param XmlElement $root
     * @param mixed $key
     * @param mixed $value
     * @return XmlElement
     * @throws InvalidXmlFormatException
     */
    protected function buildFor(XmlElement $root, $key, $value)
    {
        // 1. array: continue building using extended metadata (merge array)
        if (is_array($key) || $key instanceof Traversable) {
            return $this->build($root, $key);
        }

        // 2. string: attribute or element name
        if (is_string($key)) {
            if ($key[0] === self::ATTR_PREFIX) {
                $attr_name = substr($key, 1);
                return $this->buildAttrFor($root, $attr_name, $value);
            }

            return $this->buildNodeFor($root, $key, $value);
        }

        // 3. Xmlable: same as XmlElement
        if ($key instanceof Xmlable) {
            $element = new XmlConverter($key);
            return $root->setText($root->getText() . $element->asString());
        }

        $type = typeof($key);
        throw new InvalidXmlFormatException("Expected element name, attribute name (prefixed with '@' symbol), Xmlable or array, $type given.");
    }

    /**
     * If $value is:
     * 1. string: constant or property value
     * 2. null: attribute without name
     * 3. boolean (true): same as null
     * 4. boolean (false): skip attribute
     *
     * @param XmlElement $root
     * @param string $attr_name
     * @param mixed $value
     * @return XmlElement
     * @throws InvalidXmlFormatException
     */
    protected function buildAttrFor(XmlElement $root, $attr_name, $value)
    {
        // 1. string: constant or property value
        if (is_string($value)) {
            return $root->setAttribute($attr_name, $this->getValue($value));
        }

        // 2. null: attribute without name
        if (is_null($value)) {
            return $root->setAttribute($attr_name, null);
        }

        if (is_bool($value)) {
            // 3. boolean (true): same as null
            if ($value) {
                return $root->setAttribute($attr_name, null);
            }

            // 4. boolean (false): skip attribute
            return $root;
        }

        $type = typeof($value);
        throw new InvalidXmlFormatException("Expected string, null or boolean attribute value, $type given.");
    }

    /**
     * If $value is:
     * 1. string: constant or property text value
     * 2a. null: raw xml string
     * 2b. null: self-closing element
     * 3. boolean (true): same as null
     * 4. boolean (false): skip element
     * 5. array/Traversable: build nested element
     *
     * @param XmlElement $root
     * @param string $node_name
     * @param mixed $value
     * @return XmlElement
     * @throws InvalidXmlFormatException
     */
    protected function buildNodeFor(XmlElement $root, $node_name, $value)
    {
        $element = new XmlElement($node_name);

        // 1. string: constant or property text value
        if (is_string($value)) {
            $element->setText($this->getValue($value));
            return $root->addChild($element);
        }

        if (is_null($value)) {
            // 2a. null: raw xml string
            if(isset($node_name[0]) && $node_name[0] == self::CONST_PREFIX) {
                return $root->setText($root->getText() . substr($node_name, 1));
            }

            // 2b. null: self-closing element
            return $root->addChild($element);
        }

        if (is_bool($value)) {
            // 3. boolean (true): same as null
            if ($value) {
                return $root->addChild($element);
            }

            // 4. boolean (false): skip element
            return $root;
        }

        // 5. array: build nested element
        if (is_array($value) || $value instanceof Traversable) {
            $this->build($element, $value);
            return $root->addChild($element);
        }

        $type = typeof($value);
        throw new InvalidXmlFormatException("Expected string, null, boolean or array element value, $type given.");
    }

    /**
     * Get value for node or attribute.
     *
     * @param string $key
     * @return string
     */
    protected function getValue($key)
    {
        if (isset($key[0]) && $key[0] === self::CONST_PREFIX) {
            // If string starts with "=" symbol
            // then cut it and return plain string.
            return substr($key, 1);
        }

        // Get value using a dot notation
        $data = $this->object;
        foreach (explode('.', $key) as $step) {
            if (is_array($data)) {
                $data = $data[$step];
            } else {
                $data = $data->$step;
            }
        }

        return (string)$data;
    }

    /**
     * Rerun converter for previously specified object/array.
     *
     * @throws InvalidXmlFormatException
     */
    public function refresh()
    {
        // Use passed in constructor xml metadata
        // or get it from Xmlable object instance.
        $data = $this->xml;
        if (is_null($data)) {
            $data = $this->object->xml();
        }

        // Accept plain xml root element as string or object
        // (skip xml validation for string format).
        if (is_string($data) || $data instanceof XmlElement) {
            $this->xml = $data;
            return;
        }

        // Throw exception if data is neither string, XmlElement nor array.
        // Array must contain one element (root) with sub-elements (nodes).
        if (!is_array($data) || count($data) != 1) {
            $ns = get_class($this->object);
            throw new InvalidXmlFormatException("Method \\$ns::xml() must return string, XmlElement or array. More information at https://github.com/mleczek/xml#xml-body.");
        }

        // Build XML from array metadata
        $root = new XmlElement('XmlConverter');
        $this->xml = $this->build($root, $data)->innerXml();
    }

    /**
     * Get outer xml.
     *
     * @return string
     */
    public function asString()
    {
        return (string)($this->xml);
    }

    /**
     * Get outer xml.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->asString();
    }
}