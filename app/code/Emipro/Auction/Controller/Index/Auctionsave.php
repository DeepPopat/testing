<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Used for save Auction bid data
 */
namespace Emipro\Auction\Controller\Index;

use Emipro\Auction\Lib\Pusher\Pusher;
use Emipro\Auction\Model\AuctionFactory;
use Emipro\Auction\Model\BidFactory;
use Emipro\Auction\Model\CustomerFactory;
use \Magento\Framework\App\ResourceConnection;

class Auctionsave extends \Magento\Framework\App\Action\Action
{
    protected $datetime;
    protected $customerSession;
    public $_scopeConfig;
    protected $_helper;
    protected $_resource;
    protected $_currency;
    protected $_stdTimezone;

    /**
     * [__construct description]
     * @param \Magento\Framework\App\Action\Context              $context         [Context Object]
     * @param \Emipro\Auction\Model\BidFactory                   $BidFactory      [Get Bid Collection]
     * @param \Emipro\Auction\Model\AuctionFactory               $AuctionFactory  [Get Auction Collection]
     * @param \Emipro\Auction\Model\CustomerFactory              $CustomerFactory [Get Customer Collection]
     * @param \Magento\Customer\Model\Session                    $customerSession [Get Login Customer Collection]
     * @param \Magento\Framework\Stdlib\DateTime\DateTime        $datetime        [Get Time Zone]
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig     [Get Admin panel data]
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager    [Get Store Collection]
     * @param \Magento\Customer\Model\CustomerFactory            $customerdata    [Get Core Customer Collection]
     * @param ResourceConnection                                 $resource        [Get Database Connection]
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Emipro\Auction\Model\BidFactory $BidFactory,
        \Emipro\Auction\Model\AuctionFactory $AuctionFactory,
        \Emipro\Auction\Model\CustomerFactory $CustomerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerdata,
        \Emipro\Auction\Helper\Data $helper,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone $stdTimezone,
        ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->bidFactory = $BidFactory;
        $this->auctionfactory = $AuctionFactory;
        $this->customerfactory = $CustomerFactory;
        $this->customerSession = $customerSession;
        $this->datetime = $datetime;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_customerdata = $customerdata;
        $this->_helper = $helper;
        $this->_currency = $currencyFactory;
        $this->_stdTimezone = $stdTimezone;
        $this->_resource = $resource;
    }

    public function execute()
    {
        /**
         *Get all bid form data
         */
        $bidmsg = (array) $this->getRequest()->getPost();
        $bidmsg['bid_amount'] = $this->_helper->revertPrice($bidmsg['bid_amount']);
        $lastbidCollection = $this->bidFactory->create()->getCollection()->addFieldToFilter('auction_id', $bidmsg['auction_id'])->setOrder("created_time", "DESC");
        $lastcustomerid = $this->bidFactory->create()->getCollection()->addFieldToFilter("auction_id", $bidmsg['auction_id'])->setOrder("created_time", "DESC")->setOrder('bid_amount', 'DESC')->getFirstItem()->getCustomerId();
        $auctionModel = $this->auctionfactory->create()->load($bidmsg['auction_id']);
        $auctionBidModel = $this->bidFactory->create()->getCollection()->addFieldToFilter("auction_id", $bidmsg['auction_id'])->setOrder("created_time", "DESC");
        $lastBidCustomer = $auctionBidModel->getFirstItem()->getCustomerId();
        $maxbidgap = $auctionModel->getMaxPriceGap();
        $minbidgap = $auctionModel->getMinPriceGap();
        $startprice = $auctionModel->getMinPrice();
        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->_currency->create()->load($currencyCode);
        $currencysymbol = $currency->getCurrencySymbol();
        $lastbid = $lastbidCollection->getFirstItem()->getBidAmount() ? $lastbidCollection->getFirstItem()->getBidAmount() : $auctionModel->getMinPrice();
        $bidgap = $lastbid + $maxbidgap;
        $msg = [];
        $customer_id = $bidmsg['customer_id'];
        /**
         *Check customer is login or not
         */
        if (empty($customer_id)) {
            $url = $this->_redirect->getRefererUrl();
            $msg["status"] = "error";
            $msg["msg"] = __('Please <a href="%1">login</a> / <a href="%2">register</a> before place bid.', $this->_url->getUrl('customer/account/login', ['referer' => base64_encode($url)]), $this->_url->getUrl('customer/account/create', ['referer' => base64_encode($url)]));
            $msg["url"] = $this->_url->getUrl("customer/account/login", ['referer' => base64_encode($url)]);
            $this->customerSession->setAfterAuthUrl($url);
            $this->getResponse()->setBody(json_encode($msg));
            return;
        }
        $customer = $this->customerSession->getCustomer();
        /**
         *Check customer group is allow for auction or not
         */
        if (!in_array($customer->getGroupId(), explode(",", $auctionModel->getCustomerGroupIds()))) {
            $msg["status"] = "error";
            $msg["msg"] = __("You're not allowed to place bid in this auction.");
            $this->getResponse()->setBody(json_encode($msg));
            return;
        }
        /**
         *Check perticular customer is allow for auction or not
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerRepository = $objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
        $customeratt = $customerRepository->getById($customer_id);
        $cattrValue = $customeratt->getCustomAttribute('is_allow');
        if (isset($cattrValue)) {
            if ($cattrValue->getValue() == 0) {
                $msg["status"] = "error";
                $msg["msg"] = __("You're not allowed for the auction,Please contact to our support team.");
                $this->getResponse()->setBody(json_encode($msg));
                return;
            }
        }
        /**
         *Same customer can not place bid again
         */
        if ($lastcustomerid == $customer_id) {
            $msg["status"] = "error";
            $msg["msg"] = __("You're the Highest Bidder.");
            $this->getResponse()->setBody(json_encode($msg));
            return;
        }
        /**
         *Check bid amount is greater then min bidgap or not
         */
        $gap = number_format($bidmsg['bid_amount'] - $lastbid, 4, '.', '');
        if ($gap < $minbidgap) {
            $msg["status"] = "error";
            $msg["msg"] = __("Please enter " . $currencysymbol . $this->_helper->convertPrice($lastbid + $minbidgap) . " or more.");
            $this->getResponse()->setBody(json_encode($msg));
            return;
        }
        /**
         *Check bid amount is greater then max bidgap or not
         */
        if ($maxbidgap > 0) {
            if ($maxbidgap && $bidgap < $bidmsg['bid_amount']) {
                $msg["status"] = "error";
                $msg["msg"] = __("Please enter between " . $currencysymbol . $this->_helper->convertPrice($lastbid + $minbidgap) . " - " . $currencysymbol . $this->_helper->convertPrice($bidgap));
                $this->getResponse()->setBody(json_encode($msg));
                return;
            }
        }

