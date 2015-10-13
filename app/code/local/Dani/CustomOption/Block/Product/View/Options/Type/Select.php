<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Select
 *
 * @author daniel
 */
class Dani_CustomOption_Block_Product_View_Options_Type_Select extends Mage_Catalog_Block_Product_View_Options_Type_Select
{
    /**
     * Return formated price
     *
     * @param array $value
     * @return string
     */
    protected function _formatPrice($value, $flag=true)
    {
        if ($value['pricing_value'] == 0) {
            return '';
        }

        if(Mage::helper('customoption')->priceHasDiscount($this->getProduct(), $value['pricing_value']))
        {
            $value['pricing_value'] = Mage::helper('customoption')->priceHasDiscount($this->getProduct(), $value['pricing_value']);
        }
        
        $taxHelper = Mage::helper('tax');
        $store = $this->getProduct()->getStore();

        $sign = '+';
        if ($value['pricing_value'] < 0) {
            $sign = '-';
            $value['pricing_value'] = 0 - $value['pricing_value'];
        }

        $priceStr = $sign;
        $_priceInclTax = $this->getPrice($value['pricing_value'], true);
        $_priceExclTax = $this->getPrice($value['pricing_value']);
        if ($taxHelper->displayPriceIncludingTax()) {
            $priceStr .= $this->helper('core')->currencyByStore($_priceInclTax, $store, true, $flag);
        } elseif ($taxHelper->displayPriceExcludingTax()) {
            $priceStr .= $this->helper('core')->currencyByStore($_priceExclTax, $store, true, $flag);
        } elseif ($taxHelper->displayBothPrices()) {
            $priceStr .= $this->helper('core')->currencyByStore($_priceExclTax, $store, true, $flag);
            if ($_priceInclTax != $_priceExclTax) {
                $priceStr .= ' ('.$sign.$this->helper('core')
                    ->currencyByStore($_priceInclTax, $store, true, $flag).' '.$this->__('Incl. Tax').')';
            }
        }

        if ($flag) {
            $priceStr = '<span class="price-notice">'.$priceStr.'</span>';
        }
        
        return $priceStr;
    }
}
