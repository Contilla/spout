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
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartNumber',
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
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartNumber',
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
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartNumber',
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
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartNumber',
                    'attributes' => [
                        'minDecimalPlaces' => 2,
                        'maxDecimalPlaces' => 2,
                        'minIntegerPlaces' => 1,
                        'maxIntegerPlaces' => NULL,
                        'grouping' => true,
                    ],
                ],
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartString',
                    'attributes' => [
                        'text' => '_-',
                    ],
                ],
            ],
            NumberFormat::FORMAT_CURRENCY_EUR => [
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartNumber',
                    'attributes' => [
                        'minDecimalPlaces' => 0,
                        'maxDecimalPlaces' => NULL,
                        'minIntegerPlaces' => 1,
                        'maxIntegerPlaces' => NULL,
                        'grouping' => true,
                    ],
                ],
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartString',
                    'attributes' => [
                        'text' => '_-"€"',
                    ],
                ],
            ],
            NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE => [
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartNumber',
                    'attributes' => [
                        'minDecimalPlaces' => 2,
                        'maxDecimalPlaces' => 2,
                        'minIntegerPlaces' => 1,
                        'maxIntegerPlaces' => NULL,
                        'grouping' => true,
                    ],
                ],
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartString',
                    'attributes' => [
                        'text' => '_-"€"',
                    ],
                ],
            ],
            NumberFormat::FORMAT_CURRENCY_USD => [
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartString',
                    'attributes' => [
                        'text' => '$',
                    ],
                ],
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartNumber',
                    'attributes' => [
                        'minDecimalPlaces' => 0,
                        'maxDecimalPlaces' => NULL,
                        'minIntegerPlaces' => 1,
                        'maxIntegerPlaces' => NULL,
                        'grouping' => true,
                    ],
                ],
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartString',
                    'attributes' => [
                        'text' => '_-',
                    ],
                ],
            ],
            NumberFormat::FORMAT_CURRENCY_USD_SIMPLE => [
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartString',
                    'attributes' => [
                        'text' => '$',
                    ],
                ],
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartNumber',
                    'attributes' => [
                        'minDecimalPlaces' => 2,
                        'maxDecimalPlaces' => 2,
                        'minIntegerPlaces' => 1,
                        'maxIntegerPlaces' => NULL,
                        'grouping' => true,
                    ],
                ],
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartString',
                    'attributes' => [
                        'text' => '_-',
                    ],
                ],
            ],
            NumberFormat::FORMAT_PERCENTAGE => [
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartNumber',
                    'attributes' => [
                        'minDecimalPlaces' => 0,
                        'maxDecimalPlaces' => NULL,
                        'minIntegerPlaces' => 1,
                        'maxIntegerPlaces' => NULL,
                        'grouping' => false,
                    ],
                ],
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartString',
                    'attributes' => [
                        'text' => '%',
                    ],
                ],
            ],
            NumberFormat::FORMAT_PERCENTAGE_00 => [
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartNumber',
                    'attributes' => [
                        'minDecimalPlaces' => 2,
                        'maxDecimalPlaces' => 2,
                        'minIntegerPlaces' => 1,
                        'maxIntegerPlaces' => NULL,
                        'grouping' => false,
                    ],
                ],
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartString',
                    'attributes' => [
                        'text' => '%',
                    ],
                ],
            ],
            NumberFormat::FORMAT_TEXT => [
                [
                    'class' => 'Box\Spout\Common\Entity\Style\NumberStylePartString',
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

}
