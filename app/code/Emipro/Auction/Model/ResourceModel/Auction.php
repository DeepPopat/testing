<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
namespace Emipro\Auction\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Auction extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('emipro_auction', 'auction_id');
    }
}
