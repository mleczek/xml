<?php


namespace Mleczek\Xml\Tests;


use Mleczek\Xml\Exceptions\InvalidXmlFormatException;
use Mleczek\Xml\Exceptions\MissingXmlFormatException;
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

            'dot notation' => [
                [
                    'dot' => (object)[
                        'notation' => [
                            'example' => 'foo',
                        ]
                    ]
                ],
                [
                   'test' => 'dot.notation.example',
                ],
                '<test>foo</test>',
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

        // Bind properties to xmlable mock
        foreach($properties as $name => $value) {
            $xmlable->{$name} = $value;
        }

        // Replace all {prop_name} occurs in expected string
        // with an appropriate property value with specified name.
        preg_match_all('/\{([^\}]+)\}/', $expected, $matches);
        foreach ($matches[1] as $name) {
            $value = $properties[$name];
            $expected = str_replace('{' . $name . '}', $value, $expected);
        }

        // Compare result with expected string
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

        // Bind properties to xmlable mock
        foreach($properties as $name => $value) {
            $xmlable->{$name} = $value;
        }

        // Compare result with expected string
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

    public function testObjectToXml()
    {
        $meta = ['dog' => ['@name' => 'name']];
        $object = (object)['name' => 'Gapa'];
        $expected = '<dog name="Gapa"/>';

        $converter = new XmlConverter($object, $meta);
        $this->assertEquals($expected, (string)$converter);
    }

    public function testArrayToXml()
    {
        $meta = ['dog' => ['@name' => 'name']];
        $object = ['name' => 'Gapa'];
        $expected = '<dog name="Gapa"/>';

        $converter = new XmlConverter($object, $meta);
        $this->assertEquals($expected, (string)$converter);
    }

    public function testMissingXmlFormat()
    {
        $this->expectException(MissingXmlFormatException::class);
        new XmlConverter([]);
    }
}