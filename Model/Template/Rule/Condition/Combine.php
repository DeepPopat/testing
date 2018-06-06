<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Smartproductselector\Model\Template\Rule\Condition;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    protected $templateRuleConditionValidateFactory;
    protected $registry;
    protected $groups = [
        'category' => [
            'category_ids',
        ],
        'base' => [
            'name',
            'attribute_set_id',
            'sku',
            'url_key',
            'visibility',
            'status',
            'default_category_id',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'price',
            'special_price',
            'special_price_from_date',
            'special_price_to_date',
            'tax_class_id',
            'short_description',
            'full_description',
        ],
        'extra' => [
            'qty',
        ],
    ];

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Emipro\Smartproductselector\Model\Template\Rule\Condition\ValidateFactory $templateRuleConditionValidateFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->templateRuleConditionValidateFactory = $templateRuleConditionValidateFactory;
        $this->registry = $registry;
        $this->request = $request;
        parent::__construct($context, $data);
    }
    public function getNewChildSelectOptions()
    {
        $productCondition = $this->templateRuleConditionValidateFactory->create();
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $group = 'attributes';
            foreach ($this->groups as $key => $values) {
                if (in_array($code, $values)) {
                    $group = $key;
                }
            }
            $attributes[$group][] = [
                'value' => 'Emipro\Smartproductselector\Model\Template\Rule\Condition\Validate|' . $code,
                'label' => $label,
            ];
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            [
                'value' => 'Emipro\Smartproductselector\Model\Template\Rule\Condition\Combine',
                'label' => __('Conditions Combination'),
            ],
            [
                'label' => __('Categories and Layered navigation'),
                'value' => $attributes['category'],
            ],
        ]);
        $model = $this->registry->registry('emipro_smartproductselector_rule');
        // if (!$model) {
        //     /** use with Conditions Combination */
        //     if ($this->request->getParam('ruletype')) {
        //         //   $ruletype = preg_replace('/\D/', '', $this->request->getParam('ruletype'));
        //     }
        // }
        //if (($model && $model->getRuleType() == 0) || (isset($ruletype) && $ruletype == 0)) {
        $conditions = array_merge_recursive($conditions, [
            [
                'label' => __('Products'),
                'value' => $attributes['base'],
            ],
            [
                'label' => __('Products Attributes'),
                'value' => $attributes['attributes'],
            ],
            [
                'label' => __('Products Additional'),
                'value' => $attributes['extra'],
            ],
        ]);
        //}
        return $conditions;
    }
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
