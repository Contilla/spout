<?php

namespace Box\Spout\Writer\XLSX\Manager\Style;

use Box\Spout\Common\Entity\Style\NumberFormatCondition;
use Box\Spout\Common\Entity\Style\NumberStyle;
use Box\Spout\Common\Entity\Style\NumberStylePartNumber;
use Box\Spout\Common\Entity\Style\NumberStylePartString;
use Box\Spout\Common\Entity\Style\Style;
use Box\Spout\Writer\XLSX\Helper\BorderHelper;
use Box\Spout\Writer\XLSX\Manager\Style\StyleRegistry;

/**
 * Class StyleManager
 * Manages styles to be applied to a cell
 */
class StyleManager extends \Box\Spout\Writer\Common\Manager\Style\StyleManager {

    /** @var StyleRegistry */
    protected $styleRegistry;

    /**
     * For empty cells, we can specify a style or not. If no style are specified,
     * then the software default will be applied. But sometimes, it may be useful
     * to override this default style, for instance if the cell should have a
     * background color different than the default one or some borders
     * (fonts property don't really matter here).
     *
     * @param int $styleId
     * @return bool Whether the cell should define a custom style
     */
    public function shouldApplyStyleOnEmptyCell($styleId)
    {
        $associatedFillId = $this->styleRegistry->getFillIdForStyleId($styleId);
        $hasStyleCustomFill = ($associatedFillId !== null && $associatedFillId !== 0);

        $associatedBorderId = $this->styleRegistry->getBorderIdForStyleId($styleId);
        $hasStyleCustomBorders = ($associatedBorderId !== null && $associatedBorderId !== 0);

        return ($hasStyleCustomFill || $hasStyleCustomBorders);
    }

    /**
     * Returns the content of the "styles.xml" file, given a list of styles.
     *
     * @return string
     */
    public function getStylesXMLFileContent()
    {
        $content = <<<'EOD'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
EOD;

        $content .= $this->getFontsSectionContent();
        $content .= $this->getFillsSectionContent();
        $content .= $this->getBordersSectionContent();
        $content .= $this->getNumberFormatsSectionContent();
        $content .= $this->getCellStyleXfsSectionContent();
        $content .= $this->getCellXfsSectionContent();
        $content .= $this->getCellStylesSectionContent();

        $content .= <<<'EOD'
</styleSheet>
EOD;

        return $content;
    }

    /**
     * Returns the content of the "<fonts>" section.
     *
     * @return string
     */
    protected function getFontsSectionContent()
    {
        $registeredStyles = $this->styleRegistry->getRegisteredStyles();

        $content = '<fonts count="' . count($registeredStyles) . '">';

        /** @var Style $style */
        foreach ($registeredStyles as $style) {
            $content .= '<font>';

            $content .= '<sz val="' . $style->getFontSize() . '"/>';
            $content .= '<color rgb="' . \Box\Spout\Common\Entity\Style\Color::toARGB($style->getFontColor()) . '"/>';
            $content .= '<name val="' . $style->getFontName() . '"/>';

            if ($style->isFontBold()) {
                $content .= '<b/>';
            }
            if ($style->isFontItalic()) {
                $content .= '<i/>';
            }
            if ($style->isFontUnderline()) {
                $content .= '<u/>';
            }
            if ($style->isFontStrikethrough()) {
                $content .= '<strike/>';
            }

            $content .= '</font>';
        }

        $content .= '</fonts>';

        return $content;
    }

    /**
     * Returns the content of the "<fills>" section.
     *
     * @return string
     */
    protected function getFillsSectionContent()
    {
        $registeredFills = $this->styleRegistry->getRegisteredFills();

        // Excel reserves two default fills
        $fillsCount = count($registeredFills) + 2;
        $content = sprintf('<fills count="%d">', $fillsCount);

        $content .= '<fill><patternFill patternType="none"/></fill>';
        $content .= '<fill><patternFill patternType="gray125"/></fill>';

        // The other fills are actually registered by setting a background color
        foreach ($registeredFills as $styleId) {
            /** @var Style $style */
            $style = $this->styleRegistry->getStyleFromStyleId($styleId);

            $backgroundColor = $style->getBackgroundColor();
            $content .= sprintf(
                    '<fill><patternFill patternType="solid"><fgColor rgb="%s"/></patternFill></fill>',
                    $backgroundColor
            );
        }

        $content .= '</fills>';

        return $content;
    }

