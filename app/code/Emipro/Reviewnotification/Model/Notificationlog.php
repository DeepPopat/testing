<?php
namespace Emipro\Reviewnotification\Model;

use Magento\Framework\Model\AbstractModel;

class Notificationlog extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init('Emipro\Reviewnotification\Model\ResourceModel\Notificationlog');
    }
}
