<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Auction\Model\ResourceModel\Auction\Grid;

use Emipro\Auction\Model\ResourceModel\Auction\Collection as AuctionCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document as AuctionModel;

class Collection extends AuctionCollection implements \Magento\Framework\Api\Search\SearchResultInterface
{
    protected $aggregations;

    // @codingStandardsIgnoreStart
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = AuctionModel::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }
    // @codingStandardsIgnoreEnd
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->joinLeft(
            ['product' => $this->getTable('catalog_product_entity')],
            'product.entity_id = main_table.product_id',
            ['product_sku' => 'product.sku']
        );
        $this->getSelect()
            ->joinLeft(
                ['bid' => $this->getTable('emipro_auction_bid')],
                '(bid.auction_id = `main_table`.auction_id)',
                ['last_bid' => '(select bid_amount from ' . $this->getTable('emipro_auction_bid') . ' where auction_id = main_table.auction_id ORDER BY bid_id DESC LIMIT 1)']
            )->group('main_table.auction_id');
        $this->getSelect()
            ->joinLeft(
                ['aubid' => $this->getTable('emipro_auction_bid')],
                '(aubid.auction_id = `main_table`.auction_id)',
                ['total_bid' => '(select count(bid_amount) from ' . $this->getTable('emipro_auction_bid') . ' where auction_id = main_table.auction_id)']
            )->group('main_table.auction_id');
        $this->getSelect()
            ->joinLeft(
                ['customer' => $this->getTable('customer_grid_flat')],
                '(customer.entity_id = `main_table`.winner_customer_id)',
                ['winner_customer' => 'customer.name']
            );

        parent::_renderFiltersBefore();
    }
    public function getAggregations()
    {
        return $this->aggregations;
    }
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }
    public function getSearchCriteria()
    {
        return null;
    }
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }
    public function getTotalCount()
    {
        return $this->getSize();
    }
    public function setTotalCount($totalCount)
    {
        return $this;
    }
    public function setItems(array $items = null)
    {
        return $this;
    }
}
