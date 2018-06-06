<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Block used for auction box in product page.
 */

namespace Emipro\Auction\Block;

use Emipro\Auction\Model\BidFactory;
use Emipro\Auction\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager as ObjManager;
use Magento\Framework\Registry;

class Auction extends \Magento\Framework\View\Element\Template
{

    protected $_auctionFactory;
    protected $_catalogProduct;
    protected $_customerSession;
    protected $datetime;
    protected $_stdTimezone;
    public $_scopeConfig;
    protected $_customerFactory;
    protected $_customers;
    protected $_currency;
    // protected $_scopeConfig;
    /**
     * [__construct description]
     * @param \Magento\Framework\View\Element\Template\Context   $context         [Get context object]
     * @param \Emipro\Auction\Model\AuctionFactory               $auctionFactory  [Get auction collection]
     * @param \Magento\Catalog\Block\Product\ListProduct         $catalogProduct  [Get product collection]
     * @param \Emipro\Auction\Model\BidFactory                   $BidFactory      [Get bid collection]
     * @param \Magento\Customer\Model\Session                    $customerSession [Get customer collection]
     * @param \Magento\Framework\Stdlib\DateTime\DateTime        $datetime        [Get Date and Time]
     * @param CustomerFactory                                    $customerFactory [Get login customer collection]
     * @param Registry                                           $registry        [Store Data]
     * @param \Magento\Customer\Model\Customer                   $customers       [Get auction customer]
     * @param array                                              $data            [Create array data]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Emipro\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Catalog\Block\Product\ListProduct $catalogProduct,
        \Emipro\Auction\Model\BidFactory $BidFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Stdlib\DateTime\Timezone $stdTimezone,
        CustomerFactory $customerFactory,
        Registry $registry,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        $this->_auctionFactory = $auctionFactory;
        $this->_catalogProduct = $catalogProduct;
        $this->bidFactory = $BidFactory;
        $this->_customerSession = $customerSession;
        $this->datetime = $datetime;
        $this->_stdTimezone = $stdTimezone;
        $this->_customerFactory = $customerFactory;
        $this->registry = $registry;
        $this->_customers = $customers;
        $this->_currency = $currencyFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get Current Time Zone Value
     */
    public function getCurrentTimeZone()
    {
        return $this->_scopeConfig->getValue('general/locale/timezone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Get Loaded product collection
     */
    public function getLoadedProductCollection()
    {

        return $this->_catalogProduct->getLoadedProductCollection();
    }
    /**
     * Get Auction collection
     */
    public function getAuctionData()
    {
        return $this->_auctionFactory->create();
    }
    /**
     * Get Bid collection
     */
    public function getCurrentBid()
    {

        return $this->bidFactory->create()->getCollection();
    }
    /**
     * Get customer collection
     */
    public function getCustomer()
    {
        $objectManager = ObjManager::getInstance();
        $cust_session = $objectManager->create('Magento\Customer\Model\Session');
        return $cust_session;
    }
    /**
     * Get Current Time zone
     */
    public function getTimeZone()
    {
        return $this->_stdTimezone->date()->format('Y-m-d H:i:s');
    }
    /**
     * Get Current product collection
     */
    public function getCurrentProductCollection()
    {
        return $this->registry->registry('product');
    }
    /**
     * Get Currency symbol
     */
    public function getCurrencySymbol()
    {
        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->_currency->create()->load($currencyCode);
        $currencySymbol = $currency->getCurrencySymbol();
        return $currencySymbol;
    }
    /**
     * Get current currency code
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }
    /**
     * Get current currency rate
     */
    public function getCurrentCurrencyRate()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyRate();
    }
    /**
     * Get Current Url
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }
    /**
     * Get customer watchlist data
     */
    public function getCustomerWatchlistData()
    {
        return $this->_customerFactory->create()->getCollection();
    }
    /**
     * Get winner id
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
    /**
     * Get winner name
     */
    public function getWinner($id)
    {

        $winnerModel = $this->_auctionFactory->create()->getCollection()
            ->addFieldToFilter('auction_id', $id)->addFieldToFilter('status', 2);
        $winnerData = '';
        foreach ($winnerModel as $key => $value) {
            $winnerData = $value;
        }
        if ($winnerData) {
            $winnnerid = $winnerData['winner_customer_id'];
            $winnercustomer = $this->_customers->load($winnnerid);
            $winnerfirstname = $winnercustomer->getFirstname();
            $winnerlastname = $winnercustomer->getLastname();
            return $winnerfirstname . " " . $winnerlastname;
        }
    }
}
