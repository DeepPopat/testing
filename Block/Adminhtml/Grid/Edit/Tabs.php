<?php
namespace Emipro\Smartproductselector\Block\Adminhtml\Grid\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('grid_record');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Smart Rule'));
    }
    protected function _prepareLayout()
    {
        $this->addTab(
            'smartproductselector_grid',
            [
                'label' => __('Rule Information'),
                'content' => $this->getLayout()->createBlock('Emipro\Smartproductselector\Block\Adminhtml\Grid\Edit\Tab\Main')->toHtml(),
            ]
        );

        $this->addTab(
            'smartproductselector_condition',
            [
                'label' => __('Conditions'),
                'content' => $this->getLayout()->createBlock('Emipro\Smartproductselector\Block\Adminhtml\Grid\Edit\Tab\Conditions')->toHtml(),
            ]
        );

        $this->addTab(
            'smartproductselector_actions',
            [
                'label' => __('Configuration For Product Filter'),
                'content' => $this->getLayout()->createBlock('Emipro\Smartproductselector\Block\Adminhtml\Grid\Edit\Tab\Newcondition')->toHtml() . $this->getLayout()->createBlock('Emipro\Smartproductselector\Block\Adminhtml\Grid\Edit\Tab\Actions')->toHtml() . $this->getLayout()->createBlock('Emipro\Smartproductselector\Block\Adminhtml\Grid\Edit\Tab\NewActions')->toHtml(),
            ]
        );
    }
}
