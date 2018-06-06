<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Used for Bid detail page.
 */
namespace Emipro\Auction\Controller\Index;

class Biddetail extends \Magento\Framework\App\Action\Action
{

    protected $_auctionFactory;
    protected $resultPageFactory;
    protected $customerSession;
    /**
     * [__construct description]
     * @param \Magento\Framework\App\Action\Context      $context           [Context Object]
     * @param \Emipro\Auction\Model\AuctionFactory       $auctionFactory    [Get Auction Collection]
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory [Get Page Layout]
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Emipro\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->_auctionFactory = $auctionFactory;
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
                $navigationBlock->setActive('auction/index/mybids');
            }
            $auctionid = $this->getRequest()->getParam('auction_id');
            $auctiontitle = $this->_auctionFactory->create()->load($auctionid)->getTitle();
            if ($auctiontitle) {
                $resultPage->getConfig()->getTitle()->set(__($auctiontitle . ' - Bid Details'));
            }
            $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
            return $resultPage;
        } else {
            $url = $this->_url->getUrl('auction/index/mybids');
            $resultRedirect = $this->resultRedirectFactory->create();
            $customerBeforeAuthUrl = $this->_url->getUrl('customer/account/login', ['referer' => base64_encode($url)]);
            return $resultRedirect->setPath($customerBeforeAuthUrl);
        }
    }
}
