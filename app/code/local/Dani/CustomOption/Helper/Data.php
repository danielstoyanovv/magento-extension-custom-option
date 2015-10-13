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

    private function productHasDiscount($product)
    {
        return Mage::getResourceModel('catalogrule/rule')->getRulePrice( 
                        Mage::app()->getLocale()->storeTimeStamp($product->getStoreId()), 
                        Mage::app()->getStore($product->getStoreId())->getWebsiteId(), 
                        Mage::getSingleton('customer/session')->getCustomerGroupId(), 
                        $product->getId());      
    }
    
}
