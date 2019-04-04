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

        $formats = [
            NumberFormat::FORMAT_NUMBER => [
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
            NumberFormat::FORMAT_NUMBER_00 => [
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
            NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1 => [
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
            NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2 => [
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
            NumberFormat::FORMAT_CURRENCY_EUR => [
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
            NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE => [
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
            NumberFormat::FORMAT_CURRENCY_USD => [
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
            NumberFormat::FORMAT_CURRENCY_USD_SIMPLE => [
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
            NumberFormat::FORMAT_PERCENTAGE => [
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
            NumberFormat::FORMAT_PERCENTAGE_00 => [
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
            NumberFormat::FORMAT_TEXT => [
                [
                    'class' => NumberStylePartString::class,
                    'attributes' => [
                        'text' => '@',
                    ],
                ],
            ],
        ];

        foreach ($formats as $format => $components) {
            $style = NumberStyle::build($format);
            $parts = $style->getParts();
            $this->assertCount(count($components), $parts);
            foreach ($components as $i => $component) {
                if (isset($parts[$i])) {
                    $part = $parts[$i];

                    // test part type
                    $partType = get_class($part);
                    $this->assertEquals($component['class'], $partType, 'Failed asserting that format part has expected class');

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

    function testBuildColorFormat() {
        $format = '[red]0';
        $style = NumberStyle::build($format);

        $this->assertEquals('red', $style->getColor());
    }

    function testBuildBasicConditions() {
        $format = '0.00;0.00;0;@';

        $conditionalFormats = [
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
        ];

        $styleWithConditions = NumberStyle::build($format);

        $conditionalStyles = $styleWithConditions->getConditionalStyles();
        $this->assertIsArray($conditionalStyles);
        $this->assertCount(count($conditionalFormats), $conditionalStyles, sprintf('Invalid count of conditional formats found in format "%s"', $format));

        foreach ($conditionalStyles as $k => $definition) {
            $conditionalFormat = $conditionalFormats[$k];
            
            $this->assertArrayHasKey('condition', $definition);
            $condition = $definition['condition'];
            $this->assertEquals(NumberFormatCondition::class, get_class($condition), 'Invalid condition object');
            $this->assertEquals($conditionalFormat['condition'], $condition->getComparator(), sprintf('Comparator for condition invalid on conditional format #%s', $k));
            if(array_key_exists('condition_value', $conditionalFormat)) {
                $this->assertEquals($conditionalFormat['condition_value'], $condition->getValue(), sprintf('Comparator value for condition invalid on conditional format #%s', $k));
            }

            $this->assertArrayHasKey('style', $definition);
            $style = $definition['style'];
            $this->assertEquals(NumberStyle::class, get_class($style), 'Invalid style object');

            

            $parts = $style->getParts();
            $this->assertCount(count($conditionalFormat['parts']), $parts);
            foreach ($conditionalFormat as $i => $component) {
                if (isset($parts[$i])) {
                    $part = $parts[$i];

                    // test part type
                    $partType = get_class($part);
                    $this->assertEquals($component['class'], $partType, 'Failed asserting that format part has expected class');

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
