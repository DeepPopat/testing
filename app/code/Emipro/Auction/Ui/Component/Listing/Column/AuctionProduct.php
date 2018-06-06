<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Return product name and sku in Edit auction
 */
namespace Emipro\Auction\Ui\Component\Listing\Column;

class AuctionProduct extends \Magento\Ui\Component\Listing\Columns\Column
{
    public $_productFactory;
    /**
     * @param \Magento\Catalog\Model\ProductFactory                        $productFactory     [product collection]
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context            [context object]
     * @param \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory [ui component factory]
     * @param array                                                        $components         [$components]
     * @param array                                                        $data               [array]
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {

        $this->_productFactory = $productFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data'])) {
            if (isset($dataSource['data']['product_id']) && isset($dataSource['data']['auction_id'])) {
                $product = $this->_productFactory->create()->load($dataSource['data']['product_id']);
                $pro_name = $product->getName() . "[" . $product->getSku() . "]";
                $dataSource['data']['product_id'] = $pro_name;
            }
        }
        return $dataSource;
    }
}