    /**
     * Returns the content of the "<borders>" section.
     *
     * @return string
     */
    protected function getBordersSectionContent()
    {
        $registeredBorders = $this->styleRegistry->getRegisteredBorders();

        // There is one default border with index 0
        $borderCount = count($registeredBorders) + 1;

        $content = '<borders count="' . $borderCount . '">';

        // Default border starting at index 0
        $content .= '<border><left/><right/><top/><bottom/></border>';

        foreach ($registeredBorders as $styleId) {
            /** @var Style $style */
            $style = $this->styleRegistry->getStyleFromStyleId($styleId);
            $border = $style->getBorder();
            $content .= '<border>';

            // @link https://github.com/box/spout/issues/271
            $sortOrder = ['left', 'right', 'top', 'bottom'];

            foreach ($sortOrder as $partName) {
                if ($border->hasPart($partName)) {
                    /** @var $part \Box\Spout\Common\Entity\Style\BorderPart */
                    $part = $border->getPart($partName);
                    $content .= BorderHelper::serializeBorderPart($part);
                }
            }

            $content .= '</border>';
        }

        $content .= '</borders>';

        return $content;
    }

    private static $comparatorMapping = [
        NumberFormatCondition::COMPARE_LOWERTHAN => '<',
        NumberFormatCondition::COMPARE_LOWEREQUAL => '<=',
        NumberFormatCondition::COMPARE_EQUAL => '=',
        NumberFormatCondition::COMPARE_GREATEREQUAL => '>=',
        NumberFormatCondition::COMPARE_GREATERTHAN => '>',
        NumberFormatCondition::COMPARE_STRING => null,
    ];

    /**
     * Reconstructing of a format definition with optional condition
     * 
     * @param NumberStyle $numberStyle
     * @param NumberFormatCondition $condition
     */
    private function refactorNumberFormatPart(NumberStyle $numberStyle, $condition = null)
    {
        $formatParts = [];

        // coloring of the format
        $color = $numberStyle->getColor();
        if ($color !== null) {
            $formatParts[] = sprintf('[%s]', $color);
        }

        // condition for the format
        if ($condition instanceof NumberFormatCondition) {
            $comparator = self::$comparatorMapping[$condition->getComparator()];
            if ($comparator !== null) {
                $formatParts[] = sprintf('[%s%s]', $comparator, $condition->getValue());
            }
        }

        // the format string itself
        $format = '';
        $parts = $numberStyle->getParts();
        foreach ($parts as $part) {
            if ($part instanceof NumberStylePartNumber) {
                $grouping = $part->getGrouping(); // thousands grouping enabled?
                $groupingOpened = false;

                $requiredIntegers = $part->getMinIntegerPlaces();
                if ($requiredIntegers !== null) {
                    for ($i = 0; $i < $requiredIntegers; $i++) {
                        $groupingOpened = false;
                        $format = '0' . $format;
                        if ($grouping && ($i + 1 % 3 === 0)) {
                            $format .= ',' . $format;
                            $groupingOpened = true;
                        }
                    }
                } else {
                    $requiredIntegers = 0;
                }

                $maxIntegers = $part->getMaxIntegerPlaces();
                if (($maxIntegers === null) || ($maxIntegers < $requiredIntegers)) {
                    $maxIntegers = $requiredIntegers;
                }

                if ($grouping) {
                    $maxIntegers = max(4, $maxIntegers);
                }

                for ($i = $requiredIntegers; $i < $maxIntegers; $i++) {
                    $groupingOpened = false;
                    $format = '#' . $format;
                    if ($grouping && ($i + 1 % 3 === 0)) {
                        $format .= ',' . $format;
                        $groupingOpened = true;
                    }
                }

                if ($groupingOpened) {
                    $format = '#' . $format;
                }

                $requiredDecimals = $part->getMinDecimalPlaces();
                $maxDecimals = $part->getMaxDecimalPlaces();
                if ($maxDecimals === null) {
                    $maxDecimals = $requiredDecimals;
                }
                if (($requiredDecimals !== null) || ($maxDecimals !== null)) {
                    $format .= '.';
                    if ($requiredDecimals !== null) {
                        for ($i = 0; $i < $requiredDecimals; $i++) {
                            $format .= '0';
                        }
                    } else {
                        $requiredDecimals = 0;
                    }
                    if ($maxDecimals !== null) {
                        for ($i = 0; $i < $requiredDecimals - $requiredDecimals; $i++) {
                            $format .= '#';
                        }
                    }
                }
            } elseif ($part instanceof NumberStylePartString) {
                $format .= $part->getText();
            }
        }
        if (strlen($format) !== 0) {
            $formatParts[] = $format;
        }

        implode('', $formatParts);
    }

    private function refactorNumberFormat(NumberStyle $numberStyle)
    {
        $parts = [];
        $conditionalStyles = $numberStyle->getConditionalStyles();
        foreach ($conditionalStyles as $conditionalStyles) {
            $parts[] = $this->refactorNumberFormatPart($conditionalStyle['style'], $conditionalStyle['condition']);
        }

        $parts[] = $this->refactorNumberFormatPart($numberStyle);

        return implode(';', $parts);
    }

