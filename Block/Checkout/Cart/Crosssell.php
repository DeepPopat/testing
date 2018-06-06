<?php
namespace Emipro\Smartproductselector\Block\Checkout\Cart;

use Magento\Framework\App\ObjectManager;

class Crosssell extends \Magento\Checkout\Block\Cart\Crosssell
{
    /**
     * Get crosssell items
     *
     * @return array
     */
    public function getRulData($ruleProduct, $id)
    {
        return $ruleProduct->load($id)->getData();
    }
    public function getRulProData($rule, $rid)
    {
        return $rule->load($rid)->getData();
    }
    public function getItems()
    {
        $items = $this->getData('items');
        if ($items === null) {
            $items = [];
            $ninProductIds = $this->_getCartProductIds();
            if ($ninProductIds) {
                $lastAdded = (int) $this->_getLastAddedProductId();
                if ($lastAdded) {
                    $collection = $this->_getCollection()->addProductFilter($lastAdded);
                    if (!empty($ninProductIds)) {
                        $collection->addExcludeProductFilter($ninProductIds);
                    }
                    $collection->setPositionOrder()->load();

                    foreach ($collection as $item) {
                        $ninProductIds[] = $item->getId();
                        $items[] = $item;
                    }
                }

                if (count($items) < $this->_maxItemCount) {
                    $filterProductIds = array_merge(
                        $this->_getCartProductIds(),
                        $this->_itemRelationsList->getRelatedProductIds($this->getQuote()->getAllItems())
                    );
                    $collection = $this->_getCollection()->addProductFilter(
                        $filterProductIds
                    )->addExcludeProductFilter(
                        $ninProductIds
                    )->setPageSize(
                        $this->_maxItemCount - count($items)
                    )->setGroupBy()->setPositionOrder()->load();
                    foreach ($collection as $item) {
                        $items[] = $item;
                    }
                }
            }
            $this->setData('items', $items);
        }
        $objectManager = ObjectManager::getInstance();
        $scopeconfig = $objectManager->create("Magento\Framework\App\Config\ScopeConfigInterface");
        $proCountAdmin = $scopeconfig->getValue(
            "smartproductselector/smarproductconfig/productCountAdmin",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $displayPro = $scopeconfig->getValue("smartproductselector/smarproductconfig/displayProduct", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $tablename = $objectManager->
            create('Magento\Framework\App\ResourceConnection')->getTableName('emipro_smartproductselector_products');
        $ruleModel = $objectManager->create('Emipro\Smartproductselector\Model\Rule')->getCollection()->addFieldToFilter('alternative_type', 2);
        $ruleModel->getSelect()->join(['t2' => $tablename], 'main_table.rule_id = t2.rule_id', '*');
        $ruleModel->addFieldToFilter('t2.entity_id', ['in', $this->_getCartProductIds()]);
        $ruleModels = $ruleModel->getData();
        foreach ($ruleModels as $k => $v) {
            $shortOrderBy = $v['short_order_by'];
            $productAttr = $v['product_attribute'];
            $productShort = $v['product_shorting'];
        }

        $smartCrosssellProduct = [];
        if ($proCountAdmin == 0) {
            $smartCrosssellProduct = $objectManager->create('Emipro\Smartproductselector\Helper\Data')->getCrosssellProduct($this->_getCartProductIds());
        } else {
            $smartCrosssell = $objectManager->create('Emipro\Smartproductselector\Model\Smartcrossell')->getCollection()->addFieldToFilter('pro_id', ['in', $this->_getCartProductIds()])->getData();
            $smartCrosssellProducts = [];
            $temp = [];
            foreach ($smartCrosssell as $key => $value) {
                $id = $value['rule_id'];
                $ruleProduct = $objectManager->create('Emipro\Smartproductselector\Model\Ruleproduct');
                $ruleProduct = $this->getRulData($ruleProduct, $id);
                $rid = $ruleProduct['rule_id'];
                $rule = $objectManager->create('Emipro\Smartproductselector\Model\Rule');
                $rule = $this->getRulProData($rule, $rid);
                if ($rule['is_active'] == 1) {
                    $smartCrosssellProducts[] = explode(',', $value['frontpro_id']);
                }
            }
            if ($smartCrosssellProducts) {
                foreach ($smartCrosssellProducts as $ke => $val) {
                    foreach ($val as $k => $v) {
                        if (!in_array($v, $smartCrosssellProduct)) {
                            $smartCrosssellProduct[] = $v;
                        }
                    }
                }
            }
        }
        $smartCrosssellProduct = array_diff($smartCrosssellProduct, $this->_getCartProductIds());
        if ($smartCrosssellProduct) {
            $collection = $objectManager->create('Magento\Catalog\Model\Product')->getCollection();
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('entity_id', ['in' => $smartCrosssellProduct]);
            if ($shortOrderBy == 2) {
                $collection->addAttributeToSort($productAttr, $productShort);
            }
            if ($shortOrderBy == 1) {
                $collection->getSelect()->order('rand()');
            }
            $displayPro ?
            $collection->getSelect()->limit($displayPro) :
            $collection->getSelect()->limit(10);
            return $collection;
        }
        return $items;
    }
}
