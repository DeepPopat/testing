<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Get Auction collection emipro_auction table.
 * Set Winner id and auction complete status in database.
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Emipro\Auction\Model;

use Magento\Framework\Model\AbstractModel;

class Auction extends \Magento\Framework\Model\AbstractModel
{

    const AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY = 2;
    protected $_auctionFactory;
    protected $_autobidFactory;
    protected $_registry;
    protected $_storeViewId = null;
    protected $_storeManager;
    protected $_stdTimezone;
    protected $_checkoutHelper;
    protected $_bidFactory;
    protected $_customers;
    protected $messageManager;
    /**
     * [__construct description]
     * @param \Magento\Framework\Model\Context                       $context            [Context Object]
     * @param \Magento\Framework\Registry                            $registry           [Store data in registry]
     * @param \Emipro\Auction\Model\ResourceModel\Auction            $resource           [Get Auction Collection]
     * @param \Emipro\Auction\Model\AuctionFactory                   $auctionFactory     [Get Auction Collection]
     * @param \Magento\Framework\Stdlib\DateTime\Timezone            $_stdTimezone       [Get Time Zone]
     * @param \Magento\Store\Model\StoreManagerInterface             $storeManager       [Get Store Collection]
     * @param \Emipro\Auction\Model\BidFactory                       $bidFactory         [Get Bid Collection]
     * @param \Magento\Catalog\Model\ProductFactory                  $productFactory     [Get Product Collection]
     * @param \Magento\Framework\Mail\Template\TransportBuilder      $transportBuilder   [Send Email]
     * @param \Magento\Checkout\Helper\Data                          $checkoutHelper     [Get Helper Data]
     * @param \Magento\Framework\App\Config\ScopeConfigInterface     $scopeConfig        [Get Admin Panel Data]
     * @param \Magento\Customer\Model\Customer                       $customers          [Get Customer Collection]
     * @param \Magento\Framework\Message\ManagerInterface            $messageManager     [Send Message]
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Emipro\Auction\Model\ResourceModel\Auction $resource,
        \Emipro\Auction\Model\ResourceModel\Auction\Collection $resourceCollection,
        \Emipro\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Emipro\Auction\Model\BidFactory $bidFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection);
        $this->_bidFactory = $bidFactory;
        $this->_auctionFactory = $auctionFactory;
        $this->_registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_stdTimezone = $_stdTimezone;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_customers = $customers;
        $this->_productFactory = $productFactory;
        $this->_transport = $transportBuilder;
        $this->messageManager = $messageManager;
        if ($storeViewId = $this->_storeManager->getStore()->getId()) {
            $this->_storeViewId = $storeViewId;
        }
    }

    /**
     * Check all winner conditions and set winner id to database.
     * Send mail to winner of the auction.
     * Get email template from admin panel.
     */
    public function cronAuctionWinner()
    {
        $now = $this->_stdTimezone->date()->setTimezone(new \DateTimeZone('UTC'))->format("Y-m-d H:i:s");

        $collection = $this->_auctionFactory->create()->getCollection()->addFieldToFilter('end_time', ['lt' => $now]);

        foreach ($collection->getData() as $item) {
            $currentStore = $this->_storeManager->getStore();
            $_storeid = $currentStore->getId();
            $currencysymbol = $this->_storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $auction_id = $item['auction_id'];
            $auctionBidCollection = $this->_bidFactory->create()->getCollection()->addFieldToFilter("auction_id", $auction_id)->setOrder("bid_amount", "DESC");
            $customer_id = $auctionBidCollection->getFirstItem()->getCustomerId();
            $winner_email_template = $this->_scopeConfig->getValue('emiproauction/emailtemplate/auction_winner_email_template', $storeScope, $_storeid);
            $auctionModel = $this->loadAuction($auction_id);

            if ($auctionBidCollection->getSize()) {
                if ($auctionModel->getWinnerCustomerId() == 0 && ($auctionModel->getReservedPrice() <= $auctionBidCollection->getFirstItem()->getBidAmount())) {
                    $productModel = $this->loadProduct($auctionModel->getProductId(), $auctionBidCollection->getFirstItem()->getBidAmount());
                    $customerModel = $this->loadCustomer($customer_id);
                    $this->saveAuction($auction_id, $customer_id);
                    try {
                        if (is_numeric($winner_email_template)) {
                            $emailTemplate = $this->_transport->setTemplateIdentifier($winner_email_template);
                            if (count($emailTemplate->getData()) <= 0) {
                                $emailTemplate = $this->_transport->setTemplateIdentifier($winner_email_template);
                            }
                        } else {
                            $emailTemplate = $this->_transport->setTemplateIdentifier($winner_email_template);
                        }
                        $customer_email = $customerModel->getEmail();
                        $sender_name = $this->_scopeConfig->getValue('trans_email/ident_general/name', $storeScope, $_storeid);
                        $sender_email = $this->_scopeConfig->getValue('trans_email/ident_general/email', $storeScope, $_storeid);
                        $emailTemplateVariables = [];

                        $emailTemplateVariables['customer_name'] = $customerModel->getFirstname();
                        $emailTemplateVariables['auction_title'] = $auctionModel->getTitle();
                        $emailTemplateVariables['product_url'] = $productModel->getProductUrl();
                        $emailTemplateVariables['product_name'] = $productModel->getName();
                        $emailTemplateVariables['currency_symbol'] = $currencysymbol;
                        $emailTemplateVariables['product_price'] = $auctionBidCollection->getFirstItem()->getBidAmount();
                        $emailTemplateVariables['sender_name'] = $sender_name;
                        $options = ['area' => "frontend", 'store' => $_storeid];
                        $from = ["name" => $sender_name, "email" => $sender_email];
                        $emailTemplate = $this->_transport->setTemplateIdentifier($winner_email_template)
                            ->setTemplateOptions($options)
                            ->setTemplateVars($emailTemplateVariables)
                            ->setFrom($from)
                            ->addTo($customer_email);

                        $transport = $emailTemplate->getTransport();
                        $transport->sendMessage();
                    } catch (\Exception $e) {
                        $this->_logger->critical($e);
                        //$this->messageManager->addError($e->getMessage());
                        throw $e;
                    }
                }
            }
        }
    }
    /**
     * [load auction collection by auction_id]
     * @param  [int] $auc_id   [auction id]
     * @return [array]         [collection]
     */
    private function loadAuction($auc_id)
    {
        $aucData = $this->_auctionFactory->create()->load($auc_id);
        return $aucData;
    }
    /**
     * store auction status and winner customer id
     * @param  [int] $auc_id      [auction id]
     * @param  [int] $customer_id [customer id]
     */
    private function saveAuction($auc_id, $customer_id)
    {
        $model = $this->_auctionFactory->create()->load($auc_id);
        $model->setWinnerCustomerId($customer_id);
        $model->setStatus(2);
        $model->save();
        return;
    }
    /**
     * load customer collection
     * @param  [int] $customer_id   [customer id]
     * @return [array]              [collection]
     */
    private function loadCustomer($customer_id)
    {
        $custModel = $this->_customers->load($customer_id);
        return $custModel;
    }
    /**
     * load product and set product price
     * @param  [int] $pro_id      [product id]
     * @param  [int] $pro_price   [product price]
     * @return [array]            [collection]
     */
    private function loadProduct($pro_id, $pro_price)
    {
        $proModel = $this->_productFactory->create()->load($pro_id);
        $proModel->setPrice($pro_price);
        $proModel->save();
        return $proModel;
    }
}
