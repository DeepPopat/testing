<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Auction\Ui\Component\Listing\Column;

class AuctionActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $urlBuilder;

    const URL_EDIT_PATH = 'auction/index/edit';
    const URL_DELETE_PATH = 'auction/index/delete';
    /**
     * @param \Magento\Framework\UrlInterface                              $urlBuilder         [get current url]
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context            [context object]
     * @param \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory [ui component factory]
     * @param array                                                        $components         [$components]
     * @param array                                                        $data               [array]
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['auction_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_EDIT_PATH,
                                [
                                    'auction_id' => $item['auction_id'],
                                ]
                            ),
                            'label' => __('Edit'),
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_DELETE_PATH,
                                [
                                    'auction_id' => $item['auction_id'],
                                ]
                            ),
                            'label' => __('Delete'),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
