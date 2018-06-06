<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
namespace Emipro\Auction\Model\ResourceModel\Customer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Emipro\Auction\Model\Customer', 'Emipro\Auction\Model\ResourceModel\Customer');
    }
}
