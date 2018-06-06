<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Watchlist grid in admin
 */
namespace Emipro\Auction\Block\Adminhtml\Index\Edit\Tab;

use \Magento\Framework\App\ResourceConnection;

class Watchlist extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_customerFactory;
    protected $_auctionFactory;
    protected $_resource;
    /**
     * [__construct description]
     * @param \Magento\Backend\Block\Template\Context $context         [context object]
     * @param \Magento\Backend\Helper\Data            $backendHelper   [helper data]
     * @param \Emipro\Auction\Model\CustomerFactory   $customerFactory [auction watchlist customer collection]
     * @param \Emipro\Auction\Model\AuctionFactory    $auctionFactory  [auction collection]
     * @param ResourceConnection                      $resource        [database connectivity]
     * @param array                                   $data            [data array]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Emipro\Auction\Model\CustomerFactory $customerFactory,
        \Emipro\Auction\Model\AuctionFactory $auctionFactory,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->_auctionFactory = $auctionFactory;
        $this->_customerFactory = $customerFactory;
        $this->_resource = $resource;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setId('entity_id');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $id = $this->getRequest()->getParam('auction_id');
        $customer = $this->_resource->getTableName('customer_grid_flat');
        $auctionModel = $this->_auctionFactory->create()->load($id);
        $pro_id = $auctionModel->getProductId();
        $collection = $this->_customerFactory->create()->getCollection()->addFieldToFilter('watch_list', $pro_id);
        if (isset($id)) {
            $collection->getSelect()->join(['bidder' => $customer], 'bidder.entity_id = main_table.customer_id', ['bidder_name' => 'bidder.name', 'email' => 'bidder.email']);
        }
        $this->setCollection($collection);

        parent::_prepareCollection();
    }
    /**
     * [_prepareColumns]
     * @return [type] [render watchlist columns]
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
            ]
        );
        $this->addColumn(
            'bidder_name',
            [
                'header' => __('Bidder Name'),
                'index' => 'bidder_name',
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * [grid order setting]
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridWatchlist', ['_current' => true]);
    }

    /**
     * [Tab label]
     */
    public function getTabLabel()
    {
        return __('Watchlist Information');
    }

    /**
     * [Tab Title]
     */
    public function getTabTitle()
    {
        return __('Watchlist Information');
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
