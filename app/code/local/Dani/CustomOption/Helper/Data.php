<?php
class Dani_CustomOption_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getProductCatalogPriceRule($product, $price) 
    {
        $ruleModel = new Dani_CustomOption_Model_Rule();
        $ruleModel->calcProductPriceRule($product, $price);
        return $ruleModel::$_priceRulesData;
    }
    
    public function generateCacheKey($product, $price)
    {
        $productId       = $product->getId();
        $storeId         = $product->getStoreId();
        $websiteId       = Mage::app()->getStore($storeId)->getWebsiteId();
        $dateTs          = Mage::app()->getLocale()->date()->getTimestamp();
        
        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        
        return date('Y-m-d', $dateTs) . "|$websiteId|$customerGroupId|$productId|$price";
    }
    
    public function priceHasDiscount($product, $price)
    {
        if(!$this->productHasDiscount($product))
            return false;

        $ruleData = $this->getProductCatalogPriceRule($product, $price);
        $cacheKey = $this->generateCacheKey($product, $price);
        
        if(array_key_exists($cacheKey, $ruleData))
        {
            return $ruleData[$cacheKey];
        }
        
        return false;
    }

    public function productHasDiscount($product)
    {
        return Mage::getResourceModel('catalogrule/rule')->getRulePrice( 
                        Mage::app()->getLocale()->storeTimeStamp($product->getStoreId()), 
                        Mage::app()->getStore($product->getStoreId())->getWebsiteId(), 
                        Mage::getSingleton('customer/session')->getCustomerGroupId(), 
                        $product->getId());      
    }

    public function getRetailProductPrice($product)
    {
        $price             = $product->getPrice();
        $defaultPrices     = $this->getCustomOptionsPrices($product);
        $selectedIds       = $this->getSelectedCustomOptionsIds($product);
        $totalDefaultPrice = 0; 

        if($product->getSpecialPrice != '')
        {
            $price = $product->getSpecialPrice();
        }

        $totalDefaultPrice += $price;

        foreach ($selectedIds as $optionId => $value) {
            if(is_array($value))
            {
                foreach ($value as $k => $v) {
                    if(isset($defaultPrices[$optionId][$v]))
                    {
                        $totalDefaultPrice += $defaultPrices[$optionId][$v];
                    }
                }
            }
            else
            {
                if(isset($defaultPrices[$optionId][$value]))
                {
                    $totalDefaultPrice += $defaultPrices[$optionId][$value];
                }
            }

        }

        return $totalDefaultPrice;
    }

    public function getRulePriceData($product)
    {
        return Mage::getResourceModel('catalogrule/rule')->getRulesFromProduct(
            Mage::app()->getLocale()->storeTimeStamp($product->getStoreId()), 
            Mage::app()->getStore($product->getStoreId())->getWebsiteId(), 
             Mage::getSingleton('customer/session')->getCustomerGroupId(),
            $product->getId());
    }

    public function calculateDiscount($product)
    {
        $dicountPrice = 0;
        $totalDefaultPrice = $this->getRetailProductPrice($product);


        return $totalDefaultPrice - $product->getFinalPrice(); 

    }

    public function getSelectedCustomOptionsIds($product)
    {
        $optionsIds          = array();
        $selectedOptionsData = $product->getTypeInstance(true)->getOrderOptions($product);
        if(isset($selectedOptionsData['info_buyRequest']) && isset($selectedOptionsData['info_buyRequest']['options']))
        {
            $optionsIds = $selectedOptionsData['info_buyRequest']['options'];   
        }

        return $optionsIds;
    }

    public function getCustomOptionsPrices($product)
    {
        $pricesData      = array();
        $selectedOptions = $this->getSelectedCustomOptionsIds($product);
        foreach($selectedOptions as $optionId => $v)
        {
            foreach(Mage::getModel('catalog/product_option_value')->getValuesCollection($product->getOptionById($optionId)) as $option)
            {
                $pricesData[$option->getOptionId()][$option->getOptionTypeId()] = $option->getPrice();
            }
        }

        return $pricesData;
    }

    public function calculateSubtotalDefault()
    {   
        $defaultSubtotalPrice = 0;
        $cartData = Mage::getModel('checkout/cart')->getQuote();
        foreach ($cartData->getAllVisibleItems() as $item) {
            $defaultSubtotalPrice += $this->getRetailProductPrice($item->getProduct());
        }

        return $defaultSubtotalPrice;
    }

    public function checkIfOptionHasMultiSelect($optionId, $product)
    {
        $selectedOptions        = $this->getSelectedCustomOptionsIds($product);
        $defaultPrices          = $this->getCustomOptionsPrices($product);
        $selectedOptionPrices   = array();

        if(is_array($defaultPrices) && is_array($selectedOptions) && isset($selectedOptions[$optionId]) && is_array($selectedOptions[$optionId]))
        {
            foreach ($selectedOptions[$optionId] as $key => $value) {
                if(isset($defaultPrices[$optionId][$value]))
                {
                    $selectedOptionPrices[] = $defaultPrices[$optionId][$value]; 
                }
            }

            return $selectedOptionPrices;
        }
        
        return false;
        
    }
}
