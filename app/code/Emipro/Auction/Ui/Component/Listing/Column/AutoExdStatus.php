<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Return auto extend status
 */
namespace Emipro\Auction\Ui\Component\Listing\Column;

class AutoExdStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context            [context object]
     * @param \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory [ui component]
     * @param array                                                        $components         [components]
     * @param array                                                        $data               [array]
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['auto_extend'] == 1) {
                    $autoexdstatus = "Yes";
                } else {
                    $autoexdstatus = "No";
                }
                $item['auto_extend'] = $autoexdstatus;
            }
        }

        return $dataSource;
    }
}
