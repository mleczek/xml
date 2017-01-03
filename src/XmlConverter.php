<?php


namespace Mleczek\Xml;


use Mleczek\Xml\Exceptions\InvalidXmlFormatException;

class XmlConverter
{
    /**
     * @var Xmlable
     */
    protected $object;

    /**
     * @var string|XmlElement
     */
    protected $xml;

    public function __construct(Xmlable $object)
    {
        $this->object = $object;
        $this->refresh();
    }

    protected function build(XmlElement $root, $meta)
    {
        foreach ($meta as $name => $value) {
            // Used to create self-closing tags or attributes without value,
            // eq. ['dog' => ['@can_hau']] (equals ['dog' => [0 => '@can_hau']]).
            //                                      is_int ---^
            if (is_int($name)) {
                $name = $value;
                $value = null;
            }

            // TODO: Name and value validation

            // Attribute
            if ($name[0] === '@') {
                $name = substr($name, 1);

                $value = $this->getValue($value);
                $root->setAttribute($name, $value);

                continue;
            }

            // Element
            $element = new XmlElement($name);
            if (is_array($value)) {
                // Sub-elements
                $this->build($element, $value);
            } else {
                // Text element
                $value = $this->getValue($value);
                $element->setText($value);
            }

            $root->addChild($element);
        }

        return $root;
    }

    protected function getValue($key)
    {
        $value = $key;

        if (is_string($value)) {
            if ($key[0] === '=') {
                // If string starts with "=" symbol
                // then cut it and return plain string.
                $value = substr($key, 1);
            } else {
                // Get value using an object property
                $value = $this->object->$key;
            }
        }

        // Convert Xmlable to XML string
        if ($value instanceof Xmlable) {
            $value = (string)(new XmlConverter($value));
        }

        return $value;
    }

    public function refresh()
    {
        $data = $this->object->xml();

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
            throw new InvalidXmlFormatException("Method \\$ns::xml() must return string, XmlElement or array."); // TODO: Link to documentation
        }

        // Build XML from array metadata
        $root = new XmlElement('XmlConverter');
        $this->xml = $this->build($root, $data)->innerXml();
    }

    public function __toString()
    {
        return (string)($this->xml);
    }
}