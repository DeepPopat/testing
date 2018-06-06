<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Auction\Model\ResourceModel\Auction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use \Emipro\Auction\Model\Auction;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Emipro\Auction\Model\Auction', 'Emipro\Auction\Model\ResourceModel\Auction');
    }
    public function joinTheBidderNameAndPrice()
    {
        $this->getSelect()
            ->joinLeft(
                ['bid' => $this->getTable('emipro_auction_bid')],
                'main_table.auction_id = bid.auction_id',
                ['min_price' => 'bid.bid_amount', 'customer_id' => 'bid.customer_id']
            )
            ->joinLeft(
                ['customer' => $this->getTable('customer_entity')],
                'bid.customer_id = customer.entity_id',
                ['bidder_name' => 'customer.firstname']
            );
        return $this;
    }
}
