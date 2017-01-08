<?php


namespace Mleczek\Xml\Tests;


use Mleczek\Xml\Exceptions\InvalidXmlFormatException;
use Mleczek\Xml\Xmlable;
use Mleczek\Xml\XmlConverter;
use Mleczek\Xml\XmlElement;
use PHPUnit\Framework\TestCase;

class XmlConverterTestCase extends TestCase
{
    /**
     * @return array [properties, xml, expected]
     */
    public function validXmlProvider()
    {
        return [
            'string format' => [
                [],
                '<dog id="5"/>',
                '<dog id="5"/>',
            ],

            'XmlElement' => [
                [],
                new XmlElement('dog'),
                '<dog/>',
            ],

            'self-closing root' => [
                [],
                ['dog'],
                '<dog/>',
            ],

            'self-closing root #2' => [
                [],
                ['dog' => []],
                '<dog/>',
            ],

            'const attribute' => [
                [],
                [
                    'dog' => [
                        '@type' => '=animal',
                    ]
                ],
                '<dog type="animal"/>',
            ],

            'attribute without value' => [
                [],
                [
                    'dog' => [
                        '@hau',
                        '@cantMiau' => null,
                    ],
                ],
                '<dog hau cantMiau/>',
            ],

            'attribute without value #2' => [
                [],
                [
                    'dog' => [
                        '@hau' => null
                    ],
                ],
                '<dog hau/>',
            ],

            'dynamic attribute' => [
                [
                    'no' => 5,
                ],
                [
                    'dog' => [
                        '@id' => 'no',
                    ]
                ],
                '<dog id="{no}"/>',
            ],

            'self-closing sub-element' => [
                [],
                [
                    'dog' => ['hau'],
                ],
                '<dog><hau/></dog>',
            ],

            'attribute and sub-element' => [
                [
                    'id' => 5,
                    'name' => 'hau',
                ],
                [
                    'dog' => [
                        '@id' => 'id',
                        'name' => 'name',
                    ]
                ],
                '<dog id="{id}"><name>{name}</name></dog>',
            ],

            'nested sub-elements' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Smith',
                ],
                [
                    'person' => [
                        'name' => [
                            'first' => 'first_name',
                            'last' => 'last_name',
                        ]
                    ]
                ],
                '<person><name><first>{first_name}</first><last>{last_name}</last></name></person>',
            ],

            'xmlable property' => [
                [],
                [
                    'test' => [
                        \Mockery::mock(Xmlable::class)
                            ->shouldReceive('xml')
                            ->andReturn('<mock/>')
                            ->getMock(),
                    ]
                ],
                '<test><mock/></test>',
            ],

            'merging arrays' => [
                [],
                [
                    'test' => [
                        '@attr_1',
                        ['@attr_2'],
                    ]
                ],
                '<test attr_1 attr_2/>'
            ],

            'merging empty arrays' => [
                [],
                [
                    'test' => [
                        '@attr_1',
                        [],
                    ]
                ],
                '<test attr_1/>'
            ],

            'boolean value' => [
                [],
                [
                    'test' => [
                        'no' => false,
                        'yes' => true,
                        '@no' => false,
                        '@yes' => true,
                    ]
                ],
                '<test yes><yes/></test>'
            ],
        ];
    }

    /**
     * @return array [properties, xml, exception]
     */
    public function invalidXmlProvider()
    {
        return [
            'invalid xml type' => [
                [],
                null,
                InvalidXmlFormatException::class,
            ],

            'invalid key type' => [
                [],
                [null],
                InvalidXmlFormatException::class,
            ],

            'invalid attr value' => [
                [],
                [
                    'test' => [
                        '@test' => 0.5
                    ]
                ],
                InvalidXmlFormatException::class,
            ],

            'invalid node value' => [
                [],
                [
                    'test' => [
                        'test' => 0.5
                    ]
                ],
                InvalidXmlFormatException::class,
            ],
        ];
    }

    /**
     * @dataProvider validXmlProvider
     */
    public function testValidXml($properties, $xml, $expected)
    {
        $xmlable = $this->createMock(Xmlable::class);
        $xmlable->method('xml')->willReturn($xml);

        foreach ($properties as $name => $value) {
            $xmlable->{$name} = $value;
            $expected = str_replace('{' . $name . '}', $value, $expected);
        }

        $converter = new XmlConverter($xmlable);
        $this->assertEquals($expected, (string)$converter);
    }

    /**
     * @dataProvider invalidXmlProvider
     */
    public function testInvalidXml($properties, $xml, $exception)
    {
        $xmlable = $this->createMock(Xmlable::class);
        $xmlable->method('xml')->willReturn($xml);

        foreach ($properties as $name => $value) {
            $xmlable->{$name} = $value;
            $expected = str_replace('{' . $name . '}', $value, $expected);
        }

        $this->expectException($exception);
        $converter = new XmlConverter($xmlable);
    }

    public function testInvalidXmlFormat()
    {
        $xmlable = $this->createMock(Xmlable::class);
        $xmlable->method('xml')->willReturn(null);

        $this->expectException(InvalidXmlFormatException::class);
        new XmlConverter($xmlable);
    }
}