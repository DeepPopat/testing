<?php

/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Auction\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    /**
     * [__construct description]
     * @param \Magento\Backend\App\Action\Context        $context           [context object]
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory [result page layout]
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Emipro_Auction::manage');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Auction'));
        return $resultPage;
    }
}