        /**
         *Check Auction Time period
         */
        $currentDate = time();
        $endDate = $auctionModel->getEndTime();
        $now = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
        if (new \DateTime($endDate) < new \DateTime($now)) {
            $msg["status"] = "error";
            $msg["msg"] = __("Auction finish on this product");
            $this->getResponse()->setBody(json_encode($msg));
            return;
        }
        $_timeExtended = false;
        $_timeToExtend = $auctionModel->getAutoExtendTime();
        $_timeAutoExtendTimeLeft = $auctionModel->getAutoExtendTimeLeft();
        $timeFirst = strtotime($this->_stdTimezone->date()->format('Y-m-d H:i:s'));
        $timeSecond = strtotime($endDate);
        $differenceInSeconds = $timeSecond - $timeFirst;
        /**
         *Check Extendtime condition is allow or not
         */
        if ($auctionModel->getAutoExtend() && $_timeToExtend > 0 && $_timeAutoExtendTimeLeft > 0 && ($differenceInSeconds <= $_timeAutoExtendTimeLeft)) {
            $toDate = new \DateTime($endDate);
            $intervalDate = new \DateInterval('PT' . $_timeToExtend . 'S');
            $toDate->add($intervalDate);
            $newExtendedTime = $toDate->format('Y-m-d H:i:s');
            $auctionModel->setEndTime($newExtendedTime)->save();
            $_timeExtended = true;
        }
        /**
         *Save data in database
         */
        $bidmodel = $this->bidFactory->create();
        $bidmodel->setAuctionId($bidmsg['auction_id']);
        $bidmodel->setBidAmount($bidmsg['bid_amount']);
        $bidmodel->setCustomerId($bidmsg['customer_id']);
        $bidmodel->save();
        /**
         *Change data using pusher
         */
        $pusherkey = $this->_scopeConfig->getValue('emiproauction/pusher/key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $pushersecret = $this->_scopeConfig->getValue('emiproauction/pusher/secret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $pusherappid = $this->_scopeConfig->getValue('emiproauction/pusher/app_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (empty($pusherkey) || empty($pushersecret) || empty($pusherappid)) {
            $pusher["pusher_status"] = 'no';
        }

        $pusher["auction_id"] = $bidmsg['auction_id'];
        $pusher["customer_id"] = $bidmsg['customer_id'];
        $pusher["bid_amount"] = round($bidmsg['bid_amount'], 2);
        $pusher["auction_product_sku"] = str_replace(" ", "-", $bidmsg['auction_product_sku']);
        $pusher["customer_id_for_raisebid_template"] = $lastBidCustomer;
        $pusher["min_bid_amount"] = $minbidgap;
        $pusher["bid_count"] = count($this->bidFactory->create()->getCollection()->addFieldToFilter("auction_id", $bidmsg['auction_id'])->setOrder("created_time", "DESC"));
        $pusher["currencysymbol"] = $currencysymbol;
        $pusher["time_extended"] = 0;
        if ($_timeExtended) {
            $pusher["time_extended"] = 1;
            $pusher["time_extended_for_display"] = date("jS M, Y g:i:s A", strtotime($newExtendedTime)) . " [" . $this->_scopeConfig->getValue('general/locale/timezone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) . "]";
            $pusher["auction_current_time"] = $this->datetime->date("M j, Y H:i:s");
            $pusher["auction_new_end_time"] = date_format(date_create($newExtendedTime), "M j, Y H:i:s");
            $time = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
            $pusher["auction_new_time_left"] = strtotime($newExtendedTime) - strtotime($time);
        }
        $this->pusherAction($pusher);
        $msg["status"] = "success";
        $msg = array_merge($msg, $pusher);
        $msg["msg"] = __("Your Bid is successful.");
        $this->getResponse()->setBody(json_encode($msg));
    }
    /**
     * Get pusher key,id and cluster in admin panel
     */
    public function pusherAction($data)
    {
        $pusherkey = $this->_scopeConfig->getValue('emiproauction/pusher/key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pushersecret = $this->_scopeConfig->getValue('emiproauction/pusher/secret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pusherappid = $this->_scopeConfig->getValue('emiproauction/pusher/app_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pusherchannel = $data["auction_product_sku"];
        $clusterid = $this->_scopeConfig->getValue('emiproauction/pusher/cluster', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $options = ['cluster' => $clusterid, 'encrypted' => true];
        $pusher = new Pusher($pusherkey, $pushersecret, $pusherappid, $options);
        $pusher->trigger($pusherchannel, $pusherchannel, $data);
    }
}
