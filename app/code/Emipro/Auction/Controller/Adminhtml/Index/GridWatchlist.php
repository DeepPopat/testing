<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Displayes admin watchlist grid
 */
namespace Emipro\Auction\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;

class GridWatchlist extends Action
{
    /**
     * [__construct description]
     * @param Context       $context           [context object]
     * @param PageFactory   $resultPageFactory [result page layout]
     * @param Rawfactory    $resultRawFactory  [For page layout]
     * @param LayoutFactory $layoutFactory     [render grid]
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
    public function execute()
    {
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                'Emipro\Auction\Block\Adminhtml\Index\Edit\Tab\Watchlist',
                'auction.edit.tab.watchlist'
            )->toHtml()
        );
    }
}
