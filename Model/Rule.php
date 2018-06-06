<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Emipro\Smartproductselector\Model;

class Rule extends \Magento\Rule\Model\AbstractModel
{
    protected $_eventPrefix = 'emipro_smartproductselector_rules';

    protected $_eventObject = 'rule';

    protected $_productIds;

    protected $_productsFilter = null;

    protected $_now;

    protected static $_priceRulesData = [];

    protected $_catalogRuleData;

    protected $_cacheTypesList;

    protected $_relatedCacheTypes;

    protected $_resourceIterator;

    protected $_customerSession;

    protected $_combineFactory;

    protected $_actionCollectionFactory;

    protected $_productFactory;

    protected $_storeManager;

    protected $_productCollectionFactory;

    protected $dateTime;

    protected $_ruleProductProcessor;

    protected $templateRuleConditionCombineFactory;

    protected $ruleActionCollectionFactory;

    public function __construct(
        \Emipro\Smartproductselector\Model\Template\Rule\Condition\CombineFactory $templateRuleConditionCombineFactory,
        \Emipro\Smartproductselector\Model\Template\Rule\Action\CollectionFactory $ruleActionCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypesList,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor $ruleProductProcessor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_combineFactory = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
        $this->_productFactory = $productFactory;
        $this->_resourceIterator = $resourceIterator;
        $this->_customerSession = $customerSession;
        $this->_catalogRuleData = $catalogRuleData;
        $this->_cacheTypesList = $cacheTypesList;
        $this->_relatedCacheTypes = $relatedCacheTypes;
        $this->dateTime = $dateTime;
        $this->_ruleProductProcessor = $ruleProductProcessor;
        $this->templateRuleConditionCombineFactory = $templateRuleConditionCombineFactory;
        $this->ruleActionCollectionFactory = $ruleActionCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Emipro\Smartproductselector\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    public function getConditionsInstance()
    {
        return $this->templateRuleConditionCombineFactory->create();
        //return $this->_combineFactory->create();
    }

    public function getActionsInstance()
    {
        return $this->ruleActionCollectionFactory->create();
        //return $this->_actionCollectionFactory->create();
    }

    public function getCustomerGroupIds()
    {
        if (!$this->hasCustomerGroupIds()) {
            $customerGroupIds = $this->_getResource()->getCustomerGroupIds($this->getId());
            $this->setData('customer_group_ids', (array) $customerGroupIds);
        }
        return $this->_getData('customer_group_ids');
    }

    public function getNow()
    {
        if (!$this->_now) {
            return (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }
        return $this->_now;
    }

    public function setNow($now)
    {
        $this->_now = $now;
    }

    public function getMatchingProductIds()
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $productCollection = $this->_productCollectionFactory->create();
            if ($this->_productsFilter) {
                $productCollection->addIdFilter($this->_productsFilter);
            }
            $this->getConditions()->collectValidatedAttributes($productCollection);

            $this->_resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $this->_productFactory->create(),
                ]
            );
        }
        return $this->_productIds;
    }

    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $websites = $this->_getWebsitesMap();
        $results = [];

        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            $results[$websiteId] = $this->getConditions()->validate($product);
        }
        $this->_productIds[$product->getId()] = $results;
    }

    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = $this->_storeManager->getWebsites(true);
        foreach ($websites as $website) {
            // Continue if website has no store to be able to create catalog rule for website without store
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }

    public function validateData(\Magento\Framework\DataObject $dataObject)
    {
        $result = parent::validateData($dataObject);
        if ($result === true) {
            $result = [];
        }

        $action = $dataObject->getData('simple_action');
        $discount = $dataObject->getData('discount_amount');
        $result = array_merge($result, $this->validateDiscount($action, $discount));
        if ($dataObject->getData('sub_is_enable') == 1) {
            $action = $dataObject->getData('sub_simple_action');
            $discount = $dataObject->getData('sub_discount_amount');
            $result = array_merge($result, $this->validateDiscount($action, $discount));
        }

        return !empty($result) ? $result : true;
    }

    protected function validateDiscount($action, $discount)
    {
        $result = [];
        switch ($action) {
            case 'by_percent':
            case 'to_percent':
                if ($discount < 0 || $discount > 100) {
                    $result[] = __('Percentage discount should be between 0 and 100.');
                };
                break;
            case 'by_fixed':
            case 'to_fixed':
                if ($discount < 0) {
                    $result[] = __('Discount value should be 0 or greater.');
                };
                break;
            default:
                $result[] = __('Unknown action.');
        }
        return $result;
    }

    public function calcProductPriceRule(Product $product, $price)
    {
        $priceRules = null;
        $productId = $product->getId();
        $storeId = $product->getStoreId();
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = $this->_customerSession->getCustomerGroupId();
        }
        $dateTs = $this->_localeDate->scopeTimeStamp($storeId);
        $cacheKey = date('Y-m-d', $dateTs) . "|{$websiteId}|{$customerGroupId}|{$productId}|{$price}";

        if (!array_key_exists($cacheKey, self::$_priceRulesData)) {
            $rulesData = $this->_getRulesFromProduct($dateTs, $productId);
            if ($rulesData) {
                foreach ($rulesData as $ruleData) {
                    if ($product->getParentId()) {
                        if (!empty($ruleData['sub_simple_action'])) {
                            $priceRules = $this->_catalogRuleData->calcPriceRule(
                                $ruleData['sub_simple_action'],
                                $ruleData['sub_discount_amount'],
                                $priceRules ? $priceRules : $price
                            );
                        } else {
                            $priceRules = $priceRules ? $priceRules : $price;
                        }
                        if ($ruleData['action_stop']) {
                            break;
                        }
                    } else {
                        $priceRules = $this->_catalogRuleData->calcPriceRule(
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

    protected function _getRulesFromProduct($dateTs, $productId)
    {
        return $this->_getResource()->getRulesFromProduct($dateTs, $productId);
    }

    public function setProductsFilter($productIds)
    {
        $this->_productsFilter = $productIds;
    }

    public function getProductsFilter()
    {
        return $this->_productsFilter;
    }

    protected function _invalidateCache()
    {
        $relatedCacheTypes = count($this->_relatedCacheTypes);
        if ($relatedCacheTypes) {
            $this->_cacheTypesList->invalidate($this->_relatedCacheTypes);
        }
        return $this;
    }
    public function afterSave()
    {
        if ($this->isObjectNew()) {
            $this->getMatchingProductIds();
            if (!empty($this->productIds) && is_array($this->productIds)) {
                $objManager = \Magento\Framework\App\ObjectManager::getInstance();
                $productMetadata = $objManager->get('Magento\Framework\App\ProductMetadataInterface');
                if ($productMetadata->getVersion() > '2.2.0') {
                    $this->_getResource()->addCommitCallback([$this, 'rei ndex']);
                } else {
                    $this->_ruleProductProcessor->reindexList($this->productIds);
                }
            }
        } else {
            $this->_ruleProductProcessor->getIndexer()->invalidate();
        }
        return parent::afterSave();
    }
    public function afterDelete()
    {
        $this->_ruleProductProcessor->getIndexer()->invalidate();
        return parent::afterDelete();
    }

    public function isRuleBehaviorChanged()
    {
        if (!$this->isObjectNew()) {
            $arrayDiff = $this->dataDiff($this->getOrigData(), $this->getStoredData());
            unset($arrayDiff['name']);
            unset($arrayDiff['description']);
            if (empty($arrayDiff)) {
                return false;
            }
        }
        return true;
    }

    protected function dataDiff($array1, $array2)
    {
        $result = [];
        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    if ($value != $array2[$key]) {
                        $result[$key] = true;
                    }
                } else {
                    if ($value != $array2[$key]) {
                        $result[$key] = true;
                    }
                }
            } else {
                $result[$key] = true;
            }
        }
        return $result;
    }
}
