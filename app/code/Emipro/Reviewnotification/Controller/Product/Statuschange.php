<?php
namespace Emipro\Reviewnotification\Controller\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class Statuschange extends \Magento\Framework\App\Action\Action
{
    protected $_productloader;
    protected $_messageManager;
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $pageFactory;
        $this->_productloader = $_productloader;
        $this->_storeManager = $storeManager;
    }
    public function execute()
    {
        $reviewid = base64_decode($this->getRequest()->getParam('id'));
        $status = base64_decode($this->getRequest()->getParam('status'));
        $productid = base64_decode($this->getRequest()->getParam('entity'));
        $objManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objManager->create('Magento\Review\Model\Review')->load($reviewid);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($collection->getData('review_id')) {
            $collection->setStatusId($status)->save()->aggregate();
            $product = $this->_productloader->create()->load($productid);
            if ($status == 1) {
                $this->_messageManager->addSuccess(__('Review approved successfully, you can see here.'));
            }
            if ($status == 3) {
                $this->_messageManager->addSuccess(__('Review Disapproved.'));
            }
            $resultRedirect->setUrl($product->getProductUrl());
        } else {
            $this->_messageManager->addError(__('Something Went Wrong.'));
            $resultRedirect->setUrl($this->_storeManager->getStore()->getBaseUrl());
        }

        return $resultRedirect;
    }
}
