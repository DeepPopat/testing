<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Return auction status
 */
namespace Emipro\Auction\Ui\Component\Listing\Column;

class AuctionStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $datetime;
    protected $_stdTimezone;
    public $_auctionFactory;
    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                  $datetime           [magento datetime]
     * @param \Emipro\Auction\Model\AuctionFactory                         $auctionFactory     [auction collection]
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context            [context object]
     * @param \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory [ui component]
     * @param array                                                        $components         [components]
     * @param array                                                        $data               [array]
     */
    public function __construct(

        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Stdlib\DateTime\Timezone $stdTimezone,
        \Emipro\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {

        $this->datetime = $datetime;
        $this->_stdTimezone = $stdTimezone;
        $this->_auctionFactory = $auctionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {

                if (isset($item['auction_id'])) {
                    //$auction = $this->_auctionFactory->create()->load();
                    $now = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
                    $start_date = $this->getAuction($item['auction_id'])->getStartTime();
                    $end_date = $this->getAuction($item['auction_id'])->getEndTime();

                    if (($start_date <= $now) && ($end_date >= $now)) {
                        $auction_status = "Continue";
                    } elseif ($start_date > $now) {
                        $auction_status = "Not Started";
                    } elseif ($end_date < $now) {
                        $auction_status = "Complete";
                    }
                    $item['status'] = $auction_status;
                }
            }
        }
        return $dataSource;
    }
    public function getAuction($id)
    {
        $item = $this->_auctionFactory->create()->load($id);
        return $item;
    }
}
