<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Smartproductselector\Model\Template\Rule\Condition;

use Magento\Eav\Model\ResourceModel\Entity;

class Validate extends \Magento\Rule\Model\Condition\AbstractCondition
{
    protected $stockItemFactory;
    protected $ruleFactory;
    protected $entityAttributeSetCollectionFactory;
    protected $productFactory;
    protected $productTypeConfigurableFactory;
    protected $config;
    protected $urlManager;
    protected $backendUrlManager;
    protected $storeManager;
    protected $localeFormat;
    protected $assetRepo;
    protected $context;
    protected $registry;
    public function __construct(
        \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory,
        \Magento\CatalogInventory\Model\StockState $stockState,
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        Entity\Attribute\Set\CollectionFactory $entityAttributeSetCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $productTypeConfigurableFactory,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Backend\Model\Url $backendUrlManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObject $DataObject,
        array $data = []
    ) {
        $this->DataObject = $DataObject;
        $this->stockItemFactory = $stockItemFactory;
        $this->stockState = $stockState;
        $this->ruleFactory = $ruleFactory;
        $this->entityAttributeSetCollectionFactory = $entityAttributeSetCollectionFactory;
        $this->productFactory = $productFactory;
        $this->productTypeConfigurableFactory = $productTypeConfigurableFactory;
        $this->config = $config;
        $this->urlManager = $urlManager;
        $this->backendUrlManager = $backendUrlManager;
        $this->storeManager = $storeManager;
        $this->localeFormat = $localeFormat;
        $this->assetRepo = $context->getAssetRepository();
        $this->context = $context;
        $this->registry = $registry;
        $this->localeDate = $context->getLocaleDate();
        parent::__construct($context, $data);
    }

