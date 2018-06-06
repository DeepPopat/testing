<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Used for admain Bid grid
 */
namespace Emipro\Auction\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;

class GridBid extends Action
{
    /**
     * [__construct description]
     * @param Context       $context           [contect object]
     * @param PageFactory   $resultPageFactory [get result page factory]
     * @param Rawfactory    $resultRawFactory  [for the page layout]
     * @param LayoutFactory $layoutFactory     [render page layout]
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Rawfactory $resultRawFactory,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * [execute description]
     * @return [type] [return admin grid]
     */
    public function execute()
    {
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                'Emipro\Auction\Block\Adminhtml\Index\Edit\Tab\Bid',
                'auction.edit.tab.bid'
            )->toHtml()
        );
    }
}
