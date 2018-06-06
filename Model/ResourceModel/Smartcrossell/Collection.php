<?php
namespace Emipro\Smartproductselector\Model\ResourceModel\Smartcrossell;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Emipro\Smartproductselector\Model\Smartcrossell', 'Emipro\Smartproductselector\Model\ResourceModel\Smartcrossell');
    }
}
