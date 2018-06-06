<?php
namespace Emipro\Smartproductselector\Model\ResourceModel\Smartrelated;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Emipro\Smartproductselector\Model\Smartrelated', 'Emipro\Smartproductselector\Model\ResourceModel\Smartrelated');
    }
}
