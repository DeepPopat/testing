<?php

namespace Emipro\Reviewnotification\Model\System\Message;

class Reviewnotic implements \Magento\Framework\Notification\MessageInterface
{
    protected $_backendUrl;
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->_backendUrl = $backendUrl;
    }
    public function getNumberofReview()
    {
        $objManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objManager->create('Emipro\Reviewnotification\Model\Notificationlog')->getCollection();
        $log_id = $collection->getFirstItem()->getId();
        $NumberofReview = $collection->getFirstItem()->getNumberofReview();
        return $NumberofReview;
    }
    public function getIdentity()
    {
        $NumberofReview = $this->getNumberofReview();
        // Retrieve unique message identity
        return 'eminotificationidentity' . $NumberofReview;
    }

    public function isDisplayed()
    {
        // Return true to show your message, false to hide it
        $objManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objManager->get('Emipro\Reviewnotification\Helper\Data');
        $display_notification = $helper->getConfig('reviewnotification/config/display_notification');
        if ($display_notification) {
            $NumberofReview = $this->getNumberofReview();
            if ($NumberofReview > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getText()
    {
        $NumberofReview = $this->getNumberofReview();
        if ($NumberofReview > 0) {
            $objManager = \Magento\Framework\App\ObjectManager::getInstance();
            $collection = $objManager->create('Magento\Review\Model\Review')->getCollection();
            $collection->addFieldToFilter('status_id', 2);
            $pandingcount = $collection->count();
            $url = $this->_backendUrl->getUrl("review/product/index");
            $hideurl = $this->_backendUrl->getUrl("reviewnotification/notification/hide");
            if ($pandingcount) {
            	return __('You have %1 new review(s). And %2 pending review(s) for approval.
            	<a href="%3">Click here</a> to approve now.
            	<a class="action-primary" href="%4">Hide</a>', $NumberofReview, $pandingcount, $url, $hideurl);
            }
            else
            {
            	return __('You have %1 new review(s).
           		 <a href="%2">Click here</a> to approve now.
          		 <a class="action-primary" href="%3">Hide</a>', $NumberofReview, $url, $hideurl);
            }
            
        } else {
            return;
        }
    }

    public function getSeverity()
    {
        // Possible values: SEVERITY_CRITICAL, SEVERITY_MAJOR, SEVERITY_MINOR, SEVERITY_NOTICE
        return self::SEVERITY_CRITICAL;
    }
}
