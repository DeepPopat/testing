<?php

namespace Emipro\Smartproductselector\Block\Adminhtml\Grid\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{
    protected $_systemStore;

    protected $_groupRepository;

    protected $_searchCriteriaBuilder;

    protected $_objectConverter;

    protected $_storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\ObjectManagerInterface $objectMan,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter = $objectConverter;
        $this->objectMan = $objectMan;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Rule Information');
    }

    public function getTabTitle()
    {
        return __('Rule Information');
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

        $fieldset = $form->addFieldset('base_fieldset', []);

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $fieldset->addField(
            'rule_name',
            'text',
            ['name' => 'rule_name', 'label' => __('Rule Name'), 'title' => __('Rule Name'), 'required' => true]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'style' => 'height: 100px;',
            ]
        );
        $status = $this->objectMan->create('Emipro\Smartproductselector\Model\Status');

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => $status->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'alternative_type',
            'select',
            [
                'label' => __('Alternative Type'),
                'title' => __('Alternative Type'),
                'name' => 'alternative_type',
                'required' => true,
                'options' => $status->toAlternativeOptionArray(),
            ]
        );

        $fieldset->addField(
            'rule_priority',
            'text',
            [
                'name' => 'rule_priority',
                'label' => __('Priority'),
                'title' => __('Priority'),
            ]
        );

        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
