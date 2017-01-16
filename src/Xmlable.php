<?php


namespace Mleczek\Xml;


interface Xmlable
{
    /**
     * Get object xml representation
     * (plain or meta description).
     *
     * @return array|string|XmlElement
     */
    public function xml();
}