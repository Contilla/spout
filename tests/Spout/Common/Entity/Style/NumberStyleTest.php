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

use Box\Spout\Common\Exception\NumberformatException;
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
    public function testValidInstance()
    {
        $noConstructorParams = new NumberStyle();
        $this->expectNotToPerformAssertions();
    }
    
    /**
     * @return void
     */
    public function testBuildBasicNumberFormats()
    {
        //$this->expectException(InvalidNumberformatException::class);
        
        $formats = [
            NumberFormat::FORMAT_NUMBER,
            NumberFormat::FORMAT_NUMBER_00,
            NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            NumberFormat::FORMAT_CURRENCY_EUR,
            NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            NumberFormat::FORMAT_CURRENCY_USD,
            NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            NumberFormat::FORMAT_PERCENTAGE,
            NumberFormat::FORMAT_PERCENTAGE_00,
            NumberFormat::FORMAT_TEXT,
        ];
        
        foreach($formats as $format) {
            $style = NumberStyle::build($format);
        }
        
        $this->expectNotToPerformAssertions();
    }
    
    
    
}
