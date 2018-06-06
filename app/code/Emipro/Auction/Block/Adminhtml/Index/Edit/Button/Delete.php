<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Auction\Block\Adminhtml\Index\Edit\Button;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Delete extends Generic implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context $context
     * @param AuthorRepositoryInterface $authorRepository
     */

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $auction_id = $this->context->getRequest()->getParam('auction_id');
        if ($auction_id) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        $auction_id = $this->context->getRequest()->getParam('auction_id');
        return $this->getUrl('*/*/delete', ['auction_id' => $auction_id]);
    }
}
