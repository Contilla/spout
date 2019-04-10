<?php

/**
 * NumberformatBuilder
 * 
 * Builder to create new number format definitions. More or less this is a dummy class as all useful functionality is bundeled in \Box\Spout\Common\Entity\Style\NumberStyle
 * @see \Box\Spout\Common\Entity\Style\NumberStyle
 *
 * @copyright (c) 2019, Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 01.02.2019
 */

namespace Box\Spout\Writer\Common\Creator\Style;

/**
 * NumberformatBuilder
 * 
 * Builder to create new number format definitions. More or less this is a dummy class as all useful functionality is bundeled in \Box\Spout\Common\Entity\Style\NumberStyle
 * @see \Box\Spout\Common\Entity\Style\NumberStyle
 *
 * @copyright (c) 2019, Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 01.02.2019
 */
class NumberStyleBuilder {
    
    /**
     * @var \Box\Spout\Common\Entity\Style\NumberStyle
     */
    protected $numberStyle=null;
    
    public function __construct()
    {
        $this->numberStyle = new \Box\Spout\Common\Entity\Style\NumberStyle();
    }
    
    /**
     * Set the format code
     * 
     * This will overwrite the internal numberstyle by the new definition
     * 
     * @param string $formatCode
     * @return $this
     */
    public function setFormatCode($formatCode) {
        $this->numberStyle = \Box\Spout\Common\Entity\Style\NumberStyle::build($formatCode);
        
        return $this;
    }
    
    public function setColor($color) {
        $this->numberStyle->setColor($color);
        
        return $this;
    }
    
    /**
     * Get the format definition
     * 
     * @return \Box\Spout\Common\Entity\Style\NumberStyle
     */
    public function build() {
        return $this->numberStyle;
    }
    
    public function getFormat() {
        return $this->numberStyle;
    }
}
