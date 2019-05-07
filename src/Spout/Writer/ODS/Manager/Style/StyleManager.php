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
use Box\Spout\Writer\Common\Manager\Style\StyleManager as CommonStyleManager;
use Box\Spout\Writer\ODS\Helper\BorderHelper;
use SimpleXMLElement;

/**
 * Class StyleManager
 * Manages styles to be applied to a cell
 */
class StyleManager extends CommonStyleManager {

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
        $document = @new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><office:document-styles office:version="1.2" />');

        // declare namespaces the complicated way to avoid SimpleXMLElements behaviour of complaining about undeclared namespaces in attributes, ...
        // https://stackoverflow.com/a/9391673
        $namespaces = [
            'dc' => 'http://purl.org/dc/elements/1.1/',
            'draw' => 'http://purl.org/dc/elements/1.1/',
            'fo' => 'urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0',
            'msoxl' => 'http://schemas.microsoft.com/office/excel/formula',
            'number' => 'urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0',
            'office' => 'urn:oasis:names:tc:opendocument:xmlns:office:1.0',
            'style' => 'urn:oasis:names:tc:opendocument:xmlns:style:1.0',
            'svg' => 'urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0',
            'table' => 'urn:oasis:names:tc:opendocument:xmlns:table:1.0',
            'text' => 'urn:oasis:names:tc:opendocument:xmlns:text:1.0',
            'xlink' => 'http://www.w3.org/1999/xlink',
            'meta' => 'urn:oasis:names:tc:opendocument:xmlns:meta:1.0',
            'presentation' => 'urn:oasis:names:tc:opendocument:xmlns:presentation:1.0',
            'chart' => 'urn:oasis:names:tc:opendocument:xmlns:chart:1.0',
            'dr3d' => 'urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0',
            'math' => 'http://www.w3.org/1998/Math/MathML',
            'form' => 'urn:oasis:names:tc:opendocument:xmlns:form:1.0',
            'script' => 'urn:oasis:names:tc:opendocument:xmlns:script:1.0',
            'ooo' => 'http://openoffice.org/2004/office',
            'ooow' => 'http://openoffice.org/2004/writer',
            'oooc' => 'http://openoffice.org/2004/calc',
            'dom' => 'http://www.w3.org/2001/xml-events',
            'rpt' => 'http://openoffice.org/2005/report',
            'of' => 'urn:oasis:names:tc:opendocument:xmlns:of:1.2',
            'xhtml' => 'http://www.w3.org/1999/xhtml',
            'grddl' => 'http://www.w3.org/2003/g/data-view#',
            'tableooo' => 'http://openoffice.org/2009/table',
            'drawooo' => 'http://openoffice.org/2010/draw',
            'calcext' => 'urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0',
            'loext' => 'urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0',
            'field' => 'urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0',
            'css3t' => 'http://www.w3.org/TR/css3-text/',
        ];

        foreach ($namespaces as $ns => $uri) {
            $document->addAttribute(sprintf('xmlns:xmlns:%s', $ns), $uri);
        }

        $this->getFontFaceSectionContent($document);
        $this->getStylesSectionContent($document);
        $this->getAutomaticStylesSectionContent($document, $numWorksheets);
        $this->getMasterStylesSectionContent($document, $numWorksheets);

