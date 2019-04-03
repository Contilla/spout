<?php

/**
 * NumberformatBuilder
 * 
 * Builder to create new number format definitions
 *
 * @copyright (c) 2019, Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 01.02.2019
 */

namespace Box\Spout\Writer\Common\Creator\Style;

/**
 * NumberformatBuilder
 * 
 * Builder to create new number format definitions
 *
 * @copyright (c) 2019, Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 01.02.2019
 */
class NumberformatBuilder {
    
    /**
     * @var \Box\Spout\Common\Entity\Style\NumberFormat
     */
    protected $numberFormat=null;
    
    public function __construct()
    {
        $this->numberFormat = new \Box\Spout\Common\Entity\Style\NumberFormat();
    }
    
    /**
     * Set the format code
     * 
     * @param string $formatCode
     * @return $this
     */
    public function setFormatCode($formatCode) {
        $this->numberFormat->setFormatCode($formatCode);
        
        return $this;
    }
    
    /**
     * Get the format definition
     * 
     * @return \Box\Spout\Common\Entity\Style\NumberFormat
     */
    public function build() {
        return $this->numberFormat;
    }
    
    public function getFormat() {
        return $this->numberFormat;
    }
}
