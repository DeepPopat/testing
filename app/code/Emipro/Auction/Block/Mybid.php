<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Block used for My Bid tab in customer section.
 */
?>
<?php

namespace Emipro\Auction\Block;

use Emipro\Auction\Model\BidFactory;
use Emipro\Auction\Model\CustomerFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager as ObjManager;
use Magento\Theme\Block\Html\Pager;

class Mybid extends \Magento\Framework\View\Element\Template
{
    public $_auctionFactory;
    protected $_customerSession;
    protected $_customerFactory;
    protected $datetime;
    protected $_stdTimezone;
    protected $_currency;
    /**
     * [__construct description]
     * @param \Magento\Framework\View\Element\Template\Context $context         [Context object]
     * @param \Emipro\Auction\Model\AuctionFactory             $auctionFactory  [Get Auction Collection]
     * @param \Emipro\Auction\Model\BidFactory                 $BidFactory      [Get Bid Collection]
     * @param \Magento\Customer\Model\Session                  $customerSession [Get Login Customer Collection]
     * @param ProductFactory                                   $productFactory  [Get Product Collection]
     * @param CustomerFactory                                  $customerFactory [Get Customer Collection]
     * @param \Magento\Framework\Stdlib\DateTime\DateTime      $datetime        [Get Current Date and Time]
     * @param array                                            $data            [Create array data]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Emipro\Auction\Model\AuctionFactory $auctionFactory,
        \Emipro\Auction\Model\BidFactory $BidFactory,
        \Magento\Customer\Model\Session $customerSession,
        ProductFactory $productFactory,
        CustomerFactory $customerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Stdlib\DateTime\Timezone $stdTimezone,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        $this->_auctionFactory = $auctionFactory;
        $this->bidFactory = $BidFactory;
        $this->_customerSession = $customerSession;
        $this->_productFactory = $productFactory;
        $this->_customerFactory = $customerFactory;
        $this->datetime = $datetime;
        $this->_stdTimezone = $stdTimezone;
        $this->_currency = $currencyFactory;
        parent::__construct($context, $data);
    }
    public function displayCollection()
    {
        //get values of current page
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;

        $auctionCollection = $this->_auctionFactory->create()->getCollection();
        $auctionCollection->setPageSize($pageSize);
        $auctionCollection->setCurPage($page);
        $auctionCollection->setOrder('auction_id', 'DESC');
        return $auctionCollection;
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->displayCollection()) {
            $pager = $this->getLayout()->createBlock(Pager::class, 'emipro.auction.record.pager')
                ->setAvailableLimit([10 => 10, 15 => 15, 20 => 20, 25 => 25])
                ->setShowPerPage(true)
                ->setCollection($this->displayCollection());
            $this->setChild('pager', $pager);
            $this->displayCollection()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    /**
     * Get Auction collection
     */
    public function getAuctionData()
    {
        return $this->_auctionFactory->create();
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
     * Get product collection
     */
    public function getProduct($id)
    {
        return $this->_productFactory->create()->load($id);
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
    /**
     * Get Customer Watchlist Collection
     */
    public function getCustomerWatchlistData()
    {
        return $this->_customerFactory->create()->getCollection();
    }
    /**
     * Get Current Time Zone
     */
    public function getTimeZone()
    {
        return $this->_stdTimezone->date()->format('Y-m-d H:i:s');
    }
    /**
     * Get Winner Id
     */
    public function getWinnerId($id)
    {

        $winnerColl = $this->_auctionFactory->create()->getCollection()
            ->addFieldToFilter('auction_id', $id)->addFieldToFilter('status', 2);
        $winnerData = '';
        foreach ($winnerColl as $key => $value) {
            $winnerData = $value;
        }
        if ($winnerData) {
            $winnnerid = $winnerData['winner_customer_id'];
            return $winnnerid;
        }
    }
}
