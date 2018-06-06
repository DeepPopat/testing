<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Auction\Model;

use Emipro\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;
    protected $_productFactory;
    protected $_stdTimezone;
    protected $dataPersistor;

    // @codingStandardsIgnoreStart
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $AuctionCollectionFactory,
        ProductFactory $productFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Stdlib\DateTime\Timezone $stdTimezone,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $AuctionCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_productFactory = $productFactory;
        $this->_stdTimezone = $stdTimezone;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    // @codingStandardsIgnoreEnd

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        foreach ($items as $Auction) {
            $pro_name = $this->getProductData($Auction->getProductId())->getName() . "[" . $this->getProductData($Auction->getProductId())->getSku() . "]";
            $start_date = $this->converToTz($Auction->getStartTime(), $this->_stdTimezone->getDefaultTimezone(), $this->_stdTimezone->getConfigTimezone());
            $end_date = $this->converToTz($Auction->getEndTime(), $this->_stdTimezone->getDefaultTimezone(), $this->_stdTimezone->getConfigTimezone());
            $this->loadedData[$Auction->getId()] = $Auction->getData();
            $this->loadedData[$Auction->getId()]['product_id'] = $pro_name;
            $this->loadedData[$Auction->getId()]['start_time'] = $start_date;
            $this->loadedData[$Auction->getId()]['end_time'] = $end_date;
        }

        $data = $this->dataPersistor->get('auction_page');
        if (!empty($data)) {
            $page = $this->collection->getNewEmptyItem();
            $page->setData($data);
            $this->loadedData[$page->getId()] = $page->getData();
            $this->dataPersistor->clear('auction_page');
        }
        return $this->loadedData;
    }
    /**
     * [Load product collection by id]
     * @param  [type] $id [product id]
     * @return [type]     [array]
     */
    public function getProductData($id)
    {
        $item = $this->_productFactory->create()->load($id);
        return $item;
    }
    /**
     * [convert date according to current time zone]
     * @param  string $dateTime [datetime]
     * @param  string $toTz     [$toTz]
     * @param  string $fromTz   [$fromTz]
     * @return [type]           [datetime]
     */
    public function converToTz($dateTime = "", $toTz = '', $fromTz = '')
    {
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('Y-m-d H:i:s');
        return $dateTime;
    }
}
