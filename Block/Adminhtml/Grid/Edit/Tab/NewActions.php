<?php
namespace Emipro\Smartproductselector\Block\Adminhtml\Grid\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class NewActions extends Generic implements TabInterface
{
    protected $_rendererFieldset;

    private $ruleFactory;

    protected $_conditions;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Framework\ObjectManagerInterface $objectMan,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        $this->objectMan = $objectMan;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Configuration For Product Filter');
    }

    public function getTabTitle()
    {
        return __('Configuration For Product Filter');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('emipro_smartproductselector_rule');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset(
            'newaction_fieldset',
            ['legend' => __('    Configuration')]
        );

        $AttributeSet = $this->objectMan->create('\Magento\Catalog\Model\Product\AttributeSet\Options');

        $fieldset->addField(
            'attribute_set_id',
            'select',
            [
                'label' => __('Attribute Set'),
                'name' => 'attribute_set_id',
                'values' => $AttributeSet->toOptionArray(),
            ]
        );

        $category = $fieldset->addField(
            'cat_select',
            'select',
            [
                'label' => __('Select Category'),
                'name' => 'cat_select',
                'index' => 'cat_select',
                'values' => [
                    '0' => __('From all Category'),
                    '1' => __('Select Specific Category'),
                    '2' => __('Current Category'),
                    '3' => __('Parent Category'),
                ],
            ]
        );

        $helper = $this->objectMan->create('Emipro\Smartproductselector\Helper\Data');

        $specificCategory = $fieldset->addField(
            'specific_category_id',
            'select',
            [
                'label' => __('Specific Category'),
                'name' => 'specific_category_id',
                'values' => $helper->toCategoriesArray(),
                'required' => true,
                'note' => 'Select category which you want to set as Alternative product',
            ]
        );

        $setPriceRange = $fieldset->addField(
            'price_range',
            'select',
            [
                'label' => __('Set price Range'),
                'name' => 'price_range',
                'note' => 'Select Yes to set price range.',
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No'),
                ],
            ]
        );

        $belowPriceFiled = $fieldset->addField(
            'below_price',
            'text',
            [
                'name' => 'below_price',
                'label' => __('Below'),
                'required' => true,
            ]
        );

        $abovePriceFiled = $fieldset->addField(
            'above_price',
            'text',
            [
                'name' => 'above_price',
                'label' => __('Above'),
                'required' => true,
            ]
        );

        $fieldset = $form->addFieldset(
            'oldaction_fieldset',
            ['legend' => __('Configuration For Frontend')]
        );

        $fieldset->addField(
            'no_product',
            'text',
            [
                'name' => 'no_product',
                'index' => 'no_product',
                'label' => __('Number of products'),
                'title' => __('Number of products'),
                'required' => true,
            ]
        );

        $ShortOrderByField = $fieldset->addField(
            'short_order_by',
            'select',
            [
                'label' => __('Product order by'),
                'name' => 'short_order_by',
                'index' => 'short_order_by',
                'values' => [
                    '1' => 'Random',
                    '2' => 'Attribute',
                ],
            ]
        );

        $attributes = $this->objectMan->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')->addVisibleFilter();

        $prodAttr = [];
        foreach ($attributes as $attribute) {
            $prodAttr[] = [
                'label' => $attribute->getData('frontend_label'),
                'value' => $attribute->getData('attribute_code'),
            ];
        }

        $ProductAttributeField = $fieldset->addField(
            'product_attribute',
            'select',
            [
                'label' => __('Order by attribute'),
                'name' => 'product_attribute',
                'index' => 'product_attribute',
                'values' => $prodAttr,
            ]
        );

        $ProductShortField = $fieldset->addField(
            'product_shorting',
            'select',
            [
                'label' => __('Sort order'),
                'name' => 'product_shorting',
                'index' => 'product_shorting',
                'values' => [
                    'ASC' => 'Ascending',
                    'DESC' => 'Descending',
                ],
            ]
        );

        $fieldset->addField(
            'out_stock',
            'select',
            [
                'label' => __('Show "Out of stock" Products '),
                'name' => 'out_stock',
                'index' => 'out_stock',
                'values' => [
                    '0' => 'No',
                    '1' => 'Yes',
                ],
            ]
        );

        $form->setValues($model->getData());
        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        $this->setChild('form_after', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                ->addFieldMap($setPriceRange->getHtmlId(), $setPriceRange->getName())
                ->addFieldMap($belowPriceFiled->getHtmlId(), $belowPriceFiled->getName())
                ->addFieldMap($abovePriceFiled->getHtmlId(), $abovePriceFiled->getName())
                ->addFieldMap($ShortOrderByField->getHtmlId(), $ShortOrderByField->getName())
                ->addFieldMap($ProductAttributeField->getHtmlId(), $ProductAttributeField->getName())
                ->addFieldMap($category->getHtmlId(), $category->getName())
                ->addFieldMap($specificCategory->getHtmlId(), $specificCategory->getName())
                ->addFieldMap($ProductShortField->getHtmlId(), $ProductShortField->getName())

                ->addFieldDependence($abovePriceFiled->getName(), $setPriceRange->getName(), 1)
                ->addFieldDependence($belowPriceFiled->getName(), $setPriceRange->getName(), 1)
                ->addFieldDependence($ProductAttributeField->getName(), $ShortOrderByField->getName(), 2)
                ->addFieldDependence($ProductShortField->getName(), $ShortOrderByField->getName(), 2)
                ->addFieldDependence($specificCategory->getName(), $category->getName(), 1));

        return parent::_prepareForm();
    }
}
