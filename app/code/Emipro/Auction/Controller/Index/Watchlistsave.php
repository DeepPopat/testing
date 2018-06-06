<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Used for save watchlist product data
 */
namespace Emipro\Auction\Controller\Index;

use Emipro\Auction\Model\CustomerFactory;

class Watchlistsave extends \Magento\Framework\App\Action\Action
{
    /**
     * [__construct description]
     * @param \Magento\Framework\App\Action\Context $context         [Context Object]
     * @param CustomerFactory                       $CustomerFactory [Get Customer Collection]
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CustomerFactory $CustomerFactory
    ) {
        parent::__construct($context);
        $this->_customerFactory = $CustomerFactory;
    }
    public function execute()
    {
        /**
         * Get Customer id and watchlsit product id
         * Save in database
         * Remove from database
         * Get success message
         */
        $watchlistdata = (array) $this->getRequest()->getPost();
        $customermodel = $this->_customerFactory->create();
        $customerdata = $customermodel->getCollection()->addFieldToFilter('customer_id', $watchlistdata['customer_id']);
        foreach ($customerdata as $watchresult) {
            if ($watchlistdata['product_id'] == $watchresult->getWatchList()) {
                $this->removeWatchlist($watchresult->getEntityId());
                $msg["status"] = "remove from watchlist";
                $msg["msg"] = __("Product is removed from watchlist successfully..");
                $this->getResponse()->setBody(json_encode($msg));
                return;
            }
        }
        $customermodel->setWatchList($watchlistdata['product_id']);
        $customermodel->setCustomerId($watchlistdata['customer_id']);
        $customermodel->save();
        $msg["status"] = "Add to watchlist";
        $msg["msg"] = __("Product is added to watchlist successfully..");
        $this->getResponse()->setBody(json_encode($msg));
        return;
    }
    /**
     * [remove auction from watchlist]
     * @param  [type] $id [auction id]
     * @return [type]     [description]
     */
    private function removeWatchlist($id)
    {
        $item = $this->_customerFactory->create()->load($id);
        $item->delete();
        $item->save();
        return;
    }
}
