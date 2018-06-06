<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Auction\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_storeManager;
    protected $_currency;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_currency = $currencyFactory;
    }
    /**
     * Convert the amount according to current store currency
     * @param  [float] $amtVal [amount in base currency]
     * @return [float]         [converted amount in current store currency]
     */
    public function convertPrice($amtVal)
    {
        if (is_numeric($amtVal)) {
            $currencyrate = $this->_storeManager->getStore()->getCurrentCurrencyRate();
            $bid_price = $amtVal * $currencyrate;
            $amount = round($bid_price, 2);
            return $amount;
        } else {
            return $amtVal;
        }
    }
    /**
     * Convert the amount according to base currency
     * @param  [float] $amtVal [amount in current store currency]
     * @return [float]         [converted amount in base currency]
     */
    public function revertPrice($amtVal)
    {
        if (is_numeric($amtVal)) {
            $currencyrate = $this->_storeManager->getStore()->getCurrentCurrencyRate();
            $bid_amt = $amtVal / $currencyrate;
            //$old_amt = round($bid_amt, 2);
            return $bid_amt;
        } else {
            return $amtVal;
        }
    }
}
