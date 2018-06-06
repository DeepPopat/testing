<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Used for Watchlist tab in customer account section
 */
namespace Emipro\Auction\Controller\Index;

class Watchlist extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $customerSession;
    /**
     * [__construct description]
     * @param \Magento\Framework\App\Action\Context      $context           [Context Object]
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory [Get Page Layout]
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Execute action
     */
    public function execute()
    {

        if ($this->customerSession->isLoggedIn()) {
            $resultPage = $this->resultPageFactory->create();
            $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('auction/index/watchlist');
            }
            $resultPage->getConfig()->getTitle()->set(__('My Watch List'));
            $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
            return $resultPage;
        } else {
            $url = $this->_url->getUrl('auction/index/watchlist');
            $resultRedirect = $this->resultRedirectFactory->create();
            $customerBeforeAuthUrl = $this->_url->getUrl('customer/account/login', ['referer' => base64_encode($url)]);
            return $resultRedirect->setPath($customerBeforeAuthUrl);
        }
    }
}
