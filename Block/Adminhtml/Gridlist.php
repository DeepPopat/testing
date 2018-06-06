<?php
namespace Emipro\Smartproductselector\Block\Adminhtml;

use Magento\Backend\Block\Widget\Container;

class Gridlist extends Container
{
    protected $_template = 'grid/view.phtml';

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $this->buttonList->add(
            'add_new',
            [
                'label' => __('Add New'),
                'class' => 'primary',
                'button_class' => '',
                'onclick' => "setLocation('" . $this->_getCreateUrl() . "')",
            ]
        );

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Emipro\Smartproductselector\Block\Adminhtml\Grid\Grid', 'grid.view.grid')
        );
        return parent::_prepareLayout();
    }

    protected function _getAddButtonOptions()
    {
        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')",
        ];
        return $splitButtonOptions;
    }

    protected function _getCreateUrl()
    {
        return $this->getUrl('smartproductselector/*/new');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
