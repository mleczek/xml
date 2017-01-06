<?php


namespace Mleczek\Xml;


use Mleczek\Xml\Exceptions\InvalidXmlFormatException;

class XmlElement implements Xmlable
{
    const XmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $text = '';

    /**
     * @var XmlElement[]
     */
    protected $children = [];

    /**
     * Key-value array where key is attribute name.
     * Use null for value to build attr without value.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * XmlElement constructor.
     * @param string $name
     * @param string $text
     * @param array $attributes
     */
    public function __construct($name, $text = null, array $attributes = [])
    {
        $this->setName($name);
        $this->setText($text);
        $this->setAttributes($attributes);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return XmlElement
     */
    public function setName($name)
    {
        // TODO: Name validation

        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        // TODO: Text validation

        $this->text = $text;
        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param XmlElement $node
     * @return $this
     */
    public function addChild(XmlElement $node)
    {
        $this->children[] = $node;
        return $this;
    }

    /**
     * @param XmlElement $node
     * @return $this
     */
    public function removeChild(XmlElement $node)
    {
        if (($key = array_search($node, $this->children)) !== false) {
            unset($this->children[$key]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * @param array $attributes
     * @return XmlElement
     */
    public function setAttributes($attributes)
    {
        $attributes = (array)$attributes;
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     * @throws InvalidXmlFormatException
     */
    public function setAttribute($name, $value)
    {
        if (!is_string($name)) {
            $type = typeof($name);
            throw new InvalidXmlFormatException("Attribute name must be a string, $type given.");
        }

        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeAttribute($name)
    {
        unset($this->attributes[$name]);
        return $this;
    }

    public function innerXml()
    {
        $children = '';
        foreach ($this->getChildren() as $child) {
            $children .= (string)$child;
        }

        return $this->getText() . $children;
    }

    public function outerXml()
    {
        $attributes = '';
        foreach ($this->getAttributes() as $name => $value) {
            if ($value === null) {
                // Self-closing attribute format.
                $attributes .= " $name";
            } else {
                // Full attribute format.
                $value = addslashes($value);
                $attributes .= " $name=\"$value\"";
            }
        }

        // Self-closing element format.
        if ($this->getText() === null && count($this->getChildren()) == 0) {
            return "<{$this->getName()}$attributes/>";
        }

        // Full element format.
        return "<{$this->getName()}$attributes>{$this->innerXml()}</{$this->getName()}>";
    }

    public function xml()
    {
        return $this->outerXml();
    }

    public function __toString()
    {
        return $this->outerXml();
    }
}