<?php
namespace Emipro\Smartproductselector\Block\Product\ProductList;

class Related extends \Magento\Catalog\Block\Product\ProductList\Related
{
    public function getItems()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currentProductId = $this->getRequest()->getParam('id');
        $scopeconfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $scopeconfigVal = $scopeconfig->getValue("smartproductselector/smarproductconfig/productCountAdmin", $storeScope);
        if ($scopeconfigVal == 0) {
            // product collection calculate at frontside
            $smartRelatedProduct = $objectManager->create('Emipro\Smartproductselector\Helper\Data')->getSmartRelatedProduct($currentProductId);
            try {
            } catch (\Exception $e) {
                print_r("error found");
                return false;
            }
        } else {
            // product collection calculate already admin side
            $smartRelated = $objectManager->create('Emipro\Smartproductselector\Model\Smartrelated')->load($currentProductId, 'pro_id');

            $productId = $smartRelated->getRuleId();
            $smartRelateds = explode(',', $smartRelated->getFrontproId());

            $ruleId = $objectManager->create('Emipro\Smartproductselector\Model\Ruleproduct')->load($productId)->getRuleId();
            $rule = $objectManager->create('Emipro\Smartproductselector\Model\Rule')->load($ruleId)->getData();
            if (!empty($rule)) {
                if ($rule["is_active"] == 1) {
                    $smartRelatedProduct = $objectManager->create('Magento\Catalog\Model\Product')->getCollection();
                    $smartRelatedProduct->addAttributeToSelect('*');
                    $smartRelatedProduct->addAttributeToFilter('entity_id', ['in' => $smartRelateds]);

                    /* short_order_by = 2 [order by attribute]
                     * short_order_by = 1 [random order]
                     */
                    if ($rule["short_order_by"] == 2) {
                        $smartRelatedProduct->addAttributeToSort($rule['product_attribute'], $rule['product_shorting']);
                    }
                    if ($rule["short_order_by"] == 1) {
                        $smartRelatedProduct->getSelect()->order('rand()');
                    }
                }
            } else {
                $smartRelatedProduct = $objectManager->create('Magento\Catalog\Model\Product')->getCollection();
                $smartRelatedProduct->addAttributeToSelect('*');
                $smartRelatedProduct->addAttributeToFilter('entity_id', ['in' => $smartRelateds]);
            }
        }

        if ($this->_itemCollection->getData() != null && $smartRelatedProduct != null) {
            $smartRelatedProductIds = [];
            foreach ($smartRelatedProduct as $value) {
                $smartRelatedProductIds[] = $value->getId();
            }
            $relatedProductIds = $this->_itemCollection->getAllIds();
            $Ids = array_merge($relatedProductIds, $smartRelatedProductIds);
            $merged_ids = array_unique($Ids);

            $merged_collection = $objectManager->create('Magento\Catalog\Model\Product')
                ->getCollection()
                ->addFieldToFilter('entity_id', ['in' => $merged_ids])
                ->addAttributeToSelect('*');
            return $merged_collection;
        } else {
            if ($smartRelatedProduct != null) {
                return $smartRelatedProduct;
            }
        }
        return $this->_itemCollection;
    }
}
