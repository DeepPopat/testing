<?php
namespace Emipro\Reviewnotification\Controller\Adminhtml\Notification;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Hide extends \Magento\Backend\App\Action
{
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->_resultPageFactory = $pageFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $objManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objManager->create('Emipro\Reviewnotification\Model\Notificationlog')->getCollection();
        $log_id = $collection->getFirstItem()->getId();
        if ($log_id) {
            $objManager->create('Emipro\Reviewnotification\Model\Notificationlog')
                ->setId($log_id)
                ->setNumberofReview(0)
                ->save();
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
