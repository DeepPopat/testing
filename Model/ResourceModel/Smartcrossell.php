<?php
namespace Emipro\Smartproductselector\Model\ResourceModel;

class Smartcrossell extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('emipro_smartproductselector_crossell', 'crossell_id');
    }
}