    protected $entityAttributeValues = null;
    protected $isUsedForRuleProperty = 'is_used_for_promo_rules';
    public function getAttributeObject()
    {
        try {
            $obj = $this->config->getAttribute('catalog_product', $this->getAttribute());
        } catch (\Exception $e) {
            $obj = $this->DataObject;
            $obj->setEntity($this->productFactory->create())->setFrontendInput('text');
        }
        return $obj;
    }
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes['attribute_set_id'] = __('Attribute Set');
        $attributes['category_ids'] = __('Category');
        $attributes['created_at'] = __('Created At (days ago)');
        $attributes['updated_at'] = __('Updated At (days ago)');
        $attributes['qty'] = __('Quantity');
        $attributes['price_diff'] = __('Price - Final Price');
        $attributes['percent_discount'] = __('Percent Discount');
    }
    public function loadAttributeOptions()
    {
        $productAttributes = $this->productFactory->create()
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = [];
        foreach ($productAttributes as $attri) {
            if (!$attri->isAllowedForRuleCondition() || !$attri->getDataUsingMethod($this->isUsedForRuleProperty)) {
                continue;
            }
            $attributes[$attri->getAttributeCode()] = $attri->getFrontendLabel();
        }
        $this->_addSpecialAttributes($attributes);
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }
    protected function _prepareValueOptions()
    {
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }
        $selectOptions = null;
        if ($this->getAttribute() === 'attribute_set_id') {
            $entityTypeId = $this->config
                ->getEntityType('catalog_product')->getId();
            $selectOptions = $this->entityAttributeSetCollectionFactory->create()
                ->setEntityTypeFilter($entityTypeId)
                ->load()
                ->toOptionArray();
        } elseif (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            }
        }
        if ($selectOptions !== null) {
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = [];
                foreach ($selectOptions as $o) {
                    if (is_array($o['value'])) {
                        continue;
                    }
                    $hashedOptions[$o['value']] = $o['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }
        return $this;
    }
    public function getValueOption($option = null)
    {
        $this->_prepareValueOptions();
        return $this->getData('value_option' . ($option !== null ? '/' . $option : ''));
    }
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();
        return $this->getData('value_select_options');
    }
    public function getValueAfterElementHtml()
    {
        $html = '';
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $image = $this->assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }
        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger">
                    <img src="' . $image . '" alt="" class="v-middle rule-chooser-trigger" title="' . __('Open Chooser') . '" />
                    </a>';
        }
        return $html;
    }
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }
    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();
        if (!in_array($attribute, ['category_ids', 'qty', 'price_diff', 'percent_discount'])) {
            if ($this->getAttributeObject()->isScopeGlobal()) {
                $attributes = $this->getRule()->getCollectedAttributes();
                $attributes[$attribute] = true;
                $this->getRule()->setCollectedAttributes($attributes);
                $productCollection->addAttributeToSelect($attribute, 'left');
            } else {
                $this->entityAttributeValues = $productCollection->getAllAttributeValues($attribute);
            }
        } elseif (($attribute == 'price_diff') || ($attribute == 'percent_discount')) {
            $productCollection->addAttributeToSelect('price', 'left');
            $productCollection->addAttributeToSelect('special_price', 'left');
            $productCollection->addAttributeToSelect('special_from_date', 'left');
            $productCollection->addAttributeToSelect('special_to_date', 'left');
            $productCollection->addAttributeToSelect('type_id', 'left');
        }
        return $this;
    }
    public function getInputType()
    {
        if ($this->getAttribute() === 'attribute_set_id') {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';
            case 'multiselect':
                return 'multiselect';
            case 'boolean':
                return 'boolean';
            default:
                return 'string';
        }
    }
    public function getValueElementType()
    {
        if ($this->getAttribute() === 'attribute_set_id') {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';
            case 'multiselect':
                return 'multiselect';
            default:
                return 'text';
        }
    }
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $url = 'catalog_rule/promo_widget/chooser'
                . '/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                } else {
                    $url .= '/form/rule_conditions_fieldset';
                }
                break;
        }

        return $url !== false ? $this->backendUrlManager->getUrl($url) : '';
    }
    public function getExplicitApply()
    {
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                return true;
        }
        return false;
    }
    public function loadArray($arr)
    {
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $attribute = $this->getAttributeObject();
        if ($attribute && $attribute->getBackendType() == 'decimal') {
            if (isset($arr['value'])) {
                if (!empty($arr['operator']) && in_array($arr['operator'], ['!()', '()']) &&
                    false !== strpos($arr['value'], ',')) {
                    $tmp = [];
                    foreach (explode(',', $arr['value']) as $value) {
                        $tmp[] = $this->localeFormat->getNumber($value);
                    }
                    $arr['value'] = implode(',', $tmp);
                } else {
                    $arr['value'] = $this->localeFormat->getNumber($arr['value']);
                }
            } else {
                $arr['value'] = false;
            }
            $arr['is_value_parsed'] = isset($arr['is_value_parsed'])
            ? $this->localeFormat->getNumber($arr['is_value_parsed']) : false;
        }
        return parent::loadArray($arr);
    }
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $attrCode = $this->getAttribute();
        switch ($attrCode) {
            case 'category_ids':
                return $this->validateCategory($object);
            case 'attribute_set_id':
                $attrId = $object->getAttributeSetId();
                return $this->validateAttribute($attrId);
            case 'qty':
                return $this->validateQty($object);
            default:
                return $this->validateValue($object, $attrCode);
        }
    }
    protected function validateCategory($object)
    {
        if ($object instanceof \Magento\Catalog\Model\Category) {
            $categoryIds = [$object->getId()];
        } else {
            $categoryIds = $object->getAvailableInCategories();
        }
        $op = $this->getOperatorForValidate();
        if ((($op == '==') || ($op == '!=')) && is_array($categoryIds)) {
            $value = $this->getValueParsed();
            $value = preg_split('#\s*[,;]\s*#', $value, null, PREG_SPLIT_NO_EMPTY);
            $findElemInArray = array_intersect($categoryIds, $value);
            if (!empty($findElemInArray)) {
                if ($op == '==') {
                    $result = true;
                }
                if ($op == '!=') {
                    $result = false;
                }
            } else {
                if ($op == '==') {
                    $result = false;
                }
                if ($op == '!=') {
                    $result = true;
                }
            }
            return $result;
        }
        return $this->validateAttribute($categoryIds);
    }
    protected function validateValue($object, $attrCode)
    {
        if (!isset($this->entityAttributeValues[$object->getId()])) {
            $attr = $object->getResource()->getAttribute($attrCode);
            if ($attr && $attr->getBackendType() == 'datetime' && !is_int($this->getValue())) {
                $this->setValue(strtotime($this->getValue()));
                $value = strtotime($object->getData($attrCode));
                return $this->validateAttribute($value);
            }
            if ($attr && $attr->getFrontendInput() == 'multiselect') {
                $value = $object->getData($attrCode);
                $value = strlen($value) ? explode(',', $value) : [];
                return $this->validateAttribute($value);
            }
            return parent::validate($object);
        } else {
            $result = false; /** any valid value will set it to TRUE */
            $oldAttrValue = $object->hasData($attrCode) ? $object->getData($attrCode) : null;
            /** remember old attribute state */
            foreach ($this->entityAttributeValues[$object->getId()] as $value) {
                $attr = $object->getResource()->getAttribute($attrCode);
                if ($attr && $attr->getBackendType() == 'datetime') {
                    $value = strtotime($value);
                } elseif ($attr && $attr->getFrontendInput() == 'multiselect') {
                    $value = strlen($value) ? explode(',', $value) : [];
                }
                $object->setData($attrCode, $value);
                $result |= parent::validate($object);
                if ($result) {
                    break;
                }
            }
            if ($oldAttrValue === null) {
                $object->unsetData($attrCode);
            } else {
                $object->setData($attrCode, $oldAttrValue);
            }
            return (bool) $result;
        }
    }
    protected function validateQty($object)
    {
        $stockItem = $this->stockItemFactory->create()->setProduct($object);
        if ($object->getTypeId() == 'configurable' && $stockItem->getIsInStock()) {
            $requireChildIds = $this->productTypeConfigurableFactory->create()->getChildrenIds($object->getId(), true);
            $childrenIds = [];
            foreach ($requireChildIds as $groupedChildrenIds) {
                $childrenIds = array_merge($childrenIds, $groupedChildrenIds);
            }
            $sumQty = 0;
            foreach ($childrenIds as $childId) {
                $childQty = $this->stockState->getStockQty($childId);
                $sumQty += $childQty;
            }
            return $this->validateAttribute($sumQty);
        } elseif ($object->getTypeId() == 'configurable') {
            return false;
        }
        $qty = $this->stockState->getStockQty($object->getId());
        return $this->validateAttribute($qty);
    }
}
