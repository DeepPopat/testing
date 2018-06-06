<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Delete button for Edit auction page
 */
namespace Emipro\Auction\Block\Adminhtml\Index\Edit\Button;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class EditDelete extends Generic implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;
    protected $_auctionFactory;
    protected $_stdTimezone;

    /**
     * [__construct description]
     * @param Context                                     $context        [context object]
     * @param \Emipro\Auction\Model\AuctionFactory        $auctionFactory [Auction collection]
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime       [magento datetime]
     */
    public function __construct(
        Context $context,
        \Emipro\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Stdlib\DateTime\Timezone $stdTimezone
    ) {
        $this->context = $context;
        $this->_auctionFactory = $auctionFactory;
        $this->datetime = $datetime;
        $this->_stdTimezone = $stdTimezone;
    }

    /**
     * get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $auction_id = $this->context->getRequest()->getParam('auction_id');
        $auction = $this->_auctionFactory->create()->load($auction_id);
        $now = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
        $start_date = $auction->getStartTime();
        if ($auction_id) {
            if ($start_date > $now) {
                $data = [
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\')',
                    'sort_order' => 20,
                ];
            }
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        $auction_id = $this->context->getRequest()->getParam('auction_id');
        return $this->getUrl('*/*/delete', ['auction_id' => $auction_id]);
    }
}
