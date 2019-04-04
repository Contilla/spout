<?php

/**
 * NumberFormatCondition
 * 
 * Description of NumberFormatCondition
 *
 * @copyright (c) Expression year is undefined on line 7, column 21 in Templates/Scripting/PHPClass.php., Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 11.03.2019
 */

namespace Box\Spout\Common\Entity\Style;

use Box\Spout\Common\Exception\NumberFormatException;
use Exception;

/**
 * NumberFormatCondition
 * 
 * Description of NumberFormatCondition
 *
 * @copyright (c) Expression year is undefined on line 21, column 21 in Templates/Scripting/PHPClass.php., Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 11.03.2019
 */
class NumberFormatCondition {

    private $value;
    private $comparator;

    /**
     * Compared number must be lower than the conditions value
     */
    const COMPARE_LOWERTHAN = 0;

    /**
     * Compared number must be lower or equal to the conditions value
     */
    const COMPARE_LOWEREQUAL = 1;

    /**
     * Compared number must be equal to the conditions value
     */
    const COMPARE_EQUAL = 2;

    /**
     * Compared number must be greater or equal to the conditions value
     */
    const COMPARE_GREATEREQUAL = 3;

    /**
     * Compared number must be greater than the conditions value
     */
    const COMPARE_GREATERTHAN = 4;

    /**
     * Compared value is a string
     */
    const COMPARE_STRING = 99;

    private static $availableComparators = [self::COMPARE_EQUAL, self::COMPARE_GREATEREQUAL, self::COMPARE_GREATERTHAN, self::COMPARE_LOWEREQUAL, self::COMPARE_LOWERTHAN, self::COMPARE_STRING];

    public function __construct($value, $comparator) {
        if (!is_numeric($value)) {
            throw new NumberFormatException('Value must be numeric');
        }

        if (($comparator === null) || !in_array($comparator, self::$availableComparators)) {
            throw new NumberFormatException('Comparator must be one of the COMPARE_XX class constants');
        }

        $this->value = $value;
        $this->comparator = $comparator;
    }

    /**
     * Get the value to compare against
     * 
     * @return numeric
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Get the comparator of the condition
     * 
     * @see NumberFormatCondition::COMPARE_XX constants
     * @return int 
     */
    public function getComparator() {
        return $this->comparator;
    }

    public static function getComparatorType($comparatorString) {
        $test = trim($comparatorString);
        switch ($test) {
            case '<':
                $type = self::COMPARE_LOWERTHAN;
                break;
            case '<=':
                $type = self::COMPARE_LOWEREQUAL;
                break;
            case '=':
                $type = self::COMPARE_EQUAL;
                break;
            case '>=':
                $type = self::COMPARE_GREATEREQUAL;
                break;
            case '>':
                $type = self::COMPARE_GREATERTHAN;
                break;
            case '':
                $type = null;
                break;
            default:
                throw new NumberFormatException(sprintf('Unknown comparator string: "%s"', $test));
                break;
        }

        return $type;
    }

}
