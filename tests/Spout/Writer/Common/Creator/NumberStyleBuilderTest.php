<?php

/**
 * NumberStyleBuilderTest
 * 
 * Description of NumberStyleBuilderTest
 *
 * @copyright (c) 2019, Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 10.04.2019
 */

namespace Box\Spout\Writer\Common\Creator\Style;

use PHPUnit\Framework\TestCase;

/**
 * NumberStyleBuilderTest
 * 
 * Description of NumberStyleBuilderTest
 *
 * @copyright (c) 2019, Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 10.04.2019
 */
class NumberStyleBuilderTest extends TestCase {
    
    /**
     * @return void
     */
    public function testValidInstance() {
        $noConstructorParams = new NumberStyleBuilder();
        $this->expectNotToPerformAssertions();
    }
    
    public function testSetFormat() {
        $builder = new NumberStyleBuilder();
        $builder->setFormatCode('[red]0.00');
        $style = $builder->build();
        
        $this->assertEquals('red', $style->getColor(), 'Expected color from predefined format not found');
    }
    
    public function testSetColor() {
        $color = 'yellow';
        
        $builder = new NumberStyleBuilder();
        $builder->setColor($color);
        $style = $builder->build();
        
        $this->assertEquals($color, $style->getColor(), 'Expected color not found');
    }
    
    public function testBuild() {
        $builder = new NumberStyleBuilder();
        $style = $builder->build();
        $this->assertEquals(\Box\Spout\Common\Entity\Style\NumberStyle::class, get_class($style), 'Builder doesn\'t generates a Numberstyle object.');
    }
    
}
