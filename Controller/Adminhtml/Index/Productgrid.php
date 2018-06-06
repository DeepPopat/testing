<?php
namespace Emipro\Smartproductselector\Controller\Adminhtml\Index;

class Productgrid extends \Magento\Catalog\Controller\Adminhtml\Product
{
    protected $resultLayoutFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    public function execute()
    {
        $this->productBuilder->build($this->getRequest());
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('emipro.smartproductselector.edit.tab.productgrid')
            ->setProducts($this->getRequest()->getPost('products', null));
        return $resultLayout;
    }
}
