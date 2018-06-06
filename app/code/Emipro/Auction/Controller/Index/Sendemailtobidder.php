<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Used for send email to customer
 */
namespace Emipro\Auction\Controller\Index;

use Emipro\Auction\Model\BidFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;

class Sendemailtobidder extends \Magento\Framework\App\Action\Action
{
    protected $_storeManager;
    protected $_transport;
    protected $_config;
    protected $_customers;
    protected $logger;
    /**
     * [__construct description]
     * @param \Magento\Framework\App\Action\Context $context          [Context Object]
     * @param StoreManagerInterface                 $storeManager     [Get Store Collection]
     * @param TransportBuilder                      $transportBuilder [Used For Send Email]
     * @param ScopeConfigInterface                  $scopeConfig      [Get Data in Admin Panel]
     * @param \Magento\Customer\Model\Customer      $customers        [Get Customer Collection]
     * @param \Emipro\Auction\Model\BidFactory      $BidFactory       [Get Bid Collection]
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Customer $customers,
        \Emipro\Auction\Model\BidFactory $BidFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_transport = $transportBuilder;
        $this->_config = $scopeConfig;
        $this->_customers = $customers;
        $this->bidFactory = $BidFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        /**
         *Get Customer id and email for send mail
         */
        try {
            $template = "";
            $emaildata = (array) $this->getRequest()->getPost();
            $currentStore = $this->_storeManager->getStore();
            $_storeid = $currentStore->getId();
            $raise_amount = $emaildata['bid_amount'];
            $customer_id = $emaildata['customer_id'];
            $auction_id = $emaildata['auction_id'];
            $auction_title = $emaildata['auction_title'];
            $prourl = $emaildata['prourl'];
            $currencysymbol = $emaildata['currencysymbol'];
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $sender_email = $this->_config->getValue('trans_email/ident_general/email', $storeScope, $_storeid);
            $sender_name = $this->_config->getValue('trans_email/ident_general/name', $storeScope, $_storeid);
            $support_email = $this->_config->getValue('trans_email/ident_support/email', $storeScope, $_storeid);
            $customerdata = $this->_customers->load($customer_id);
            $customer_email = $customerdata->getEmail();

            /**
             *Get email template in admin panel
             *For place bid email
             */
            $bidder_email_template = $this->_config->getValue("emiproauction/emailtemplate/bid_successful_email_template", $storeScope, $_storeid);
            $emailTemplateVariables = [];
            $emailTemplateVariables['auction_title'] = $auction_title;
            $emailTemplateVariables['product_url'] = $prourl;
            $emailTemplateVariables['bid_amount'] = $raise_amount;
            $emailTemplateVariables['currency_symbol'] = $currencysymbol;
            $options = ['area' => "frontend", 'store' => $_storeid];
            $from = ["name" => $sender_name, "email" => $sender_email];
            $transportTemplate = $this->_transport->setTemplateIdentifier($bidder_email_template)
                ->setTemplateOptions($options)
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($from)
                ->addTo($customer_email);
            $transport = $transportTemplate->getTransport();
            $transport->sendMessage();

            $lastcustomerid = $emaildata["customer_id_for_raisebid_template"];
            $lastcustomerdata = $this->_customers->load($lastcustomerid);
            $lastcustomeremail = $lastcustomerdata->getEmail();
            /**
             *Get email template in admin panel
             *For raise bid email
             */
            if ($lastcustomerid != $customer_id) {
                $raise_email_template = $this->_config->getValue("emiproauction/emailtemplate/raise_bid_email_template", $storeScope, $_storeid);
                $raise_email_variables = [];
                $raise_email_variables['auction_title'] = $auction_title;
                $raise_email_variables['raise_amount'] = $raise_amount;
                $raise_email_variables['product_url'] = $prourl;
                $raise_email_variables['currency_symbol'] = $currencysymbol;
                $options = ['area' => "frontend", 'store' => $_storeid];
                $from = ["name" => $sender_name, "email" => $sender_email];
                $transportTemplate = $this->_transport->setTemplateIdentifier($raise_email_template)
                    ->setTemplateOptions($options)
                    ->setTemplateVars($raise_email_variables)
                    ->setFrom($from)
                    ->addTo($lastcustomeremail);

                $transport = $transportTemplate->getTransport();
                $transport->sendMessage();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw $e;
        }
    }
}
