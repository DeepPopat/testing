<?php
namespace Emipro\Smartproductselector\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_storeManager;

    protected $date;

    protected $_scopeConfig;

    protected $_catalogProduct;

    protected $messageManager;

    protected $_response;

    protected $_resourceConfig;

    protected $_responseFactory;

    protected $_url;

    protected $_coreRegistry;

    protected $_category;

    protected $_logger;

    protected $_smartRule;

    protected $_smartRuleproduct;

    protected $_smartRelated;

    protected $_smartUpsell;

    protected $_smartcrossell;

    protected $rule_id;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\Config\Storage\WriterInterface $resourceConfig,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $coreRegistrycore,
        \Magento\Catalog\Model\Product $catalogProduct,
        \Magento\Catalog\Model\Category $category,
        \Emipro\Smartproductselector\Model\Rule $smartRule,
        \Emipro\Smartproductselector\Model\Ruleproduct $smartRuleproduct,
        \Emipro\Smartproductselector\Model\Smartrelated $smartRelated,
        \Emipro\Smartproductselector\Model\Smartupsell $smartUpsell,
        \Emipro\Smartproductselector\Model\Smartcrossell $smartcrossell,
        \Magento\Framework\ObjectManagerInterface $objectMan,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_storeManager = $storeManager;
        $this->date = $date;
        $this->_scopeConfig = $scopeConfig;
        $this->_catalogProduct = $catalogProduct;
        $this->messageManager = $messageManager;
        $this->_response = $response;
        $this->_resourceConfig = $resourceConfig;
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        $this->objectMan = $objectMan;
        $this->_coreRegistry = $coreRegistrycore;
        $this->_category = $category;
        $this->_smartRule = $smartRule;
        $this->_smartRuleproduct = $smartRuleproduct;
        $this->_smartRelated = $smartRelated;
        $this->_smartUpsell = $smartUpsell;
        $this->_smartcrossell = $smartcrossell;
        $this->_logger = $logger;
    }

    public function toCategoriesArray()
    {
        $helper = $this->getRootCategories(1);
        $array = [];
        $array[""] = 'Select Category';
        foreach ($helper as $_category) {
            $id = $_category->getId();
            $name = $_category->getName();

            $_category = $this->getCat($id);
            $_subcategories = $_category->getChildrenCategories();
            $childData = $this->getCategoriesRecursively($_subcategories);

            foreach ($childData as $key => $value) {
                $name .= '-->' . $value;
            }
            $array[$id] = $name;
        }
        return $array;
    }
    public function getCat($id)
    {
        return $this->_category->load($id);
    }

    public function getRootCategories($eachStoreId)
    {
        $storeId = $this->_storeManager->getStore($eachStoreId)->getId();
        $rootCat = $this->_storeManager->getStore($storeId)->getRootCategoryId();
        $_categories = $this->_category->getCategories($rootCat);
        return $_categories;
    }

    public function getCategoriesRecursively($categories)
    {
        $array = [];
        foreach ($categories as $category) {
            $id = $category->getId();
            $cat = $this->getCat($id);
            $count = $cat->getProductCount();
            $array[$category->getId()] = $category->getName();
            if ($category->hasChildren()) {
                $children = $this->_category->getCategories($category->getId());
                $arr = $this->getCategoriesRecursively($children);
                foreach ($arr as $key => $value) {
                    $array[$key] = $value;
                }
            }
        }
        return $array;
    }

    public function getSmartRelatedProduct($current_product_id)
    {
        $rule_id = $this->ProductMatch($current_product_id);
        $rule_count = 0;
        $filted_product = null;
        $rule_collection = $this->_smartRule->getCollection()->setOrder('rule_priority', 'asc');

        foreach ($rule_collection as $values) {
            if ($values["is_active"] == 1 && $rule_id != null && in_array($values["rule_id"], $rule_id) && $values["alternative_type"] == 0 && $rule_count == 0) {
                if ($values["set_sku"] == 0) {
                    //echo "<br>getSmartr2";
                    $filted_product = $this->productAttributeFilter($values["rule_id"], $current_product_id);
                } else {
                    $filted_product = $this->productSkuFilter($values["rule_id"], $current_product_id);
                }
                $filted_product->getSelect()->limit($values["no_product"]);
                $rule_count = 1;
                return $filted_product;
            }
        }
        return;
    }

    /* return smart upsell product collection */
    public function getUpsellProduct($current_product_id)
    {
        $rule_count = 0;
        $rule_id = $this->ProductMatch($current_product_id);
        $rule_collection = $this->_smartRule->getCollection()->setOrder('rule_priority', 'asc');

        foreach ($rule_collection as $values) {
            if ($values["is_active"] == 1 && $rule_id != null && in_array($values["rule_id"], $rule_id) && $values["alternative_type"] == 1 && $rule_count == 0) {
                if ($values["set_sku"] == 0) {
                    $filted_product = $this->productAttributeFilter($values["rule_id"], $current_product_id);
                } else {
                    $filted_product = $this->productSkuFilter($values["rule_id"], $current_product_id);
                }
                $filted_product->getSelect()->limit($values["no_product"]);
                $rule_count = 1;
                return $filted_product;
            }
        }
        return false;
    }

    public function ProductMatch($current_product_id)
    {
        $rule_product_collection = $this->_smartRuleproduct->getCollection()->addFieldToFilter('entity_id', ['eq' => $current_product_id])->getData();

        if (!empty($rule_product_collection)) {
            foreach ($rule_product_collection as $value) {
                $rule_id[] = $value["rule_id"];
            }
            return $rule_id;
        } else {
            return false;
        }
    }

    public function tmpproductAttributeFilter($ruleId, $current_product_id)
    {
        $collections = $this->objectMan->create('Magento\Catalog\Model\Product')->load($current_product_id);

        $category_reg = $this->_coreRegistry->registry('current_category');
        if ($category_reg) {
            $cat_id = $category_reg->getId();
        } else {
            $cat_id = $collections->getCategoryIds();
            $cat_id = end($cat_id);
        }

        $_category = $this->_category->load($cat_id);
        $collection = $this->_catalogProduct->getCollection();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', ['neq' => $current_product_id]);
        $rule_info = $this->_smartRule->load($ruleId)->getData();

        if ($rule_info["cat_select"] == 1) {
            $specificCategory = $this->_category->load($rule_info["specific_category_id"]);
            $collection->addCategoryFilter($specificCategory);
            $collection->joinField('category_id', 'catalog_category_product', 'category_id', 'product_id=entity_id', null, 'left');
            $collection->addAttributeToFilter('category_id', $rule_info["specific_category_id"]);
        }

        if ($rule_info["cat_select"] == 2) {
            $collection->addCategoryFilter($_category);
        }

        if ($rule_info["cat_select"] == 3) {
            $collection->addCategoryFilter($_category->getParentCategory());
        }

        /*
         * Configure product attributes condition for display alternative products
         */
        $attribute_conditions = unserialize($rule_info["attribute_conditions"]);
        if (!empty($attribute_conditions)) {
            /*
             * there error for some product_attrbute set into that collection comment and check that
             */
            foreach ($attribute_conditions as $value) {
                $attribute = $value["product_attr"];
                if ($attribute == "quantity_and_stock_status") {
                    $collection->joinField(
                        'qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left'
                    )->addAttributeToFilter('qty', [$value["condition"] => $collections[$attribute]['qty']]);

                } elseif ($attribute == "category_ids") {
                    $collection->addCategoriesFilter([$value['condition'] => $collections[$attribute]]);
                } else {
                    $collection->addAttributeToFilter($value["product_attr"], [$value["condition"] => $collections[$attribute]]);
                }
            }
            if ($rule_info["price_range"] == 1) {
                $collection->addAttributeToFilter('price', ['gteq' => $rule_info["above_price"]])->addAttributeToFilter('price', ['lteq' => $rule_info["below_price"]]);
            }
        } else {
            if ($rule_info["price_range"] == 1) {
                $collection->addAttributeToFilter('price', ['gteq' => $rule_info["above_price"]])
                    ->addAttributeToFilter('price', ['lteq' => $rule_info["below_price"]]);
            }
        }

        /**** Attribute set condition filter  ******/
        if ($rule_info["attribute_set_id"]) {
            $collection->addAttributeToFilter('attribute_set_id', $rule_info["attribute_set_id"]);
        }

        /* short_order_by = 2 [order by attribute]
         * short_order_by = 1 [random order]
         */
        if ($rule_info["short_order_by"] == 2) {
            $collection->addAttributeToSort($rule_info['product_attribute'], $rule_info['product_shorting']);
        }
        if ($rule_info["short_order_by"] == 1) {
            $collection->getSelect()->order('rand()');
        }
        $collection->addAttributeToFilter('visibility', ['neq' => 1]);
        return $collection;
    }
    public function productAttributeFilter($ruleId, $current_product_id)
    {
        try {
            $collections = $this->_catalogProduct->load($current_product_id);
        } catch (\Error $e) {
            $collection = $this->tmpproductAttributeFilter($ruleId, $current_product_id);
            return $collection;
        }
        $category_reg = $this->_coreRegistry->registry('current_category');
        if ($category_reg) {
            $cat_id = $category_reg->getId();
        } else {
            $cat_id = $collections->getCategoryIds();
            $cat_id = end($cat_id);
        }

        $_category = $this->_category->load($cat_id);
        $collection = $this->_catalogProduct->getCollection();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', ['neq' => $current_product_id]);
        $rule_info = $this->_smartRule->load($ruleId)->getData();

        if ($rule_info["cat_select"] == 1) {
            $specificCategory = $this->_category->load($rule_info["specific_category_id"]);
            $collection->addCategoryFilter($specificCategory);
            $collection->joinField('category_id', 'catalog_category_product', 'category_id', 'product_id=entity_id', null, 'left');
            $collection->addAttributeToFilter('category_id', $rule_info["specific_category_id"]);
        }

        if ($rule_info["cat_select"] == 2) {
            $collection->addCategoryFilter($_category);
        }

        if ($rule_info["cat_select"] == 3) {
            $collection->addCategoryFilter($_category->getParentCategory());
        }

        /*
         * Configure product attributes condition for display alternative products
         */
        $attribute_conditions = unserialize($rule_info["attribute_conditions"]);
        if (!empty($attribute_conditions)) {
            /*
             * there error for some product_attrbute set into that collection comment and check that
             */
            foreach ($attribute_conditions as $value) {
                $attribute = $value["product_attr"];
                if ($attribute == "quantity_and_stock_status") {
                    $collection->joinField(
                        'qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left'
                    )->addAttributeToFilter('qty', [$value["condition"] => $collections[$attribute]['qty']]);

                } elseif ($attribute == "category_ids") {
                    $collection->addCategoriesFilter([$value['condition'] => $collections[$attribute]]);
                } else {
                    $collection->addAttributeToFilter($value["product_attr"], [$value["condition"] => $collections[$attribute]]);
                }
                //$collection->addAttributeToFilter('type_id', array('nin' => $collections[$attribute]));
            }
            if ($rule_info["price_range"] == 1) {
                $collection->addAttributeToFilter('price', ['gteq' => $rule_info["above_price"]])->addAttributeToFilter('price', ['lteq' => $rule_info["below_price"]]);
            }
        } else {
            if ($rule_info["price_range"] == 1) {
                $collection->addAttributeToFilter('price', ['gteq' => $rule_info["above_price"]])
                    ->addAttributeToFilter('price', ['lteq' => $rule_info["below_price"]]);
            }
        }

        /**** Attribute set condition filter  ******/
        if ($rule_info["attribute_set_id"]) {
            $collection->addAttributeToFilter('attribute_set_id', $rule_info["attribute_set_id"]);
        }

        /* short_order_by = 2 [order by attribute]
         * short_order_by = 1 [random order]
         */
        if ($rule_info["short_order_by"] == 2) {
            $collection->addAttributeToSort($rule_info['product_attribute'], $rule_info['product_shorting']);
        }
        if ($rule_info["short_order_by"] == 1) {
            $collection->getSelect()->order('rand()');
        }
        $collection->addAttributeToFilter('visibility', ['neq' => 1]);
        return $collection;
    }

    public function productSkuFilter($ruleId, $current_product_id)
    {
        $rule_info = $this->_smartRule->load($ruleId)->getData();
        $skuData = array_map('trim', explode(',', $rule_info['sku_data']));
        $collection = $this->_catalogProduct->getCollection();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', ['neq' => $current_product_id]);
        $collection->addAttributeToFilter('sku', ['in' => $skuData]);
        /* short_order_by = 2 [order by attribute]
         * short_order_by = 1 [random order]
         */
        if ($rule_info["short_order_by"] == 2) {
            $collection->addAttributeToSort($rule_info['product_attribute'], $rule_info['product_shorting']);
        }
        if ($rule_info["short_order_by"] == 1) {
            $collection->getSelect()->order('rand()');
        }
        $collection->addAttributeToFilter('visibility', ['neq' => 1]);
        return $collection;
    }

    public function getCrosssellProduct($cart_pro)
    {
        $rule_count = 0;
        $filted_product = [];
        $rule_id = $this->ProductMatchCrosssell($cart_pro);
        $rule_collection = $this->_smartRule->getCollection()->addFieldToFilter('alternative_type', 2)->setOrder('rule_priority', 'asc');
        $applyed_rule_id = [];
        foreach ($cart_pro as $pro_id) {
            foreach ($rule_collection as $values) {
                if ($values["is_active"] == 1 && $rule_id != null && in_array($values["rule_id"], $rule_id)) {
                    $filted_pro = null;
                    if ($values["set_sku"] == 0) {
                        $filted_pro = $this->productAttributeFilter($values["rule_id"], $pro_id);
                    } else {
                        $filted_pro = $this->productSkuFilter($values["rule_id"], $pro_id);
                    }
                    $filted_pro->getSelect()->limit($values["no_product"]);
                    $filted_product[] = $filted_pro->getData();
                    $rule_count = 1;
                }
            }
        }
        return $this->getProduct($filted_product);
    }

    public function ProductMatchCrosssell($cart_pro)
    {
        $rule_product_collection = $this->_smartRuleproduct->getCollection()->addFieldToFilter('entity_id', ['in' => [$cart_pro]])->getData();
        if ($rule_product_collection != null) {
            foreach ($rule_product_collection as $value) {
                $rule_id[] = $value["rule_id"];
            }
            return $rule_id;
        } else {
            return false;
        }
    }

    public function getProduct($product_collection)
    {
        $product = [];
        foreach ($product_collection as $value) {
            foreach ($value as $pro) {
                $product[] = $pro['entity_id'];
            }
        }
        return array_unique($product);
    }
    public function getRuleData($id)
    {
        return $this->_smartRule->load($id)->getData();
    }
    public function relaMod($relatedModels)
    {
        return $relatedModels->save();
    }
    public function upsellMod($upsellModels)
    {
        return $upsellModels->save();
    }
    public function crossMod($crosssellModels)
    {
        return $crosssellModels->save();
    }
    public function getSmartApplyData($pids)
    {
        $ruleProduct = $this->_smartRuleproduct->getCollection()->addFieldToFilter('rule_id', $pids)->getData();
        foreach ($ruleProduct as $key => $value) {
            $id = $value['rule_id'];
            $ruleCollection = $this->getRuleData($id);
            switch ($ruleCollection['alternative_type']) {
                case 0:
                    $collect = $this->getSmartRelatedProduct($value['entity_id']);
                    if ($collect) {
                        foreach ($collect->getData() as $k => $v) {
                            $productId[$value['entity_id']][] = $v['entity_id'];
                        }
                    }
                    $relatedModels = $this->objectMan->create('Emipro\Smartproductselector\Model\Smartrelated');
                    $modelData = $this->_smartRelated->load($value['entity_id'], 'pro_id');
                    if ($modelData->getRelatedId()) {
                        $relatedModels->setData('related_id', $modelData->getRelatedId());
                    }

                    if (isset($productId[$value['entity_id']])) {
                        $pro = implode(',', $productId[$value['entity_id']]);
                        $relatedModels->setData('pro_id', $value['entity_id']);
                        $relatedModels->setData('frontpro_id', $pro);
                        $relatedModels->setData('updated_at', $this->date->gmtDate());
                        $relatedModels->setData('rule_id', $value['id']);
                        $relatedModels = $this->relaMod($relatedModels);
                    }
                    break;

                case 1:
                    $collect = $this->getUpsellProduct($value['entity_id']);
                    foreach ($collect->getData() as $k => $v) {
                        $productId[$value['entity_id']][] = $v['entity_id'];
                    }
                    $upsellModel = $this->_smartUpsell;
                    $upsellModels = $this->objectMan->create('Emipro\Smartproductselector\Model\Smartupsell');
                    $modelData = $upsellModel->load($value['entity_id'], 'pro_id');
                    if ($modelData->getUpsellId()) {
                        $upsellModels->setData('upsell_id', $modelData->getUpsellId());
                    }

                    if (isset($productId[$value['entity_id']])) {
                        $pro = implode(',', $productId[$value['entity_id']]);
                        $upsellModels->setData('pro_id', $value['entity_id']);
                        $upsellModels->setData('frontpro_id', $pro);
                        $upsellModels->setData('updated_at', $this->date->gmtDate());
                        $upsellModels->setData('rule_id', $value['id']);
                        $upsellModels = $this->upsellMod($upsellModels);

                    }
                    break;

                case 2:
                    $prod = [];
                    $prod[0] = $value['entity_id'];
                    $collect = $this->getCrosssellProduct($prod);
                    $productId[$value['entity_id']] = $collect;
                    $crosssellModels = $this->objectMan->create('Emipro\Smartproductselector\Model\Smartcrossell');
                    $modelData = $this->_smartcrossell->load($value['entity_id'], 'pro_id');

                    if ($modelData->getCrossellId()) {
                        $crosssellModels->setData('crossell_id', $modelData->getCrossellId());
                    }

                    if (isset($productId[$value['entity_id']])) {
                        $pro = implode(',', $productId[$value['entity_id']]);
                        $crosssellModels->setData('pro_id', $value['entity_id']);
                        $crosssellModels->setData('frontpro_id', $pro);
                        $crosssellModels->setData('updated_at', $this->date->gmtDate());
                        $crosssellModels->setData('rule_id', $value['id']);
                        $crosssellModels = $this->crossMod($crosssellModels);
                    }
                    break;

                default:
                    $collect = [];
                    $productId = [];
                    break;
            }
        }
    }
}
