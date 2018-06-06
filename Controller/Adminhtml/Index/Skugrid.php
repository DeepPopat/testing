<?php
namespace Emipro\Smartproductselector\Controller\Adminhtml\Index;

class Skugrid extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget
{
    protected $resultPageFactory;

    public function execute()
    {
        $request = $this->getRequest();
        $block = $this->_view->getLayout()->createBlock(
            'Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser\Sku',
            'promo_widget_chooser_sku',
            ['data' => ['js_form_object' => $request->getParam('form')]]
        );
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
}
