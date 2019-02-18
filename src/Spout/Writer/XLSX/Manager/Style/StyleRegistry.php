<?php

namespace Box\Spout\Writer\XLSX\Manager\Style;

use Box\Spout\Common\Entity\Style\Style;

/**
 * Class StyleRegistry
 * Registry for all used styles
 */
class StyleRegistry extends \Box\Spout\Writer\Common\Manager\Style\StyleRegistry
{
    /**
     * @var array
     */
    protected $registeredFills = [];

    /**
     * @var array [STYLE_ID] => [FILL_ID] maps a style to a fill declaration
     */
    protected $styleIdToFillMappingTable = [];

    /**
     * Excel preserves two default fills with index 0 and 1
     * Since Excel is the dominant vendor - we play along here
     *
     * @var int The fill index counter for custom fills.
     */
    protected $fillIndex = 2;

    /**
     * @var array
     */
    protected $registeredBorders = [];

    /**
     * @var array [STYLE_ID] => [BORDER_ID] maps a style to a border declaration
     */
    protected $styleIdToBorderMappingTable = [];

    /**
     * @var array
     */
    protected $registeredNumberFormats = [];
    
    /**
     * @var array [STYLE_ID] => [NUMBERFORMAT_ID] maps a style to a numberformat declaration
     */
    protected $styleIdToNumberFormatMappingTable = [];
    
    /**
     * XLSX specific operations on the registered styles
     *
     * @param Style $style
     * @return Style
     */
    public function registerStyle(Style $style)
    {
        $registeredStyle = parent::registerStyle($style);
        $this->registerFill($registeredStyle);
        $this->registerBorder($registeredStyle);
        $this->registerNumberFormat($registeredStyle);

        return $registeredStyle;
    }
    
    /**
     * Register a fill definition
     *
     * @param Style $style
     */
    private function registerFill(Style $style)
    {
        $styleId = $style->getId();

        // Currently - only solid backgrounds are supported
        // so $backgroundColor is a scalar value (RGB Color)
        $backgroundColor = $style->getBackgroundColor();

        if ($backgroundColor) {
            $isBackgroundColorRegistered = isset($this->registeredFills[$backgroundColor]);

            // We need to track the already registered background definitions
            if ($isBackgroundColorRegistered) {
                $registeredStyleId = $this->registeredFills[$backgroundColor];
                $registeredFillId = $this->styleIdToFillMappingTable[$registeredStyleId];
                $this->styleIdToFillMappingTable[$styleId] = $registeredFillId;
            } else {
                $this->registeredFills[$backgroundColor] = $styleId;
                $this->styleIdToFillMappingTable[$styleId] = $this->fillIndex++;
            }
        } else {
            // The fillId maps a style to a fill declaration
            // When there is no background color definition - we default to 0
            $this->styleIdToFillMappingTable[$styleId] = 0;
        }
    }

    /**
     * @param int $styleId
     * @return int|null Fill ID associated to the given style ID
     */
    public function getFillIdForStyleId($styleId)
    {
        return (isset($this->styleIdToFillMappingTable[$styleId])) ?
            $this->styleIdToFillMappingTable[$styleId] :
            null;
    }

    /**
     * Register a border definition
     *
     * @param Style $style
     */
    private function registerBorder(Style $style)
    {
        $styleId = $style->getId();

        if ($style->shouldApplyBorder()) {
            $border = $style->getBorder();
            $serializedBorder = serialize($border);

            $isBorderAlreadyRegistered = isset($this->registeredBorders[$serializedBorder]);

            if ($isBorderAlreadyRegistered) {
                $registeredStyleId = $this->registeredBorders[$serializedBorder];
                $registeredBorderId = $this->styleIdToBorderMappingTable[$registeredStyleId];
                $this->styleIdToBorderMappingTable[$styleId] = $registeredBorderId;
            } else {
                $this->registeredBorders[$serializedBorder] = $styleId;
                $this->styleIdToBorderMappingTable[$styleId] = count($this->registeredBorders);
            }
        } else {
            // If no border should be applied - the mapping is the default border: 0
            $this->styleIdToBorderMappingTable[$styleId] = 0;
        }
    }

    /**
     * @param int $styleId
     * @return int|null Fill ID associated to the given style ID
     */
    public function getBorderIdForStyleId($styleId)
    {
        return (isset($this->styleIdToBorderMappingTable[$styleId])) ?
            $this->styleIdToBorderMappingTable[$styleId] :
            null;
    }
    
    /**
     * Register a number format definition
     *
     * @param Style $style
     */
    private function registerNumberFormat(Style $style) {
        $styleId = $style->getId();

        if ($style->shouldApplyNumberFormat()) {
            $numberFormat = $style->getNumberFormat();
            $serializedNumberFormat = serialize($numberFormat);

            $isNumberFormatAlreadyRegistered = isset($this->registeredNumberFormats[$serializedNumberFormat]);

            if ($isNumberFormatAlreadyRegistered) {
                $registeredStyleId = $this->registeredNumberFormats[$serializedNumberFormat];
                $registeredNumberFormatId = $this->styleIdToNumberFormatMappingTable[$registeredStyleId];
                $this->styleIdToNumberFormatMappingTable[$styleId] = $registeredNumberFormatId;
            } else {
                $this->registeredNumberFormats[$serializedNumberFormat] = $styleId;
                $this->styleIdToNumberFormatMappingTable[$styleId] = count($this->registeredNumberFormats);
            }
        } else {
            // If no number format should be applied - the mapping is the default format: 0
            $this->styleIdToNumberFormatMappingTable[$styleId] = 0;
        }
    }

    /**
     * @param int $styleId
     * @return int|null Fill ID associated to the given style ID
     */
    public function getNumberFormatIdForStyleId($styleId) {
        return (isset($this->styleIdToNumberFormatMappingTable[$styleId])) ? $this->styleIdToNumberFormatMappingTable[$styleId] : null;
    }

    /**
     * @return array
     */
    public function getRegisteredFills()
    {
        return $this->registeredFills;
    }

    /**
     * @return array
     */
    public function getRegisteredBorders()
    {
        return $this->registeredBorders;
    }
    
     /**
     * @return array
     */
    public function getRegisteredNumberFormats()
    {
        return $this->registeredNumberFormats;
    }
}