    /**
     * Returns the content of the "<numFmts>" section.
     *
     * @return string
     */
    protected function getNumberFormatsSectionContent()
    {
        $registeredStyles = $this->styleRegistry->getRegisteredNumberStyles();

        // There is one default border with index 0
        //$formatsCount = count($registeredFormats) + 1;
        $styleCount = count($registeredStyles);

        $content = '';
        if ($styleCount !== 0) {

            $content .= '<numFmts count="' . $styleCount . '">';

            // Default border starting at index 0
            //$content .= '<numFmt><left/><right/><top/><bottom/></border>';

            foreach ($registeredStyles as $styleId) {
                /** @var Style $style */
                $style = $this->styleRegistry->getStyleFromStyleId($styleId);
                $numberStyle = $style->getNumberStyle();
                $content .= sprintf('<numFmt numFmtId="%s" formatCode="%s"/>', $this->getNumberStyleIdForStyleId($styleId), htmlentities($this->refactorNumberFormat($numberStyle)));
            }

            $content .= '</numFmts>';
        }

        return $content;
    }

    /**
     * Returns the content of the "<cellStyleXfs>" section.
     *
     * @return string
     */
    protected function getCellStyleXfsSectionContent()
    {
        return <<<'EOD'
<cellStyleXfs count="1">
    <xf borderId="0" fillId="0" fontId="0" numFmtId="0"/>
</cellStyleXfs>
EOD;
    }

    /**
     * Returns the content of the "<cellXfs>" section.
     *
     * @return string
     */
    protected function getCellXfsSectionContent()
    {
        $registeredStyles = $this->styleRegistry->getRegisteredStyles();

        $content = '<cellXfs count="' . count($registeredStyles) . '">';

        foreach ($registeredStyles as $style) {
            $styleId = $style->getId();
            $fillId = $this->getFillIdForStyleId($styleId);
            $borderId = $this->getBorderIdForStyleId($styleId);
            $numberFormatId = $this->getNumberStyleIdForStyleId($styleId);

            $content .= '<xf numFmtId="' . $numberFormatId . '" fontId="' . $styleId . '" fillId="' . $fillId . '" borderId="' . $borderId . '" xfId="0"';

            if ($style->shouldApplyFont()) {
                $content .= ' applyFont="1"';
            }

            $content .= sprintf(' applyBorder="%d"', $style->shouldApplyBorder() ? 1 : 0);

            if ($style->shouldWrapText()) {
                $content .= ' applyAlignment="1">';
                $content .= '<alignment wrapText="1"/>';
                $content .= '</xf>';
            } else {
                $content .= '/>';
            }
        }

        $content .= '</cellXfs>';

        return $content;
    }

    /**
     * Returns the fill ID associated to the given style ID.
     * For the default style, we don't a fill.
     *
     * @param int $styleId
     * @return int
     */
    private function getFillIdForStyleId($styleId)
    {
        // For the default style (ID = 0), we don't want to override the fill.
        // Otherwise all cells of the spreadsheet will have a background color.
        $isDefaultStyle = ($styleId === 0);

        return $isDefaultStyle ? 0 : ($this->styleRegistry->getFillIdForStyleId($styleId) ?: 0);
    }

    /**
     * Returns the fill ID associated to the given style ID.
     * For the default style, we don't a border.
     *
     * @param int $styleId
     * @return int
     */
    private function getBorderIdForStyleId($styleId)
    {
        // For the default style (ID = 0), we don't want to override the border.
        // Otherwise all cells of the spreadsheet will have a border.
        $isDefaultStyle = ($styleId === 0);

        return $isDefaultStyle ? 0 : ($this->styleRegistry->getBorderIdForStyleId($styleId) ?: 0);
    }

    /**
     * Returns the number format ID associated to the given style ID.
     * For the default style, we don't add a number format.
     *
     * @param int $styleId
     * @return int
     */
    private function getNumberStyleIdForStyleId($styleId)
    {
        // For the default style (ID = 0), we don't want to override the border.
        // Otherwise all cells of the spreadsheet will have a border.
        $isDefaultStyle = ($styleId === 0);

        return $isDefaultStyle ? 0 : ($this->styleRegistry->getNumberStyleIdForStyleId($styleId) ?: 0);
    }

    /**
     * Returns the content of the "<cellStyles>" section.
     *
     * @return string
     */
    protected function getCellStylesSectionContent()
    {
        return <<<'EOD'
<cellStyles count="1">
    <cellStyle builtinId="0" name="Normal" xfId="0"/>
</cellStyles>
EOD;
    }

}