        return $document->asXML();
    }

    /**
     * Returns the content of the "<office:font-face-decls>" section, inside "styles.xml" file.
     *
     * @return string
     */
    protected function getFontFaceSectionContent(\SimpleXMLElement $document)
    {
        $content = $document->addChild('xmlns:office:font-face-decls');
        foreach ($this->styleRegistry->getUsedFonts() as $fontName) {
            $fontStyle = $content->addChild('xmlns:style:font-face');
            $fontStyle->addAttribute('xmlns:style:name', $fontName);
            $fontStyle->addAttribute('xmlns:svg:font-family', $fontName);
        }

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
    private function refactorNumberStyle(\SimpleXMLElement $documentStyles, NumberStyle $style, $name, $condition = null)
    {
        $nodes = [];
        $numberNode = $documentStyles->addChild('xmlns:number:number-style');
        $numberNode->addAttribute('xmlns:style:name', $name);

        if ($condition instanceof NumberFormatCondition) {
            $numberNode->addAttribute('xmlns:style:volatile', 'true');
        }

        $parts = $style->getParts();
        foreach ($parts as $idx => $part) {
            if ($part instanceof NumberStylePartNumber) {
                $styleNumber = $numberNode->addChild('xmlns:number:number');

                $minDecimals = $part->getMinDecimalPlaces();
                $maxDecimals = $part->getMaxDecimalPlaces();
                if ($maxDecimals !== null) {
                    $maxDecimals = max($maxDecimals, $minDecimals);
                    $styleNumber->addAttribute('xmlns:number:decimal-places', (int) $maxDecimals);
                }
                $styleNumber->addAttribute('xmlns:loext:min-decimal-places', (int) $minDecimals);

                $minIntegers = $part->getMinIntegerPlaces();
                $styleNumber->addAttribute('xmlns:number:min-integer-digits', (int) $minIntegers);

                if ($part->isGroupingEnabled()) {
                    $styleNumber->addAttribute('xmlns:number:grouping', 'true');
                }
            } elseif ($part instanceof NumberStylePartString) {
                $numberNode->addChild('xmlns:number:text', $part->getText());
            }
        }

        $conditionalStyles = $style->getConditionalStyles();
        if (is_array($conditionalStyles)) {
            foreach ($conditionalStyles as $idx => $conditionalStyle) {
                $conditionalStyleName = $name . 'P' . $idx;
                $condition = $conditionalStyle['condition'];
                $conditionalNode = $this->refactorNumberStyle($documentStyles, $conditionalStyle['style'], $conditionalStyleName, $condition);
                $mappingXml = $numberNode->addChild('xmlns:style:map');
                $mappingXml->addAttribute('xmlns:style:condition', sprintf('value()%s%s', self::comparatorMapping[$condition->getComparator()], $condition->getValue()));
                $mappingXml->addAttribute('xmlns:style:apply-style-name', $conditionalStyleName);
            }
        }

        return $numberNode;
    }

    /**
     * 
     * @param int $styleId
     * @param Style $style
     * @return string
     */
    private function getNumberFormatSectionContent($documentStyles, $styleId, Style $style)
    {
        $content = [];

        $numberStyle = $style->getNumberStyle();
        if ($numberStyle instanceof DateFormat) {
            // decode date/time format string
            //$this->getDateFormat($numberStyle);
            $numberStylesNode = $this->refactorDateStyle($documentStyles, $numberStyle, 'N' . $styleId);
        } elseif ($numberStyle instanceof NumberStyle) {
            // decode number format string
            $numberStylesNode = $this->refactorNumberStyle($documentStyles, $numberStyle, 'N' . $styleId);
        }

        return $numberStylesNode;
    }

    /**
     * Get the section with the number format definitions
     * 
     * @return string
     */
    private function getNumberFormatsSectionContent(\SimpleXMLElement $documentStyles)
    {
        $content = [];

        // default number style
        $defaultNumberStyle = NumberStyle::build(NumberFormat::FORMAT_NUMBER);
        $defaultNumberStyleNode = $this->refactorNumberStyle($documentStyles, $defaultNumberStyle, 'N0');

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
                fwrite(STDERR, var_export($content, true) . ": call self::getNumberFormatSectionContent()\n");
                $numberstyleNode = $this->getNumberFormatSectionContent($documentStyles, $styleId, $style);
            }
        }

        return $documentStyles;
    }

    /**
     * Returns the content of the "<office:styles>" section, inside "styles.xml" file.
     *
     * @return string
     */
    protected function getStylesSectionContent(\SimpleXMLElement $document)
    {
        $defaultStyle = $this->getDefaultStyle();

        $documentStyles = $document->addChild('xmlns:office:styles');

        $this->getNumberFormatsSectionContent($documentStyles);

        $defaultStyleNode = $documentStyles->addChild('xmlns:style:style');
        $defaultStyleNode->addAttribute('xmlns:style:data-style-name', 'N0');
        $defaultStyleNode->addAttribute('xmlns:style:family', 'table-cell');
        $defaultStyleNode->addAttribute('xmlns:style:name', 'Default');

        $defaultStyleTableCellNode = $defaultStyleNode->addChild('xmlns:style:table-cell-properties');
        $defaultStyleTableCellNode->addAttribute('xmlns:fo:background-color', 'transparent');
        $defaultStyleTableCellNode->addAttribute('xmlns:style:vertical-align', 'automatic');


        $defaultStyleTextPropertiesNode = $defaultStyleNode->addChild('xmlns:style:text-properties');
        $defaultStyleTextPropertiesNode->addAttribute('xmlns:fo:color', '#' . $defaultStyle->getFontColor());
        foreach (['fo:font-size', 'style:font-size-asian', 'style:font-size-complex'] as $attribute) {
            $defaultStyleTextPropertiesNode->addAttribute('xmlns:' . $attribute, $defaultStyle->getFontSize() . 'pt');
        }
        foreach (['style:font-name', 'style:font-name-asian', 'style:font-name-complex'] as $attribute) {
            $defaultStyleTextPropertiesNode->addAttribute('xmlns:' . $attribute, $defaultStyle->getFontName());
        }

        return $documentStyles;
    }

    /**
     * Returns the content of the "<office:automatic-styles>" section, inside "styles.xml" file.
     *
     * @param int $numWorksheets Number of worksheets created
     * @return string
     */
    protected function getAutomaticStylesSectionContent(\SimpleXMLElement $document, $numWorksheets)
    {
        $stylesAutomatic = $document->addChild('xmlns:office:automatic-styles');

        for ($i = 1; $i <= $numWorksheets; $i++) {
            $pageLayout = $stylesAutomatic->addChild('xmlns:style:page-layout');
            $pageLayout->addAttribute('xmlns:style:name', 'pm' . $i);

            $pageLayoutProperties = $pageLayout->addChild('xmlns:style:page-layout-properties');
            $pageLayoutProperties->addAttribute('xmlns:style:first-page-number', 'continue');
            $pageLayoutProperties->addAttribute('xmlns:style:print', 'objects charts drawings');
            $pageLayoutProperties->addAttribute('xmlns:style:table-centering', 'none');

            $pageLayout->addChild('xmlns:style:header-style');
            $pageLayout->addChild('xmlns:style:footer-style');
        }

        return $stylesAutomatic;
    }

    /**
     * Returns the content of the "<office:master-styles>" section, inside "styles.xml" file.
     *
     * @param int $numWorksheets Number of worksheets created
     * @return string
     */
    protected function getMasterStylesSectionContent(\SimpleXMLElement $document, $numWorksheets)
    {
        $masterStyles = $document->addChild('xmlns:office:master-styles');

        for ($i = 1; $i <= $numWorksheets; $i++) {

            $masterPage = $masterStyles->addChild('xmlns:style:master-page');
            $masterPage->addAttribute('xmlns:style:name', 'mp' . $i);
            $masterPage->addAttribute('xmlns:style:page-layout-name', 'pm' . $i);

            $masterPage->addChild('xmlns:style:header');
            $masterPageHeaderLeft = $masterPage->addChild('xmlns:style:header-left');
            $masterPageHeaderLeft->addAttribute('xmlns:style:display', 'false');
            $masterPage->addChild('xmlns:style:footer');
            $masterPageFooterLeft = $masterPage->addChild('xmlns:style:footer-left');
            $masterPageFooterLeft->addAttribute('xmlns:style:display', 'false');
        }

        return $masterStyles;
    }

    /**
     * Returns the contents of the "<office:font-face-decls>" section, inside "content.xml" file.
     *
     * @return string
     */
    public function getContentXmlFontFaceSectionContent(\SimpleXMLElement $document)
    {
        return $this->getFontFaceSectionContent($document);
    }

    /**
     * Returns the contents of the "<office:automatic-styles>" section, inside "content.xml" file.
     *
     * @param \SimpleXMLElement $parent
     * @param Worksheet[] $worksheets
     * @return \SimpleXMLElement
     */
    public function getContentXmlAutomaticStylesSectionContent(\SimpleXMLElement $parent, $worksheets)
    {
        $stylesAutomatic = $parent->addChild('xmlns:office:automatic-styles');

        foreach ($this->styleRegistry->getRegisteredStyles() as $style) {
            $this->getStyleSectionContent($stylesAutomatic, $style);
        }

        $nodeStyle = $stylesAutomatic->addChild('xmlns:style:style');
        $nodeStyle->addAttribute('xmlns:style:family', 'table-column');
        $nodeStyle->addAttribute('xmlns:style:name', 'co1');

        $nodeTableColumnProp = $nodeStyle->addChild('xmlns:style:table-column-properties');
        $nodeTableColumnProp->addAttribute('xmlns:fo:break-before', 'auto');

        $nodeStyle = $stylesAutomatic->addChild('xmlns:style:style');
        $nodeStyle->addAttribute('xmlns:style:family', 'table-row');
        $nodeStyle->addAttribute('xmlns:style:name', 'ro1');

        $nodeTableRowProp = $nodeStyle->addChild('xmlns:style:table-row-properties');
        $nodeTableRowProp->addAttribute('xmlns:fo:break-before', 'auto');
        $nodeTableRowProp->addAttribute('xmlns:style:row-height', '15pt');
        $nodeTableRowProp->addAttribute('xmlns:style:use-optimal-row-height', 'true');

        foreach ($worksheets as $worksheet) {

            $worksheetId = $worksheet->getId();
            $isSheetVisible = $worksheet->getExternalSheet()->isVisible() ? 'true' : 'false';

            $nodeStyle = $stylesAutomatic->addChild('xmlns:style:style');
            $nodeStyle->addAttribute('xmlns:style:family', 'table');
            $nodeStyle->addAttribute('xmlns:master-page-name', 'mp'.$worksheetId);
            $nodeStyle->addAttribute('xmlns:style:name', 'ta'.$worksheetId);
            

            $nodeTableProp = $nodeStyle->addChild('xmlns:table-properties');
            $nodeTableProp->addAttribute('xmlns:style:writing-mode', 'lr-tb');
            $nodeTableProp->addAttribute('xmlns:table:display', $isSheetVisible);
        }

        return $stylesAutomatic;
    }

    /**
     * Returns the contents of the "<style:style>" section, inside "<office:automatic-styles>" section
     *
     * @param \SimpleXMLElement $nodeAutomaticStyles
     * @param Style $style
     * @return string
     */
    protected function getStyleSectionContent(\SimpleXMLElement $nodeAutomaticStyles, $style)
    {
        $styleIndex = $style->getId() + 1; // 1-based

        $nodeStyle = $nodeAutomaticStyles->addChild('xmlns:style:style');
        $nodeStyle->addAttribute('xmlns:style:data-style-name', 'N0');
        $nodeStyle->addAttribute('xmlns:style:family', 'table-cell');
        $nodeStyle->addAttribute('xmlns:style:name', 'ce' . $styleIndex);
        $nodeStyle->addAttribute('xmlns:style:parent-style-name', 'Default');

        $this->getTextPropertiesSectionContent($nodeStyle, $style);
        $this->getTableCellPropertiesSectionContent($nodeStyle, $style);

        return $nodeStyle;
    }

    /**
     * Returns the contents of the "<style:text-properties>" section, inside "<style:style>" section
     *
     * @param \SimpleXMLElement $nodeStyle
     * @param Style $style
     * @return string
     */
    private function getTextPropertiesSectionContent(\SimpleXMLElement $nodeStyle, $style)
    {
        if ($style->shouldApplyFont()) {
            $this->getFontSectionContent($nodeStyle, $style);
        }

        return $nodeStyle;
    }

    /**
     * Returns the contents of the "<style:text-properties>" section, inside "<style:style>" section
     *
     * @param \SimpleXMLElement $nodeStyle
     * @param Style $style
     * @return string
     */
    private function getFontSectionContent(\SimpleXMLElement $nodeStyle, $style)
    {
        $defaultStyle = $this->getDefaultStyle();

        $nodeTextProperties = $nodeStyle->addChild('xmlns:style:text-properties');

        $fontColor = $style->getFontColor();
        if ($fontColor !== $defaultStyle->getFontColor()) {
            $nodeTextProperties->addAttribute('xmlns:fo:color', '#' . $fontColor);
        }

        $fontName = $style->getFontName();
        if ($fontName !== $defaultStyle->getFontName()) {
            $nodeTextProperties->addAttribute('xmlns:style:font-name', $fontName);
            $nodeTextProperties->addAttribute('xmlns:style:font-name-asian', $fontName);
            $nodeTextProperties->addAttribute('xmlns:font-name-complex', $fontName);
        }

        $fontSize = $style->getFontSize();
        if ($fontSize !== $defaultStyle->getFontSize()) {
            $nodeTextProperties->addAttribute('xmlns:fo:font-size', $fontSize . 'pt');
            $nodeTextProperties->addAttribute('xmlns:style:font-size-asian', $fontSize . 'pt');
            $nodeTextProperties->addAttribute('xmlns:style:font-size-complex', $fontSize . 'pt');
        }

        if ($style->isFontBold()) {
            $nodeTextProperties->addAttribute('xmlns:fo:font-weight', 'bold');
            $nodeTextProperties->addAttribute('xmlns:style:font-weight-asian', 'bold');
            $nodeTextProperties->addAttribute('xmlns:style:font-weight-complex', 'bold');
        }

        if ($style->isFontItalic()) {
            $nodeTextProperties->addAttribute('xmlns:fo:font-style', 'italic');
            $nodeTextProperties->addAttribute('xmlns:style:font-style-asian', 'italic');
            $nodeTextProperties->addAttribute('xmlns:style:font-style-complex', 'italic');
        }

        if ($style->isFontUnderline()) {
            $nodeTextProperties->addAttribute('xmlns:style:text-underline-style', 'solid');
            $nodeTextProperties->addAttribute('xmlns:style:text-underline-type', 'single');
        }

        if ($style->isFontStrikethrough()) {
            $nodeTextProperties->addAttribute('xmlns:style:text-line-through-style', 'solid');
        }

        return $nodeTextProperties;
    }

    /**
     * Returns the contents of the "<style:table-cell-properties>" section, inside "<style:style>" section
     *
     * @param \SimpleXMLElement $parent
     * @param Style $style
     * @return \SimpleXMLElement parent element
     */
    private function getTableCellPropertiesSectionContent(\SimpleXMLElement $parent, $style)
    {
        if ($style->shouldWrapText()) {
            $this->getWrapTextXMLContent($parent);
        }

        if ($style->shouldApplyBorder()) {
            $this->getBorderXMLContent($parent, $style);
        }

        if ($style->shouldApplyBackgroundColor()) {
            $this->getBackgroundColorXMLContent($parent, $style);
        }

        return $parent;
    }

    /**
     * Returns the contents of the wrap text definition for the "<style:table-cell-properties>" section
     *
     * @param \SimpleXMLElement $parent
     * @return \SimpleXMLElement new node
     */
    private function getWrapTextXMLContent(\SimpleXMLElement $parent)
    {
        $node = $parent->addChild('xmlns:style:table-cell-properties');
        $node->addAttribute('xmlns:fo:wrap-option', 'wrap');
        $node->addAttribute('xmlns:style:vertical-align', 'automatic');

        return $node;
    }

    /**
     * Returns the contents of the borders definition for the "<style:table-cell-properties>" section
     *
     * @param Style $style
     * @return \SimpleXMLElement new node
     */
    private function getBorderXMLContent(\SimpleXMLElement $parent, $style)
    {
        $node = $parent->addChild('xmlns:style:table-cell-properties');
        
        BorderHelper::setBorderAttributes($node, $style->getBorder());

        return $node;
    }

    /**
     * Returns the contents of the background color definition for the "<style:table-cell-properties>" section
     *
     * @param \SimpleXMLElement $parent
     * @param Style $style
     * @return \SimpleXMLElement
     */
    private function getBackgroundColorXMLContent(\SimpleXMLElement $parent, $style)
    {
        $node = $parent->addChild('xmlns:style:table-cell-properties');
        $node->addAttribute('xmlns:fo:background-color', '#' . $style->getBackgroundColor());

        return $node;
    }

}
