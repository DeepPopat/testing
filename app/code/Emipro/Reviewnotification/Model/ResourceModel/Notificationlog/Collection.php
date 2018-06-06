<?php
namespace Emipro\Reviewnotification\Model\ResourceModel\Notificationlog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Emipro\Reviewnotification\Model\Notificationlog',
            'Emipro\Reviewnotification\Model\ResourceModel\Notificationlog'
        );
    }
}
