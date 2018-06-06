<?php
namespace Emipro\Smartproductselector\Block\Product\ProductList;

class Upsell extends \Magento\Catalog\Block\Product\ProductList\Upsell
{
    public function getItemCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currentProductId = $this->getRequest()->getParam('id');

        $scopeconfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $scopeconfigVal = $scopeconfig->getValue("smartproductselector/smarproductconfig/productCountAdmin", $storeScope);
        if ($scopeconfigVal == 0) {
            $smartUpsellProduct = $objectManager->create('Emipro\Smartproductselector\Helper\Data')->getUpsellProduct($currentProductId);
        } else {
            $smartUpsell = $objectManager->create('Emipro\Smartproductselector\Model\Smartupsell')->load($currentProductId, 'pro_id');
            $productId = $smartUpsell->getRuleId();
            $smartUpsells = explode(',', $smartUpsell->getFrontproId());

            $ruleId = $objectManager->create('Emipro\Smartproductselector\Model\Ruleproduct')->load($productId)->getRuleId();
            $rule = $objectManager->create('Emipro\Smartproductselector\Model\Rule')->load($ruleId)->getData();
            if (!empty($rule)) {
                if ($rule["is_active"] == 1) {
                    $smartUpsellProduct = $objectManager->create('Magento\Catalog\Model\Product')->getCollection();
                    $smartUpsellProduct->addAttributeToSelect('*');
                    $smartUpsellProduct->addAttributeToFilter('entity_id', ['in' => $smartUpsells]);

                    /* short_order_by = 2 [order by attribute]
                     * short_order_by = 1 [random order]
                     */
                    if ($rule["short_order_by"] == 2) {
                        $smartUpsellProduct->addAttributeToSort($rule['product_attribute'], $rule['product_shorting']);
                    }
                    if ($rule["short_order_by"] == 1) {
                        $smartUpsellProduct->getSelect()->order('rand()');
                    }
                }
            } else {
                $smartUpsellProduct = $objectManager->create('Magento\Catalog\Model\Product')->getCollection();
                $smartUpsellProduct->addAttributeToSelect('*');
                $smartUpsellProduct->addAttributeToFilter('entity_id', ['in' => $smartUpsells]);
            }
        }

        if ($this->_itemCollection->getData() != null && $smartUpsellProduct != null) {
            $smartUpsellProductIds = [];
            foreach ($smartUpsellProduct as $value) {
                $smartUpsellProductIds[] = $value->getId();
            }
            $upsellProductIds = $this->_itemCollection->getAllIds();
            $Ids = array_merge($upsellProductIds, $smartUpsellProductIds);
            $merged_ids = array_unique($Ids);

            $merged_collection = $objectManager->create('Magento\Catalog\Model\Product')
                ->getCollection()
                ->addFieldToFilter('entity_id', ['in' => $merged_ids])
                ->addAttributeToSelect('*');
            return $merged_collection;
        } else {
            if ($smartUpsellProduct != null) {
                return $smartUpsellProduct;
            }
        }
        return $this->_itemCollection;
    }
}
