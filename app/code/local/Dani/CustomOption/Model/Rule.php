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
 * @package     Mage_CatalogRule
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Rule data model
 *
 * @method Mage_CatalogRule_Model_Resource_Rule _getResource()
 * @method Mage_CatalogRule_Model_Resource_Rule getResource()
 * @method string getName()
 * @method Mage_CatalogRule_Model_Rule setName(string $value)
 * @method string getDescription()
 * @method Mage_CatalogRule_Model_Rule setDescription(string $value)
 * @method string getFromDate()
 * @method Mage_CatalogRule_Model_Rule setFromDate(string $value)
 * @method string getToDate()
 * @method Mage_CatalogRule_Model_Rule setToDate(string $value)
 * @method Mage_CatalogRule_Model_Rule setCustomerGroupIds(string $value)
 * @method int getIsActive()
 * @method Mage_CatalogRule_Model_Rule setIsActive(int $value)
 * @method string getConditionsSerialized()
 * @method Mage_CatalogRule_Model_Rule setConditionsSerialized(string $value)
 * @method string getActionsSerialized()
 * @method Mage_CatalogRule_Model_Rule setActionsSerialized(string $value)
 * @method int getStopRulesProcessing()
 * @method Mage_CatalogRule_Model_Rule setStopRulesProcessing(int $value)
 * @method int getSortOrder()
 * @method Mage_CatalogRule_Model_Rule setSortOrder(int $value)
 * @method string getSimpleAction()
 * @method Mage_CatalogRule_Model_Rule setSimpleAction(string $value)
 * @method float getDiscountAmount()
 * @method Mage_CatalogRule_Model_Rule setDiscountAmount(float $value)
 * @method string getWebsiteIds()
 * @method Mage_CatalogRule_Model_Rule setWebsiteIds(string $value)
 *
 * @category    Mage
 * @package     Mage_CatalogRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Dani_CustomOption_Model_Rule extends Mage_CatalogRule_Model_Rule
{
   
    public static $_priceRulesData = array();

   
    /**
     * Calculate price using catalog price rule of product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float $price
     * @return float|null
     */
    public function calcProductPriceRule(Mage_Catalog_Model_Product $product, $price)
    {
        $priceRules = null;
        $productId  = $product->getId();
        $storeId    = $product->getStoreId();
        $websiteId  = Mage::app()->getStore($storeId)->getWebsiteId();
        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        $dateTs     = Mage::app()->getLocale()->date()->getTimestamp();
        $cacheKey   = date('Y-m-d', $dateTs) . "|$websiteId|$customerGroupId|$productId|$price";

        if (!array_key_exists($cacheKey, self::$_priceRulesData)) {
            $rulesData = $this->_getResource()->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
            if ($rulesData) {
                foreach ($rulesData as $ruleData) {
                    if ($product->getParentId()) {
                        if (!empty($ruleData['sub_simple_action'])) {
                            $priceRules = Mage::helper('catalogrule')->calcPriceRule(
                                $ruleData['sub_simple_action'],
                                $ruleData['sub_discount_amount'],
                                $priceRules ? $priceRules : $price
                            );
                        } else {
                            $priceRules = ($priceRules ? $priceRules : $price);
                        }
                        if ($ruleData['action_stop']) {
                            break;
                        }
                    } else {
                        $priceRules = Mage::helper('catalogrule')->calcPriceRule(
                            $ruleData['action_operator'],
                            $ruleData['action_amount'],
                            $priceRules ? $priceRules : $price
                        );
                        if ($ruleData['action_stop']) {
                            break;
                        }
                    }
                }
                return self::$_priceRulesData[$cacheKey] = $priceRules;
            } else {
                self::$_priceRulesData[$cacheKey] = null;
            }
        } else {
            return self::$_priceRulesData[$cacheKey];
        }
        return null;
    }

    
}
