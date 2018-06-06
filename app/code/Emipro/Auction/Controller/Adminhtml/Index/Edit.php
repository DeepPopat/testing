<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Used for Edit auction Data in admin
 */
namespace Emipro\Auction\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Emipro_Auction::manage');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Auction'));
        return $resultPage;
    }
}
