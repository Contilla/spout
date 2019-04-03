<?php

namespace Box\Spout\Writer\ODS\Manager\Style;

use Box\Spout\Common\Entity\Style\Style;

/**
 * Class StyleRegistry
 * Registry for all used styles
 */
class StyleRegistry extends \Box\Spout\Writer\Common\Manager\Style\StyleRegistry {

    /** @var array [FONT_NAME] => [] Map whose keys contain all the fonts used */
    protected $usedFontsSet = [];

    /**
     * @var array
     */
    protected $registeredNumberFormats = [];

    /**
     * @var array [STYLE_ID] => [NUMBERFORMAT_ID] maps a style to a numberformat declaration
     */
    protected $styleIdToNumberFormatMappingTable = [];

    /**
     * Registers the given style as a used style.
     * Duplicate styles won't be registered more than once.
     *
     * @param Style $style The style to be registered
     * @return Style The registered style, updated with an internal ID.
     */
    public function registerStyle(Style $style) {
        $registeredStyle = parent::registerStyle($style);
        $this->usedFontsSet[$style->getFontName()] = true;

        $this->registerNumberFormat($registeredStyle);

        return $registeredStyle;
    }

    /**
     * @return string[] List of used fonts name
     */
    public function getUsedFonts() {
        return array_keys($this->usedFontsSet);
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
     * @return int|null NumberFormat ID associated to the given style ID
     */
    public function getNumberFormatIdForStyleId($styleId) {
        return (isset($this->styleIdToNumberFormatMappingTable[$styleId])) ? $this->styleIdToNumberFormatMappingTable[$styleId] : null;
    }

    /**
     * @return array
     */
    public function getRegisteredNumberFormats() {
        return $this->registeredNumberFormats;
    }

}
