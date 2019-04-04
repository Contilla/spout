<?php

/**
 * NumberStyleString
 * 
 * Description of NumberStyleString
 *
 * @copyright (c) Expression year is undefined on line 7, column 21 in Templates/Scripting/PHPClass.php., Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 13.03.2019
 */

namespace Box\Spout\Common\Entity\Style;

use Exception;

/**
 * NumberStyleString
 * 
 * Description of NumberStyleString
 *
 * @copyright (c) Expression year is undefined on line 21, column 21 in Templates/Scripting/PHPClass.php., Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 13.03.2019
 */
class NumberStylePartString implements NumberStylePartInterface {
    /**
     * @var string
     */
    private $text;
    
    /**
     * Set the text value of the part
     * 
     * @param string $text
     * @return $this
     * @throws Exception
     */
    public function setText($text) {
        if(!is_string($text)) {
            throw new Exception('Text must be a string');
        }
        $this->text=$text;
        
        return $this;
    }
    
    /**
     * Get the text value of the part
     * 
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Builds a NumberStylePartString that holds the provided string
     * 
     * @param string $formatPart
     * @return \Box\Spout\Common\Entity\Style\NumberStylePartString
     */
    public static function build($formatPart) {
        $entity = new NumberStylePartString();
        $entity->setText($formatPart);
        
        return $entity;
    }

}
