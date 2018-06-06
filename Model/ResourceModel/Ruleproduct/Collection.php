<?php
namespace Emipro\Smartproductselector\Model\ResourceModel\Ruleproduct;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Emipro\Smartproductselector\Model\Ruleproduct', 'Emipro\Smartproductselector\Model\ResourceModel\Ruleproduct');
    }
}
