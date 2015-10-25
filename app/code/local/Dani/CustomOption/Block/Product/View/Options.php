<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product options block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
//die('Dani_CustomOption_Block_Product_View_Options');
class Dani_CustomOption_Block_Product_View_Options extends Mage_Catalog_Block_Product_View_Options
{
   
    /**
     * Get price configuration
     *
     * @param Mage_Catalog_Model_Product_Option_Value|Mage_Catalog_Model_Product_Option $option
     * @return array
     */
    protected function _getPriceConfiguration($option)
    {
        $optionPrice        = $option->getPrice(true);
        $optionOldPrice     = $option->getPrice(false);
        
        if(is_numeric($this->helper('customoption')->priceHasDiscount($this->getProduct(), $optionPrice)))
        {
            $optionPrice = $this->helper('customoption')->priceHasDiscount($this->getProduct(), $optionPrice);
        }
        
        if(is_numeric($this->helper('customoption')->priceHasDiscount($this->getProduct(), $optionOldPrice)))
        {
            $optionOldPrice = $this->helper('customoption')->priceHasDiscount($this->getProduct(), $optionOldPrice);
        }


        
        $data = array();
        /*
        $data['price']      = Mage::helper('core')->currency($option->getPrice(true), false, false);
        $data['oldPrice']   = Mage::helper('core')->currency($option->getPrice(false), false, false);
        $data['priceValue'] = $option->getPrice(false);
        $data['type']       = $option->getPriceType();
        $data['excludeTax'] = $price = Mage::helper('tax')->getPrice($option->getProduct(), $data['price'], false);
        $data['includeTax'] = $price = Mage::helper('tax')->getPrice($option->getProduct(), $data['price'], true);
        */

        $data['priceDefault'] = $option->getPrice(true);
        $data['price']        = Mage::helper('core')->currency($optionPrice, false, false);
        $data['oldPrice']     = Mage::helper('core')->currency($optionOldPrice, false, false);
        $data['priceValue']   = $optionOldPrice;
        $data['type']         = $option->getPriceType();
        $data['excludeTax']   = $price = Mage::helper('tax')->getPrice($option->getProduct(), $data['price'], false);
        $data['includeTax']   = $price = Mage::helper('tax')->getPrice($option->getProduct(), $data['price'], true);
        

        return $data;
    }

    
}
