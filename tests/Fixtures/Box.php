<?php


namespace Mleczek\Xml\Tests\Fixtures;


use Mleczek\Xml\Xmlable;

class Box implements Xmlable
{
    public $width = 5;
    public $height = 4;
    public $length = 3;

    public function xml()
    {
        return "<box w=\"{$this->width}\" h=\"{$this->height}\" l=\"{$this->length}\"/>";
    }
}