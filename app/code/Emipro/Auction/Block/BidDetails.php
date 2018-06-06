<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Block used for Bid details of current customer in customer section.
 */
?>
<?php

namespace Emipro\Auction\Block;

use Emipro\Auction\Model\BidFactory;
use Emipro\Auction\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager as ObjManager;
use Magento\Theme\Block\Html\Pager;

class BidDetails extends \Magento\Framework\View\Element\Template
{
    public $_auctionFactory;
    protected $_customerSession;
    protected $_customerFactory;
    protected $datetime;
    protected $_currency;
    /**
     * [__construct description]
     * @param \Magento\Framework\View\Element\Template\Context $context         [Context object]
     * @param \Emipro\Auction\Model\AuctionFactory             $auctionFactory  [Get Auction Collection]
     * @param \Emipro\Auction\Model\BidFactory                 $BidFactory      [Get Bid Collection]
     * @param \Magento\Customer\Model\Session                  $customerSession [Get Login Customer Collection]
     * @param CustomerFactory                                  $customerFactory [Get Customer Collection]
     * @param array                                            $data            [Create array data]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Emipro\Auction\Model\AuctionFactory $auctionFactory,
        \Emipro\Auction\Model\BidFactory $BidFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->_auctionFactory = $auctionFactory;
        $this->bidFactory = $BidFactory;
        $this->_customerSession = $customerSession;
        $this->_currency = $currencyFactory;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }
    public function displayBid()
    {
        //get values of current page
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;
        $auctionid = $this->getRequest()->getParam('auction_id');
        $customerid = $this->getCustomer()->getCustomerId();
        $bidCollection = $this->bidFactory->create()->getCollection();
        $bidCollection->addFieldToFilter('auction_id', $auctionid)->addFieldToFilter('customer_id', $customerid);
        $bidCollection->setPageSize($pageSize);
        $bidCollection->setCurPage($page);
        $bidCollection->setOrder('created_time', 'DESC');
        return $bidCollection;
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->displayBid()) {
            $pager = $this->getLayout()->createBlock(Pager::class, 'emipro.auction.record.pager')->setAvailableLimit([10 => 10, 15 => 15, 20 => 20, 25 => 25])->setShowPerPage(true)->setCollection(
                $this->displayBid()
            );
            $this->setChild('pager', $pager);
            $this->displayBid()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    /**
     * Get Customer collection
     */
    public function getCustomer()
    {
        $objectManager = ObjManager::getInstance();
        $cust_session = $objectManager->create('Magento\Customer\Model\Session');
        return $cust_session;
    }
    /**
     * Get Bid collection
     */
    public function getBidmodel()
    {

        return $this->bidFactory->create()->getCollection();
    }
    /**
     * Get Currency Symbol
     */
    public function getCurrencySymbol()
    {
        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->_currency->create()->load($currencyCode);
        $currencySymbol = $currency->getCurrencySymbol();
        return $currencySymbol;
    }
}
