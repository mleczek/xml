<?php


namespace Mleczek\Xml\Tests;


use Mleczek\Xml\Xmlable;
use Mleczek\Xml\XmlConverter;
use Mleczek\Xml\XmlElement;
use PHPUnit\Framework\TestCase;

class XmlConverterTestCase extends TestCase
{
    /**
     * @return array [properties, expected, xml_meta]
     */
    public function xmlProvider()
    {
        return [
            'string format' => [
                [],
                '<dog id="5"/>',
                '<dog id="5"/>',
            ],
            'XmlElement' => [
                [],
                '<dog/>',
                new XmlElement('dog'),
            ],
            'self-closing root' => [
                [],
                '<dog/>',
                ['dog'],
            ],
            'self-closing root #2' => [
                [],
                '<dog/>',
                ['dog' => []],
            ],
            'const attribute' => [
                [],
                '<dog type="animal"/>',
                [
                    'dog' => [
                        '@type' => '=animal',
                    ]
                ],
            ],
            'attribute without value' => [
                [],
                '<dog hau cantMiau/>',
                [
                    'dog' => [
                        '@hau',
                        '@cantMiau' => null,
                    ],
                ],
            ],
            'attribute without value #2' => [
                [],
                '<dog hau/>',
                [
                    'dog' => [
                        '@hau' => null
                    ],
                ],
            ],
            'dynamic attribute' => [
                [
                    'no' => 5,
                ],
                '<dog id="{no}"/>',
                [
                    'dog' => [
                        '@id' => 'no',
                    ]
                ],
            ],
            'self-closing sub-element' => [
                [],
                '<dog><hau/></dog>',
                [
                    'dog' => ['hau'],
                ]
            ],
            'attribute and sub-element' => [
                [
                    'id' => 5,
                    'name' => 'hau',
                ],
                '<dog id="{id}"><name>{name}</name></dog>',
                [
                    'dog' => [
                        '@id' => 'id',
                        'name' => 'name',
                    ]
                ],
            ],
            'nested sub-elements' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Smith',
                ],
                '<person><name><first>{first_name}</first><last>{last_name}</last></name></person>',
                [
                    'person' => [
                        'name' => [
                            'first' => 'first_name',
                            'last' => 'last_name',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider xmlProvider
     */
    public function testArrayXml($properties, $expected, $xml_meta)
    {
        $xmlable = \Mockery::mock(Xmlable::class);
        $xmlable->shouldReceive('xml')->andReturn($xml_meta);

        foreach ($properties as $name => $value) {
            $xmlable->{$name} = $value;
            $expected = str_replace('{'.$name.'}', $value, $expected);
        }

        $converter = new XmlConverter($xmlable);
        $this->assertEquals($expected, (string)$converter);
    }
}