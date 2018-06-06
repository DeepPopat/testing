<?php
namespace Emipro\Smartproductselector\Model\ResourceModel\Smartupsell;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Emipro\Smartproductselector\Model\Smartupsell', 'Emipro\Smartproductselector\Model\ResourceModel\Smartupsell');
    }
}
