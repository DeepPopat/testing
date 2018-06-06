<?php

/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Save button for Edit auction page
 */
namespace Emipro\Auction\Block\Adminhtml\Index\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

class EditSave extends Generic implements ButtonProviderInterface
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
     * @param \Emipro\Auction\Model\AuctionFactory        $auctionFactory [Auction collection]
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
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'auction_edit_form.auction_edit_form',
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
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    protected function getOptions()
    {
        $auction_id = $this->context->getRequest()->getParam('auction_id');
        $auction = $this->_auctionFactory->create()->load($auction_id);
        $now = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
        $start_date = $auction->getStartTime();
        $options = [];
        if ($start_date > $now) {
            $options[] = [
                'id_hard' => 'save_and_new',
                'label' => __('Save & New'),
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'auction_edit_form.auction_edit_form',
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
                                    'targetName' => 'auction_edit_form.auction_edit_form',
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
        }

        return $options;
    }
}
