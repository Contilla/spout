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
    private $map = [];

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

    public function addChild(NumberStyle $declaration, NumberFormatCondition $condition) {
        $this->map[] = ['condition' => $condition, 'declaration' => $declaration];

        return $this;
    }

    protected static function getDefaultConditions() {
        if (self::$defaultConditions === null) {
            $conditions = [];
            $conditions[] = new NumberFormatCondition(0, NumberFormatCondition::COMPARE_LOWERTHAN);
            $conditions[] = new NumberFormatCondition(0, NumberFormatCondition::COMPARE_GREATEREQUAL);
            $conditions[] = new NumberFormatCondition(0, NumberFormatCondition::COMPARE_EQUAL);

            self::$defaultConditions = $conditions;
        }

        return self::$defaultConditions;
    }

    public static function build($formatCode) {
        $class = self::class;
        $declaration = new $class;

        // regexp to get color definition for a format
        $expColor = '\[(black|white|red|green|blue|yellow|magenta|cyan|color[0-9]{1,2})\]';
        // regexp to get conditional definition for a format
        $expCondition = '\[([><=]=?)([0-9]+(\.[0-9]+)?)\]';


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
                if (isset($match[3])) {
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

                if ($numTokens !== 1) {
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

}
