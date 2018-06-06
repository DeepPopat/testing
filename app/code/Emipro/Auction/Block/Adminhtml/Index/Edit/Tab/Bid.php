<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Render Bid Grid in admin
 */
namespace Emipro\Auction\Block\Adminhtml\Index\Edit\Tab;

use \Magento\Framework\App\ResourceConnection;

class Bid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_bidFactory;
    protected $_resource;
    /**
     * [__construct description]
     * @param \Magento\Backend\Block\Template\Context $context       [context object]
     * @param \Magento\Backend\Helper\Data            $backendHelper [Helper data]
     * @param \Emipro\Auction\Model\BidFactory        $bidFactory    [Bid collection]
     * @param ResourceConnection                      $resource      [database connectivity]
     * @param array                                   $data          [array]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Emipro\Auction\Model\BidFactory $bidFactory,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->_bidFactory = $bidFactory;
        $this->_resource = $resource;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('bid_id');
        $this->setDefaultSort('bid_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $id = $this->getRequest()->getParam('auction_id');
        $customer = $this->_resource->getTableName('customer_grid_flat');
        $collection = $this->_bidFactory->create()->getCollection()->addFieldToFilter('auction_id', $id);
        $collection->getSelect()->join(['bidder' => $customer], 'bidder.entity_id = main_table.customer_id', ['bidder_name' => 'bidder.name']);
        $this->setCollection($collection);
        parent::_prepareCollection();
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'bid_id',
            [
                'header' => __('ID'),
                'index' => 'bid_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'bidder_name',
            [
                'header' => __('Bidder Name'),
                'index' => 'bidder_name',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'bid_amount',
            [
                'header' => __('Bid Amount'),
                'index' => 'bid_amount',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'created_time',
            [
                'header' => __('Bid Date & Time'),
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'index' => 'created_time',
                'type' => 'datetime',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Grid ascending/descending setting
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridBid', ['_current' => true]);
    }

    /**
     * Tab label
     */
    public function getTabLabel()
    {
        return __('Bid Information');
    }

    /**
     * Tab title
     */
    public function getTabTitle()
    {
        return __('Bid Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
