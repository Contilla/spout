<?php

/**
 * NumberStyleTest
 * 
 * Description of NumberStyleTest
 *
 * @copyright (c) 2019, Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 03.04.2019
 */

namespace Box\Spout\Common\Entity\Style;

use Box\Spout\Common\Exception\NumberFormatException;
use PHPUnit\Framework\TestCase;

/**
 * NumberStyleTest
 * 
 * Description of NumberStyleTest
 *
 * @copyright (c) 2019, Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 03.04.2019
 */
class NumberStyleTest extends TestCase {

    /**
     * Tests the structure of a NumberStyle against a given format declaration
     * 
     * @param \Box\Spout\Common\Entity\Style\NumberStyle $testStyle
     * @param array $formatDeclaration
     */
    private function _verifyNumberStyle($format, NumberStyle $testStyle, array $formatDeclaration) {
        if (isset($formatDeclaration['parts'])) {
            // test primary format
            $components = $formatDeclaration['parts'];
            $parts = $testStyle->getParts();
            $this->assertCount(count($components), $parts);
            foreach ($components as $i => $component) {
                if (isset($parts[$i])) {
                    $part = $parts[$i];

                    // test part type
                    $partType = get_class($part);
                    $this->assertEquals($component['class'], $partType, sprintf('Failed asserting that format part has expected class for format "%s"', $format));

                    // test part attributes
                    foreach ($component['attributes'] as $name => $value) {
                        $this->assertObjectHasAttribute($name, $part);
                        $methodName = 'get' . mb_convert_case($name[0], MB_CASE_UPPER) . substr($name, 1);
                        $this->assertEquals($value, $part->$methodName(), sprintf('Failed asserting that part attribute `%s` has expected value of %s for format "%s"', $name, var_export($value, true), $format));
                    }
                }
            }
        }

        if (isset($formatDeclaration['conditionals'])) {
            // format definitions with conditionals
            $conditionalFormats = $formatDeclaration['conditionals'];
            $conditionalStyles = $testStyle->getConditionalStyles();
            $this->assertIsArray($conditionalStyles);
            $this->assertCount(count($conditionalFormats), $conditionalStyles, sprintf('Invalid count of conditional formats found in format "%s"', $format));

            foreach ($conditionalStyles as $k => $definition) {
                $conditionalFormat = $conditionalFormats[$k];

                $this->assertArrayHasKey('condition', $definition);
                $condition = $definition['condition'];
                $this->assertEquals(NumberFormatCondition::class, get_class($condition), sprintf('Invalid condition object for format "%s"', $format));
                $this->assertEquals($conditionalFormat['condition'], $condition->getComparator(), sprintf('Comparator for condition invalid on part #%s in format "%s"', $k, $format));
                if (array_key_exists('condition_value', $conditionalFormat)) {
                    $this->assertEquals($conditionalFormat['condition_value'], $condition->getValue(), sprintf('Comparator value for condition invalid on part #%s in format "%s"', $k, $format));
                }

                $this->assertArrayHasKey('style', $definition);
                $style = $definition['style'];
                $this->assertEquals(NumberStyle::class, get_class($style), sprintf('Invalid style object for format "%s"', $format));



                $parts = $style->getParts();
                $this->assertCount(count($conditionalFormat['parts']), $parts);
                foreach ($conditionalFormat as $i => $component) {
                    if (isset($parts[$i])) {
                        $part = $parts[$i];

                        // test part type
                        $partType = get_class($part);
                        $this->assertEquals($component['class'], $partType, sprintf('Failed asserting that format part has expected class for format "%s"', $format));

                        // test part attributes
                        foreach ($component['attributes'] as $name => $value) {
                            $this->assertObjectHasAttribute($name, $part);
                            $methodName = 'get' . mb_convert_case($name[0], MB_CASE_UPPER) . substr($name, 1);
                            $this->assertEquals($value, $part->$methodName(), sprintf('Failed asserting that part attribute `%s` has expected value of %s for format "%s"', $name, var_export($value, true), $format));
                        }
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    public function testValidInstance() {
        $noConstructorParams = new NumberStyle();
        $this->expectNotToPerformAssertions();
    }

    /**
     * @return void
     */
    public function testBuildBasicNumberFormats() {
        // if an exception is expected, declare it
        //$this->expectException(InvalidNumberformatException::class);

        $formatDeclarations = [
            NumberFormat::FORMAT_NUMBER => [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 0,
                            'maxDecimalPlaces' => NULL,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => false,
                        ]
                    ],
                ],
            ],
            NumberFormat::FORMAT_NUMBER_00 => [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 2,
                            'maxDecimalPlaces' => 2,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => false,
                        ],
                    ],
                ],
            ],
            NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1 => [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 2,
                            'maxDecimalPlaces' => 2,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => true,
                        ],
                    ],
                ],
            ],
            NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2 => [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 2,
                            'maxDecimalPlaces' => 2,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => true,
                        ],
                    ],
                    [
                        'class' => NumberStylePartString::class,
                        'attributes' => [
                            'text' => '_-',
                        ],
                    ],
                ],
            ],
            NumberFormat::FORMAT_CURRENCY_EUR => [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 0,
                            'maxDecimalPlaces' => NULL,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => true,
                        ],
                    ],
                    [
                        'class' => NumberStylePartString::class,
                        'attributes' => [
                            'text' => '_-"€"',
                        ],
                    ],
                ],
            ],
            NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE => [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 2,
                            'maxDecimalPlaces' => 2,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => true,
                        ],
                    ],
                    [
                        'class' => NumberStylePartString::class,
                        'attributes' => [
                            'text' => '_-"€"',
                        ],
                    ],
                ],
            ],
            NumberFormat::FORMAT_CURRENCY_USD => [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartString::class,
                        'attributes' => [
                            'text' => '$',
                        ],
                    ],
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 0,
                            'maxDecimalPlaces' => NULL,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => true,
                        ],
                    ],
                    [
                        'class' => NumberStylePartString::class,
                        'attributes' => [
                            'text' => '_-',
                        ],
                    ],
                ],
            ],
            NumberFormat::FORMAT_CURRENCY_USD_SIMPLE => [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartString::class,
                        'attributes' => [
                            'text' => '$',
                        ],
                    ],
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 2,
                            'maxDecimalPlaces' => 2,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => true,
                        ],
                    ],
                    [
                        'class' => NumberStylePartString::class,
                        'attributes' => [
                            'text' => '_-',
                        ],
                    ],
                ],
            ],
            NumberFormat::FORMAT_PERCENTAGE => [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 0,
                            'maxDecimalPlaces' => NULL,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => false,
                        ],
                    ],
                    [
                        'class' => NumberStylePartString::class,
                        'attributes' => [
                            'text' => '%',
                        ],
                    ],
                ],
            ],
            NumberFormat::FORMAT_PERCENTAGE_00 => [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 2,
                            'maxDecimalPlaces' => 2,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => false,
                        ],
                    ],
                    [
                        'class' => NumberStylePartString::class,
                        'attributes' => [
                            'text' => '%',
                        ],
                    ],
                ],
            ],
            NumberFormat::FORMAT_TEXT => [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartString::class,
                        'attributes' => [
                            'text' => '@',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($formatDeclarations as $format => $formatDeclaration) {
            $style = NumberStyle::build($format);
            $this->_verifyNumberStyle($format, $style, $formatDeclaration);
        }
    }

    /**
     * Test parsing of color attribute in conditional formating
     */
    function testBuildColorFormat() {
        $testColors = ['black', 'white', 'red', 'green', 'blue', 'yellow', 'magenta', 'cyan'];
        for ($i = 0; $i < 16; $i++) {
            $testColors[] = sprintf('color%s', $i + 1);
        }

        foreach ($testColors as $color) {
            $format = sprintf('[%s]0', $color);
            $style = NumberStyle::build($format);

            $this->assertEquals($color, $style->getColor());
        }
    }

    function testBuildColorAndConditionalFormat() {
        $testColors = ['black', 'white', 'red', 'green', 'blue', 'yellow', 'magenta', 'cyan'];
        for ($i = 0; $i < 16; $i++) {
            $testColors[] = sprintf('color%s', $i + 1);
        }

        $testConditions = [
            NumberFormatCondition::COMPARE_GREATEREQUAL => '>=',
            NumberFormatCondition::COMPARE_GREATERTHAN => '>',
            NumberFormatCondition::COMPARE_EQUAL => '=',
            NumberFormatCondition::COMPARE_LOWERTHAN => '<',
            NumberFormatCondition::COMPARE_LOWEREQUAL => '<=',
        ];

        foreach ($testColors as $color) {


            foreach ($testConditions as $condition => $conditionString) {

                $conditionValue = rand();
                $format = sprintf('[%s][%s%s]0;0', $color, $conditionString, $conditionValue);
                $style = NumberStyle::build($format);

                $formatDeclaration = [
                    'class' => NumberStyle::class,
                    'description' => 'Primary (default) format of the declaration',
                    'parts' => [
                        [
                            'class' => NumberStylePartNumber::class,
                            'attributes' => [
                                'minDecimalPlaces' => 0,
                                'maxDecimalPlaces' => NULL,
                                'minIntegerPlaces' => 1,
                                'maxIntegerPlaces' => NULL,
                                'grouping' => false,
                            ]
                        ],
                    ],
                    'conditionals' => [
                        [
                            'condition' => $condition,
                            'condition_value' => $conditionValue,
                            'color' => $color,
                            'parts' => [
                                [
                                    'class' => NumberStylePartNumber::class,
                                    'attributes' => [
                                        'minDecimalPlaces' => 0,
                                        'maxDecimalPlaces' => NULL,
                                        'minIntegerPlaces' => 1,
                                        'maxIntegerPlaces' => NULL,
                                        'grouping' => false,
                                    ]
                                ],
                            ],
                        ],
                    ],
                ];

                $this->_verifyNumberStyle($format, $style, $formatDeclaration);
            }
        }
    }

    function testBuildBasicConditions() {
        $format = '0.00;0.00;0;@';

        $formatDeclaration = [
            'class' => NumberStyle::class,
            'description' => 'Primary (default) format of the declaration',
//            'parts' => [
//                [
//                    'class' => NumberStylePartNumber::class,
//                    'attributes' => [
//                        'minDecimalPlaces' => 2,
//                        'maxDecimalPlaces' => 2,
//                        'minIntegerPlaces' => 1,
//                        'maxIntegerPlaces' => NULL,
//                        'grouping' => false,
//                    ],
//                ]
//            ],
            'conditionals' => [
                [
                    'condition' => NumberFormatCondition::COMPARE_LOWERTHAN,
                    'condition_value' => 0,
                    'parts' =>
                    [
                        [
                            'class' => NumberStylePartNumber::class,
                            'attributes' => [
                                'minDecimalPlaces' => 2,
                                'maxDecimalPlaces' => 2,
                                'minIntegerPlaces' => 1,
                                'maxIntegerPlaces' => NULL,
                                'grouping' => false,
                            ],
                        ],
                    ],
                ],
                [
                    'condition' => NumberFormatCondition::COMPARE_GREATERTHAN,
                    'condition_value' => 0,
                    'parts' =>
                    [
                        [
                            'class' => NumberStylePartNumber::class,
                            'attributes' => [
                                'minDecimalPlaces' => 2,
                                'maxDecimalPlaces' => 2,
                                'minIntegerPlaces' => 1,
                                'maxIntegerPlaces' => NULL,
                                'grouping' => false,
                            ],
                        ],
                    ],
                ],
                [
                    'condition' => NumberFormatCondition::COMPARE_EQUAL,
                    'condition_value' => 0,
                    'parts' =>
                    [
                        [
                            'class' => NumberStylePartNumber::class,
                            'attributes' => [
                                'minDecimalPlaces' => 0,
                                'maxDecimalPlaces' => NULL,
                                'minIntegerPlaces' => 1,
                                'maxIntegerPlaces' => NULL,
                                'grouping' => false,
                            ],
                        ],
                    ],
                ],
                [
                    'condition' => NumberFormatCondition::COMPARE_STRING,
                    'parts' =>
                    [
                        [
                            'class' => NumberStylePartString::class,
                            'attributes' => [
                                'text' => '@',
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $styleWithConditions = NumberStyle::build($format);

        $this->_verifyNumberStyle($format, $styleWithConditions, $formatDeclaration);
    }

    function testBuildCustomConditionFirst() {
        $testConditions = [
            NumberFormatCondition::COMPARE_GREATEREQUAL => '>=',
            NumberFormatCondition::COMPARE_GREATERTHAN => '>',
            NumberFormatCondition::COMPARE_EQUAL => '=',
            NumberFormatCondition::COMPARE_LOWERTHAN => '<',
            NumberFormatCondition::COMPARE_LOWEREQUAL => '<=',
        ];

        foreach ($testConditions as $condition => $conditionString) {

            $conditionValue = rand();
            $format = sprintf('[%s%s]0.00;0.00', $conditionString, $conditionValue);

            $formatDeclaration = [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 2,
                            'maxDecimalPlaces' => 2,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => false,
                        ],
                    ]
                ],
                'conditionals' => [
                    [
                        'condition' => $condition,
                        'condition_value' => $conditionValue,
                        'parts' =>
                        [
                            [
                                'class' => NumberStylePartNumber::class,
                                'attributes' => [
                                    'minDecimalPlaces' => 2,
                                    'maxDecimalPlaces' => 2,
                                    'minIntegerPlaces' => 1,
                                    'maxIntegerPlaces' => NULL,
                                    'grouping' => false,
                                ],
                            ],
                        ],
                    ],
                ]
            ];

            $styleWithConditions = NumberStyle::build($format);
            $this->_verifyNumberStyle($format, $styleWithConditions, $formatDeclaration);
        }
    }

    function testBuildCustomConditionSecond() {

        $testConditions = [
            NumberFormatCondition::COMPARE_GREATEREQUAL => '>=',
            NumberFormatCondition::COMPARE_GREATERTHAN => '>',
            NumberFormatCondition::COMPARE_EQUAL => '=',
            NumberFormatCondition::COMPARE_LOWERTHAN => '<',
            NumberFormatCondition::COMPARE_LOWEREQUAL => '<=',
        ];

        foreach ($testConditions as $condition => $conditionString) {

            $conditionValue = rand();

            $format = sprintf('0.00;[%s%s]0.00', $conditionString, $conditionValue);

            $formatDeclaration = [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 2,
                            'maxDecimalPlaces' => 2,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => false,
                        ],
                    ]
                ],
                'conditionals' => [
                    [
                        'condition' => $condition,
                        'condition_value' => $conditionValue,
                        'parts' =>
                        [
                            [
                                'class' => NumberStylePartNumber::class,
                                'attributes' => [
                                    'minDecimalPlaces' => 2,
                                    'maxDecimalPlaces' => 2,
                                    'minIntegerPlaces' => 1,
                                    'maxIntegerPlaces' => NULL,
                                    'grouping' => false,
                                ],
                            ],
                        ],
                    ],
                ]
            ];

            $styleWithConditions = NumberStyle::build($format);
            $this->_verifyNumberStyle($format, $styleWithConditions, $formatDeclaration);
        }
    }

    function testBuildNegativeCondition() {
        $testConditions = [
            NumberFormatCondition::COMPARE_GREATEREQUAL => '>=',
            NumberFormatCondition::COMPARE_GREATERTHAN => '>',
            NumberFormatCondition::COMPARE_EQUAL => '=',
            NumberFormatCondition::COMPARE_LOWERTHAN => '<',
            NumberFormatCondition::COMPARE_LOWEREQUAL => '<=',
        ];

        foreach ($testConditions as $condition => $conditionString) {

            $conditionValue = rand() * (-1);

            $format = sprintf('[%s%s]0.00;0.00', $conditionString, $conditionValue);

            $formatDeclaration = [
                'class' => NumberStyle::class,
                'description' => 'Primary (default) format of the declaration',
                'parts' => [
                    [
                        'class' => NumberStylePartNumber::class,
                        'attributes' => [
                            'minDecimalPlaces' => 2,
                            'maxDecimalPlaces' => 2,
                            'minIntegerPlaces' => 1,
                            'maxIntegerPlaces' => NULL,
                            'grouping' => false,
                        ],
                    ]
                ],
                'conditionals' => [
                    [
                        'condition' => $condition,
                        'condition_value' => $conditionValue,
                        'parts' =>
                        [
                            [
                                'class' => NumberStylePartNumber::class,
                                'attributes' => [
                                    'minDecimalPlaces' => 2,
                                    'maxDecimalPlaces' => 2,
                                    'minIntegerPlaces' => 1,
                                    'maxIntegerPlaces' => NULL,
                                    'grouping' => false,
                                ],
                            ],
                        ],
                    ],
                ]
            ];

            $styleWithConditions = NumberStyle::build($format);
            $this->_verifyNumberStyle($format, $styleWithConditions, $formatDeclaration);
        }
    }

}
