<?php

/**
 * NumberStyleNumber
 * 
 * Description of NumberStyleNumber
 *
 * @copyright (c) Expression year is undefined on line 7, column 21 in Templates/Scripting/PHPClass.php., Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 12.03.2019
 */

namespace Box\Spout\Common\Entity\Style;

/**
 * NumberStyleNumber
 * 
 * Description of NumberStyleNumber
 *
 * @copyright (c) Expression year is undefined on line 21, column 21 in Templates/Scripting/PHPClass.php., Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 12.03.2019
 */
class NumberStylePartNumber implements NumberStylePartInterface {

    private $minDecimalPlaces = 0;
    private $maxDecimalPlaces = null;
    private $minIntegerPlaces = 1;
    private $maxIntegerPlaces = null;
    private $grouping = false;

    /**
     * Set the absolut number of digits after the decimal seperator
     * 
     * @param int $places
     * @return $this
     */
    public function setDecimalPlaces($places = 0) {
        $this
                ->setMinDecimalPlaces($places)
                ->setMaxDecimalPlaces($places);

        return $this;
    }

    /**
     * Set the minimum number of digits after the decimal seperator
     * 
     * @param int $places
     * @return $this
     * @throws \Exception
     */
    public function setMinDecimalPlaces($places = 0) {
        if (is_int($places)) {
            if ($places < 0) {
                throw new \Exception('Places must be an positive integer value or 0');
            }
        } else {
            throw new \Exception('Places must be an positive integer value or 0');
        }

        $this->minDecimalPlaces = $places;

        return $this;
    }

    /**
     * Get the minimum number of digits after the decimal seperator
     * 
     * @return int
     */
    public function getMinDecimalPlaces() {
        return $this->minDecimalPlaces;
    }

    /**
     * Set the maximum number of digits after the decimal seperator
     * 
     * @param int|null $places
     * @return $this
     * @throws \Exception
     */
    public function setMaxDecimalPlaces($places = null) {
        if ($places === 0) {
            $places = null;
        } elseif (is_int($places)) {
            if ($places < 0) {
                throw new \Exception('Places must be a positive integer value');
            }
        } elseif ($places === null) {
            
        } else {
            throw new \Exception('Places must be a positive integer value');
        }

        $this->maxDecimalPlaces = $places;

        return $this;
    }

    /**
     * Get the maximum number of digits after the decimal seperator
     * 
     * @return int|null
     */
    public function getMaxDecimalPlaces() {
        return $this->maxDecimalPlaces;
    }

    /**
     * Set the minimum number of digits before the decimal seperator
     * 
     * @param int $places
     * @return $this
     * @throws \Exception
     */
    public function setMinIntegerPlaces($places = 1) {
        if (is_int($places)) {
            if ($places < 1) {
                throw new \Exception('Places must be an integer value greater 1');
            }
        } else {
            throw new \Exception('Places must be an integer value greater 1');
        }

        $this->minIntegerPlaces = $places;

        return $this;
    }

    /**
     * Get the minimum number of digits before the decimal seperator
     * 
     * @return int
     */
    public function getMinIntegerPlaces() {
        return $this->minIntegerPlaces;
    }

    /**
     * Set the maximum number of digits before the decimal seperator
     * 
     * @param int|null $places
     * @return $this
     * @throws \Exception
     */
    public function setMaxIntegerPlaces($places = null) {
        if ($places === 0) {
            $places = null;
        } elseif (is_int($places)) {
            if ($places < 0) {
                throw new \Exception('Places must be a positive integer value');
            }
        } elseif ($places === null) {
            
        } else {
            throw new \Exception('Places must be a positive integer value');
        }

        $this->maxIntegerPlaces = $places;

        return $this;
    }

    /**
     * Get the maximum number of digits before the decimal seperator
     * 
     * @return int|null
     */
    public function getMaxIntegerPlaces() {
        return $this->maxIntegerPlaces;
    }

    /**
     * Set grouping (thousands seperator) of the format
     * 
     * @param boolean $enabled
     * @return $this
     */
    public function setGrouping($enabled = true) {
        $this->grouping = ($enabled == true);

        return $this;
    }

    /**
     * Get grouping (thousands seperator) state of the format
     * 
     * @return boolean
     */
    public function getGrouping() {
        return $this->grouping;
    }

    /**
     * Tests if grouping (thousands seperator) is enabled for this format
     * 
     * @return boolean
     */
    public function isGroupingEnabled() {
        return $this->grouping;
    }

    /**
     * Builds a NumberStylePartNumber that holds the provided string
     * 
     * @param string $formatPart
     * @return \Box\Spout\Common\Entity\Style\NumberStylePartNumber
     */
    public static function build($formatPart) {
        $entity = new NumberStylePartNumber();

        // test for group/thousands seperator
        $usesGrouping = strpos($formatPart, ',') !== false;
        $entity->setGrouping($usesGrouping);

        // strip thousands seperator
        $formatPartStripped = str_replace(',', '', $formatPart);

        // test for number format
        $regexp = '/^((#*)(0*))((\.)((0*)(#*)))?$/';
        if (preg_match($regexp, $formatPartStripped, $match)) {
            $minIntegerPlaces = strlen($match[3]);
            $maxIntegerPlaces = null; // unlimited
            if (isset($match[2]) && is_string($match[2]) && strlen($match[2]) !== 0) {
                if (!$usesGrouping) {
                    $maxIntegerPlaces = strlen($match[1]);
                }
            }
            $usesDecimalSeperator = isset($match[5]) && strlen($match[5]) !== 0;
            if ($usesDecimalSeperator) {
                $minDecimalPlaces = max(strlen($match[7]), 1);
                $maxDecimalPlaces = max(strlen($match[6]), $minDecimalPlaces);
            } else {
                $minDecimalPlaces = 0;
                $maxDecimalPlaces = 0;
            }

            $entity
                    ->setMinDecimalPlaces($minDecimalPlaces)
                    ->setMaxDecimalPlaces($maxDecimalPlaces)
                    ->setMinIntegerPlaces($minIntegerPlaces)
                    ->setMaxIntegerPlaces($maxIntegerPlaces);
        } else {
            throw new \Exception('Unexpected numeric format: ' . $formatPart);
        }
        return $entity;
    }

}
