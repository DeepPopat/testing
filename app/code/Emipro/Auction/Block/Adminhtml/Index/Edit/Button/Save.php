<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Save button for New auction
 */
namespace Emipro\Auction\Block\Adminhtml\Index\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

class Save extends Generic implements ButtonProviderInterface
{
    /**
     * get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'auction_form.auction_form',
                                'actionName' => 'save',
                                'params' => [
                                    false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getOptions(),
        ];
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    protected function getOptions()
    {
        $options[] = [
            'id_hard' => 'save_and_new',
            'label' => __('Save & New'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'auction_form.auction_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'back' => 'add',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $options[] = [
            'id_hard' => 'save_and_close',
            'label' => __('Save & Close'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'auction_form.auction_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $options;
    }
}
