<?php

namespace Box\Spout\Writer\ODS\Manager\Style;

use Box\Spout\Common\Entity\Style\BorderPart;
use Box\Spout\Common\Entity\Style\DateFormat;
use Box\Spout\Common\Entity\Style\NumberFormat;
use Box\Spout\Common\Entity\Style\NumberFormatCondition;
use Box\Spout\Common\Entity\Style\NumberStyle;
use Box\Spout\Common\Entity\Style\NumberStylePartNumber;
use Box\Spout\Common\Entity\Style\NumberStylePartString;
use Box\Spout\Common\Entity\Style\Style;
use Box\Spout\Writer\Common\Entity\Worksheet;
use Box\Spout\Writer\Common\Manager\Style\StyleManager;
use Box\Spout\Writer\ODS\Helper\BorderHelper;
use SimpleXMLElement;

/**
 * Class StyleManager
 * Manages styles to be applied to a cell
 */
class StyleManager extends StyleManager {

    /** @var StyleRegistry */
    protected $styleRegistry;

    /**
     * Returns the content of the "styles.xml" file, given a list of styles.
     *
     * @param int $numWorksheets Number of worksheets created
     * @return string
     */
    public function getStylesXMLFileContent($numWorksheets)
    {
        $content = <<<'EOD'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<office:document-styles office:version="1.2" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:msoxl="http://schemas.microsoft.com/office/excel/formula" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
EOD;

        $content .= $this->getFontFaceSectionContent();
        $content .= $this->getStylesSectionContent();
        $content .= $this->getAutomaticStylesSectionContent($numWorksheets);
        $content .= $this->getMasterStylesSectionContent($numWorksheets);

        $content .= <<<'EOD'
</office:document-styles>
EOD;

        return $content;
    }

    /**
     * Returns the content of the "<office:font-face-decls>" section, inside "styles.xml" file.
     *
     * @return string
     */
    protected function getFontFaceSectionContent()
    {
        $content = '<office:font-face-decls>';
        foreach ($this->styleRegistry->getUsedFonts() as $fontName) {
            $content .= '<style:font-face style:name="' . $fontName . '" svg:font-family="' . $fontName . '"/>';
        }
        $content .= '</office:font-face-decls>';

        return $content;
    }

    protected function getDateFormat($format)
    {
        
    }

