<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
namespace Emipro\Auction\Model\ResourceModel\Bid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Emipro\Auction\Model\Bid', 'Emipro\Auction\Model\ResourceModel\Bid');
    }
}
