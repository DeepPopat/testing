<?php
namespace Emipro\Reviewnotification\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Notificationlog extends AbstractDb
{
    public function _construct()
    {
        $this->_init('emipro_review_notification_log', 'id');
    }
}
