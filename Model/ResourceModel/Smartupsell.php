<?php
namespace Emipro\Smartproductselector\Model\ResourceModel;

class Smartupsell extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('emipro_smartproductselector_upsell', 'upsell_id');
    }
}
