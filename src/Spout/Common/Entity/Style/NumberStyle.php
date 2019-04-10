<?php

/**
 * NumberFormatDeclaration
 * 
 * Description of NumberFormatDeclaration
 *
 * @copyright (c) Expression year is undefined on line 7, column 21 in Templates/Scripting/PHPClass.php., Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 08.03.2019
 */

namespace Box\Spout\Common\Entity\Style;

use Box\Spout\Common\Exception\NumberFormatException;

/**
 * NumberFormatDeclaration
 * 
 * Description of NumberFormatDeclaration
 *
 * @copyright (c) Expression year is undefined on line 21, column 21 in Templates/Scripting/PHPClass.php., Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 08.03.2019
 */
class NumberStyle {

    /**
     * @var NumberStyle
     */
    private $map = null;

    /**
     * Color code for the format
     * 
     * @var string
     */
    private $color = null;

    /**
     * @var NumberStylePartInterface[]
     */
    private $parts = [];

    /**
     * @var NumberFormatCondition[] 
     */
    static $defaultConditions = null;

    public function __construct() {
        
    }

    /**
     * Add a subdeclaration with a condition to a number style
     * 
     * @param \Box\Spout\Common\Entity\Style\NumberStyle $style
     * @param \Box\Spout\Common\Entity\Style\NumberFormatCondition $condition
     * @return $this
     */
    public function addChild(NumberStyle $style, NumberFormatCondition $condition) {
        if ($this->map === null) {
            $this->map = [];
        }
        $this->map[] = ['condition' => $condition, 'style' => $style];

        return $this;
    }

    /**
     * Get default conditions
     * 
     * @return NumberFormatCondition[]
     */
    protected static function getDefaultConditions() {
        if (self::$defaultConditions === null) {
            $conditions = [];
            $conditions[] = new NumberFormatCondition(0, NumberFormatCondition::COMPARE_LOWERTHAN);
            $conditions[] = new NumberFormatCondition(0, NumberFormatCondition::COMPARE_GREATERTHAN);
            $conditions[] = new NumberFormatCondition(0, NumberFormatCondition::COMPARE_EQUAL);
            $conditions[] = new NumberFormatCondition(0, NumberFormatCondition::COMPARE_STRING);

            self::$defaultConditions = $conditions;
        }

        return self::$defaultConditions;
    }

    /**
     * Build a number style out of a format definition
     * 
     * @param type $formatCode
     * @return $this
     */
    public static function build($formatCode) {
        $class = self::class;
        $declaration = new $class;

        // regexp to get color definition for a format
        $expColor = '\[(black|white|red|green|blue|yellow|magenta|cyan|color[0-9]{1,2})\]';
        // regexp to get conditional definition for a format
        $expCondition = '\[([><=]=?)(-?[0-9]+(\.[0-9]+)?)\]';


        $usesCustomConditions = preg_match('/' . $expCondition . '/', $formatCode);
        $defaultConditions = self::getDefaultConditions();

        $tokens = explode(';', $formatCode);
        $numTokens = count($tokens);
        foreach ($tokens as $index => $token) {
            // regexp to get the number format itself
            $expFormat = '.*?';
            $regExp = '/^(' . $expColor . ')?(' . $expCondition . ')?(' . $expFormat . ')$/i';

            if (preg_match($regExp, $token, $match)) {
                $color = $match[2];
                if (isset($match[3]) && (strlen($match[3]) !== 0)) {
                    $condition = new NumberFormatCondition((float) $match[5], NumberFormatCondition::getComparatorType($match[4]));
                } else {
                    if (!$usesCustomConditions) {
                        $condition = $defaultConditions[$index];
                    } else {
                        $condition = null;
                    }
                }
                $format = $match[7]; // includes all but the condition and the color
                $formatParts = self::parseFormat($format);

                if (($numTokens !== 1) && ($condition !== null)) {
                    $mappedDeclaration = new NumberStyle();
                    if (strlen($color) !== 0) {
                        $mappedDeclaration->setColor($color);
                    }
                    foreach ($formatParts as $part) {
                        $mappedDeclaration->addPart($part);
                    }
                    $declaration->addChild($mappedDeclaration, $condition);
                } else {
                    if (strlen($color) !== 0) {
                        $declaration->setColor($color);
                    }
                    foreach ($formatParts as $part) {
                        $declaration->addPart($part);
                    }
                }
            }
        }

        return $declaration;
    }

    /**
     * Analyse the given number format and create a list of number style parts that resamples the layout
     * 
     * @param string $format
     * @return NumberStylePartInterface[]
     * @throws Exception
     */
    protected function parseFormat($format) {
        // regexp to get a string constant
        $expStringInQuotes = '/^(".*?"|\'.*?\')/';

        // regexp to get a number block
        $expDigits = '/^([\#0\.,]+)/';

        // regexp to get format part that is not a number block and not surrounded by quotation marks
        $expNoDigits = '/^([^\#0\.,]+)/';

        $tests = [
            [
                'class' => NumberStylePartNumber::class,
                'regexp' => $expDigits
            ],
            [
                'class' => NumberStylePartString::class,
                'regexp' => $expStringInQuotes,
                'convert' => function($text) {
                    // remove quotation marks
                    return substr($text, 1, -1);
                }
            ],
            [
                'class' => NumberStylePartString::class,
                'regexp' => $expNoDigits,
            ],
        ];

        $parts = [];
        while (strlen($format) !== 0) {
            $found = false;
            foreach ($tests as $test) {
                if (preg_match($test['regexp'], $format, $match)) {
                    $found = true;
                    $partFormat = $match[1];
                    $format = substr($format, strlen($partFormat));
                    if (isset($test['convert'])) {
                        $partFormat = $test['convert']($partFormat);
                    }

                    $class = $test['class'];
                    $parts[] = $class::build($partFormat);
                }

                if ($found) {
                    break;
                }
            }

            if (!$found) {
                throw new NumberFormatException(sprintf('Unable to parse format string. No known token starting at first character: "%s"', substr($format, 0, 5)));
            }
        }

        return $parts;
    }

    /**
     * Add a form definition part for this styling
     * 
     * @param NumberStylePartInterface $part
     * @return $this
     */
    public function addPart(NumberStylePartInterface $part) {
        $this->parts[] = $part;

        return $this;
    }

    /**
     * Get the definitions parts of the format
     * 
     * @return NumberStylePartInterface[]
     */
    public function getParts() {
        return $this->parts;
    }

    /**
     * Get list of conditional mapped number styles
     * 
     * @return NumberStyle[]
     */
    public function getConditionalStyles() {
        return $this->map;
    }

    /**
     * Set the color for the format
     * 
     * @param string $color
     * @return $this
     * @throws NumberFormatException
     */
    public function setColor($color) {
        if (!is_string($color) || empty($color)) {
            throw new NumberFormatException('Color can\'t be empty');
        }

        $this->color = $color;
        return $this;
    }

    /**
     * Get the color for the format
     * 
     * @return type
     */
    public function getColor() {
        return $this->color;
    }

}
