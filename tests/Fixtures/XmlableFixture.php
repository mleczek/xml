<?php
namespace Mleczek\Xml\Tests\Fixtures;

use Mleczek\Xml\Xmlable;
use Mleczek\Xml\XmlConvertible;

class XmlableFixture implements Xmlable
{
    use XmlConvertible;

    public function xml()
    {
        return '<dog/>';
    }
}