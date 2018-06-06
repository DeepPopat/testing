<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
namespace Emipro\Auction\Model;

class Customer extends \Magento\Framework\Model\AbstractModel
{

    protected function _construct()
    {
        $this->_init('Emipro\Auction\Model\ResourceModel\Customer');
    }
}
