<?php


namespace Mleczek\Xml\Tests;


use Mleczek\Xml\StructureAnalyser;
use Mleczek\Xml\XmlConverter;
use PHPUnit\Framework\TestCase;

class StructureAnalyserTest extends TestCase
{
    /**
     * @return array [input, expected]
     */
    public function validInputProvider()
    {
        return [
            'basic array' => [
                ['key' => 'value'],
                '<result><key>value</key></result>',
            ],

            'basic object' => [
                (object)['key' => 'value'],
                '<result><key>value</key></result>',
            ],

            'nested array' => [
                ['key' => [
                    'nkey' => 'val'
                ]],
                '<result><key><nkey>val</nkey></key></result>',
            ],

            'self-closing element' => [
                ['key'],
                '<result><key/></result>'
            ],

            'self-closing element #2' => [
                ['key' => null],
                '<result><key/></result>',
            ],

            'boolean value' => [
                [
                    'key1' => true,
                    'key2' => false,
                ],
                '<result><key1/></result>'
            ],

            'array of arrays' => [
                [['a'], ['b']],
                '<result><result><a/></result><result><b/></result></result>'
            ],

            'skip invalid keys' => [
                [0, 1 => null, 'test'],
                '<result><test/></result>'
            ],
        ];
    }

    /**
     * @dataProvider validInputProvider
     */
    public function testValidInput($input, $expected)
    {
        $xml_meta = (new StructureAnalyser())->analyse($input);
        $result = (string)(new XmlConverter($input, $xml_meta));

        $this->assertEquals($expected, $result);
    }
}