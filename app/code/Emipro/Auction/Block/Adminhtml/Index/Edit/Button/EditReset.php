<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Reset button for Edit auction page
 */
namespace Emipro\Auction\Block\Adminhtml\Index\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class EditReset implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;
    protected $_auctionFactory;
    protected $_stdTimezone;

    /**
     * [__construct description]
     * @param \Magento\Backend\Block\Template\Context     $context        [context object]
     * @param \Emipro\Auction\Model\AuctionFactory        $auctionFactory [Auction colllection]
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime       [Magento datetime]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
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
        $auction_id = $this->context->getRequest()->getParam('auction_id');
        $auction = $this->_auctionFactory->create()->load($auction_id);
        $now = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
        $start_date = $auction->getStartTime();
        if ($start_date > $now) {
            return [
                'label' => __('Reset'),
                'class' => 'reset',
                'on_click' => 'location.reload();',
                'sort_order' => 30,
            ];
        }
    }
}