    protected function getNumberFormat($format)
    {
        
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
     * Get a list of XML nodes that describe the number format
     * 
     * @param NumberStyle $style
     * @param string $name
     * @param NumberFormatCondition $condition
     * @return SimpleXMLElement[]
     */
    private function refactorNumberStyle(NumberStyle $style, $name, $condition = null)
    {
        $nodes = [];
        $styleXml = new SimpleXMLElement(sprintf('<number:number-style style:name="%s"/>', $name));

        if ($condition instanceof NumberFormatCondition) {
            $styleXml->addAttribute('style:volatile', 'true');
        }

        $parts = $style->getParts();
        foreach ($parts as $idx => $part) {
            if ($part instanceof NumberStylePartNumber) {
                $styleNumber = $styleXml->addChild('number:number');

                $minDecimals = $part->getMinDecimalPlaces();
                $maxDecimals = $part->getMaxDecimalPlaces();
                if ($maxDecimals !== null) {
                    $maxDecimals = max($maxDecimals, $minDecimals);
                    $styleNumber->addAttribute('number:decimal-places', (int) $maxDecimals);
                }
                $styleNumber->addAttribute('loext:min-decimal-places', (int) $minDecimals);

                $minIntegers = $part->getMinIntegerPlaces();
                $styleNumber->addAttribute('number:min-integer-digits', (int) $minIntegers);

                if ($part->isGroupingEnabled()) {
                    $styleNumber->addAttribute('number:grouping', 'true');
                }
            } elseif ($part instanceof NumberStylePartString) {
                $styleXml->addChild('number:text', $part->getText());
            }
        }

        $conditionalStyles = $style->getConditionalStyles();
        foreach ($conditionalStyles as $idx => $conditionalStyle) {
            $conditionalStyleName = $name . 'P' . $idx;
            $condition = $conditionalStyle['condition'];
            $nodes = array_merge($nodes, $this->refactorNumberStyle($conditionalStyle['style'], $conditionalStyleName, $condition));
            $mappingXml = $styleXml->addChild('style:map');
            $mappingXml->addAttribute('style:condition', sprintf('value()%s%s', self::comparatorMapping[$condition->getComparator()], $condition->getValue()));
            $mappingXml->addAttribute('style:apply-style-name', $conditionalStyleName);
        }

        $nodes[] = $styleXml;

        return $nodes;
    }

    /**
     * 
     * @param int $styleId
     * @param Style $style
     * @return string
     */
    private function getNumberFormatSectionContent($styleId, Style $style)
    {
        $content = [];

        $numberStyle = $style->getNumberStyle();
        if ($numberStyle instanceof DateFormat) {
            // decode date/time format string
            $this->getDateFormat($numberStyle);
        } elseif ($numberStyle instanceof NumberStyle) {
            // decode number format string
            $numberStylesXML = $this->refactorNumberStyle($numberStyle, 'N' . $styleId);
            foreach ($numberStylesXML as $idx => $numberStyleXml) {
                $content[] = $numberStyleXml->asXML();
            }
        }

        return implode("\n", $content);
    }

    /**
     * Get the section with the number format definitions
     * 
     * @return string
     */
    private function getNumberFormatsSectionContent()
    {
        $content = [];

        // default number style
        $defaultNumberStyle = NumberStyle::build(NumberFormat::FORMAT_NUMBER);
        $defaultNumberStyleXMLs = $this->refactorNumberStyle($defaultNumberStyle, 'N0');
        foreach ($defaultNumberStyle as $idx => $numberStyleXml) {
            $content[] = $numberStyleXml->asXML();
        }

//        // default
//        $content = <<<EOD
//                <number:number-style style:name="N0">
//                    <number:number number:min-integer-digits="1"/>
//                </number:number-style>
//EOD;

        $registeredFormats = $this->styleRegistry->getRegisteredNumberFormats();

        // There is one default border with index 0
        //$formatsCount = count($registeredFormats) + 1;
        $formatsCount = count($registeredFormats);

        if ($formatsCount !== 0) {
            foreach ($registeredFormats as $styleId) {
                /** @var Style $style */
                $style = $this->styleRegistry->getStyleFromStyleId($styleId);

                $content[] = $this->getNumberFormatSectionContent($styleId, $style);
            }
        }

        return implode("\n", $content);
    }

    /**
     * Returns the content of the "<office:styles>" section, inside "styles.xml" file.
     *
     * @return string
     */
    protected function getStylesSectionContent()
    {
        $defaultStyle = $this->getDefaultStyle();

        return <<<EOD
<office:styles>
   {$this->getNumberFormatsSectionContent()} 
   
    <style:style style:data-style-name="N0" style:family="table-cell" style:name="Default">
        <style:table-cell-properties fo:background-color="transparent" style:vertical-align="automatic"/>
        <style:text-properties fo:color="#{$defaultStyle->getFontColor()}"
                               fo:font-size="{$defaultStyle->getFontSize()}pt" style:font-size-asian="{$defaultStyle->getFontSize()}pt" style:font-size-complex="{$defaultStyle->getFontSize()}pt"
                               style:font-name="{$defaultStyle->getFontName()}" style:font-name-asian="{$defaultStyle->getFontName()}" style:font-name-complex="{$defaultStyle->getFontName()}"/>
    </style:style>
</office:styles>
EOD;
    }

    /**
     * Returns the content of the "<office:automatic-styles>" section, inside "styles.xml" file.
     *
     * @param int $numWorksheets Number of worksheets created
     * @return string
     */
    protected function getAutomaticStylesSectionContent($numWorksheets)
    {
        $content = '<office:automatic-styles>';

        for ($i = 1; $i <= $numWorksheets; $i++) {
            $content .= <<<EOD
<style:page-layout style:name="pm$i">
    <style:page-layout-properties style:first-page-number="continue" style:print="objects charts drawings" style:table-centering="none"/>
    <style:header-style/>
    <style:footer-style/>
</style:page-layout>
EOD;
        }

        $content .= '</office:automatic-styles>';

        return $content;
    }

    /**
     * Returns the content of the "<office:master-styles>" section, inside "styles.xml" file.
     *
     * @param int $numWorksheets Number of worksheets created
     * @return string
     */
    protected function getMasterStylesSectionContent($numWorksheets)
    {
        $content = '<office:master-styles>';

        for ($i = 1; $i <= $numWorksheets; $i++) {
            $content .= <<<EOD
<style:master-page style:name="mp$i" style:page-layout-name="pm$i">
    <style:header/>
    <style:header-left style:display="false"/>
    <style:footer/>
    <style:footer-left style:display="false"/>
</style:master-page>
EOD;
        }

        $content .= '</office:master-styles>';

        return $content;
    }

    /**
     * Returns the contents of the "<office:font-face-decls>" section, inside "content.xml" file.
     *
     * @return string
     */
    public function getContentXmlFontFaceSectionContent()
    {
        $content = '<office:font-face-decls>';
        foreach ($this->styleRegistry->getUsedFonts() as $fontName) {
            $content .= '<style:font-face style:name="' . $fontName . '" svg:font-family="' . $fontName . '"/>';
        }
        $content .= '</office:font-face-decls>';

        return $content;
    }

    /**
     * Returns the contents of the "<office:automatic-styles>" section, inside "content.xml" file.
     *
     * @param Worksheet[] $worksheets
     * @return string
     */
    public function getContentXmlAutomaticStylesSectionContent($worksheets)
    {
        $content = '<office:automatic-styles>';

        foreach ($this->styleRegistry->getRegisteredStyles() as $style) {
            $content .= $this->getStyleSectionContent($style);
        }

        $content .= <<<'EOD'
<style:style style:family="table-column" style:name="co1">
    <style:table-column-properties fo:break-before="auto"/>
</style:style>
<style:style style:family="table-row" style:name="ro1">
    <style:table-row-properties fo:break-before="auto" style:row-height="15pt" style:use-optimal-row-height="true"/>
</style:style>
EOD;

        foreach ($worksheets as $worksheet) {
            $worksheetId = $worksheet->getId();
            $isSheetVisible = $worksheet->getExternalSheet()->isVisible() ? 'true' : 'false';

            $content .= <<<EOD
<style:style style:family="table" style:master-page-name="mp$worksheetId" style:name="ta$worksheetId">
    <style:table-properties style:writing-mode="lr-tb" table:display="$isSheetVisible"/>
</style:style>
EOD;
        }

        $content .= '</office:automatic-styles>';

        return $content;
    }

    /**
     * Returns the contents of the "<style:style>" section, inside "<office:automatic-styles>" section
     *
     * @param Style $style
     * @return string
     */
    protected function getStyleSectionContent($style)
    {
        $styleIndex = $style->getId() + 1; // 1-based

        $content = '<style:style style:data-style-name="N0" style:family="table-cell" style:name="ce' . $styleIndex . '" style:parent-style-name="Default">';

        $content .= $this->getTextPropertiesSectionContent($style);
        $content .= $this->getTableCellPropertiesSectionContent($style);

        $content .= '</style:style>';

        return $content;
    }

    /**
     * Returns the contents of the "<style:text-properties>" section, inside "<style:style>" section
     *
     * @param Style $style
     * @return string
     */
    private function getTextPropertiesSectionContent($style)
    {
        $content = '';

        if ($style->shouldApplyFont()) {
            $content .= $this->getFontSectionContent($style);
        }

        return $content;
    }

    /**
     * Returns the contents of the "<style:text-properties>" section, inside "<style:style>" section
     *
     * @param Style $style
     * @return string
     */
    private function getFontSectionContent($style)
    {
        $defaultStyle = $this->getDefaultStyle();

        $content = '<style:text-properties';

        $fontColor = $style->getFontColor();
        if ($fontColor !== $defaultStyle->getFontColor()) {
            $content .= ' fo:color="#' . $fontColor . '"';
        }

        $fontName = $style->getFontName();
        if ($fontName !== $defaultStyle->getFontName()) {
            $content .= ' style:font-name="' . $fontName . '" style:font-name-asian="' . $fontName . '" style:font-name-complex="' . $fontName . '"';
        }

        $fontSize = $style->getFontSize();
        if ($fontSize !== $defaultStyle->getFontSize()) {
            $content .= ' fo:font-size="' . $fontSize . 'pt" style:font-size-asian="' . $fontSize . 'pt" style:font-size-complex="' . $fontSize . 'pt"';
        }

        if ($style->isFontBold()) {
            $content .= ' fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"';
        }
        if ($style->isFontItalic()) {
            $content .= ' fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"';
        }
        if ($style->isFontUnderline()) {
            $content .= ' style:text-underline-style="solid" style:text-underline-type="single"';
        }
        if ($style->isFontStrikethrough()) {
            $content .= ' style:text-line-through-style="solid"';
        }

        $content .= '/>';

        return $content;
    }

    /**
     * Returns the contents of the "<style:table-cell-properties>" section, inside "<style:style>" section
     *
     * @param Style $style
     * @return string
     */
    private function getTableCellPropertiesSectionContent($style)
    {
        $content = '';

        if ($style->shouldWrapText()) {
            $content .= $this->getWrapTextXMLContent();
        }

        if ($style->shouldApplyBorder()) {
            $content .= $this->getBorderXMLContent($style);
        }

        if ($style->shouldApplyBackgroundColor()) {
            $content .= $this->getBackgroundColorXMLContent($style);
        }

        return $content;
    }

    /**
     * Returns the contents of the wrap text definition for the "<style:table-cell-properties>" section
     *
     * @return string
     */
    private function getWrapTextXMLContent()
    {
        return '<style:table-cell-properties fo:wrap-option="wrap" style:vertical-align="automatic"/>';
    }

    /**
     * Returns the contents of the borders definition for the "<style:table-cell-properties>" section
     *
     * @param Style $style
     * @return string
     */
    private function getBorderXMLContent($style)
    {
        $borderProperty = '<style:table-cell-properties %s />';

        $borders = array_map(function (BorderPart $borderPart) {
            return BorderHelper::serializeBorderPart($borderPart);
        }, $style->getBorder()->getParts());

        return sprintf($borderProperty, implode(' ', $borders));
    }

    /**
     * Returns the contents of the background color definition for the "<style:table-cell-properties>" section
     *
     * @param Style $style
     * @return string
     */
    private function getBackgroundColorXMLContent($style)
    {
        return sprintf(
                '<style:table-cell-properties fo:background-color="#%s"/>',
                $style->getBackgroundColor()
        );
    }

}
