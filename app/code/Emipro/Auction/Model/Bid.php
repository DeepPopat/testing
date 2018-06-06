<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Get Bid collection emipro_auction_bid table.
 */
namespace Emipro\Auction\Model;

class Bid extends \Magento\Framework\Model\AbstractModel
{
    /**
     * [__construct description]
     * @param \Magento\Framework\Model\Context                   $context            [Context Object]
     * @param \Magento\Framework\Registry                        $registry           [Store Data in Registry]
     * @param \Emipro\Auction\Model\ResourceModel\Bid\Collection $resourceCollection [Get Bid Collection]
     * @param \Magento\Framework\Stdlib\DateTime\Timezone        $_stdTimezone       [Get Time Zone]
     * @param \Magento\Checkout\Helper\Data                      $checkoutHelper     [Get Helper Data]
     * @param \Magento\Customer\Model\CustomerFactory            $_customerFactory   [Get Customer Collection]
     * @param \Emipro\Auction\Model\AuctionFactory               $aution             [Get Auction Collection]
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Emipro\Auction\Model\ResourceModel\Bid $resource,
        \Emipro\Auction\Model\ResourceModel\Bid\Collection $resourceCollection,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Customer\Model\CustomerFactory $_customerFactory,
        \Emipro\Auction\Model\AuctionFactory $aution
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_auctionFactory = $aution;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_stdTimezone = $_stdTimezone;
        $this->_customerFactory = $_customerFactory;
    }
    /**
     * Get Auction Id.
     */
    public function getAuction()
    {
        if (!$this->getData('auction')) {
            $this->setData('auction', $this->_auctionFactory->create()->load($this->getAuctionId()));
        }
        return $this->getData('auction');
    }
}
