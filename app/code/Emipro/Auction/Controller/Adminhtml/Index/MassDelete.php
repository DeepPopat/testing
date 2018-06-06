<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
namespace Emipro\Auction\Controller\Adminhtml\Index;

use Emipro\Auction\Model\Auction;
use Emipro\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends \Magento\Backend\App\Action
{
    protected $filter;
    protected $collectionFactory;
    protected $Auctionmodel;
    /**
     * @param Context           $context           [context object]
     * @param Filter            $filter            [Mass action filter]
     * @param CollectionFactory $collectionFactory [Auction colleciotn]
     * @param Auction           $Auctionmodel      [Auction model]
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Auction $Auctionmodel
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->Auctionmodel = $Auctionmodel;
        parent::__construct($context);
    }

    public function execute()
    {
        $jobData = $this->collectionFactory->create();

        foreach ($jobData as $value) {
            $templateId[] = $value['auction_id'];
        }
        $parameterData = $this->getRequest()->getParams('auction_id');
        $selectedAppsid = $this->getRequest()->getParams('auction_id');
        if (array_key_exists("selected", $parameterData)) {
            $selectedAppsid = $parameterData['selected'];
        }
        if (array_key_exists("excluded", $parameterData)) {
            if ($parameterData['excluded'] == 'false') {
                $selectedAppsid = $templateId;
            } else {
                $selectedAppsid = array_diff($templateId, $parameterData['excluded']);
            }
        }
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('auction_id', ['in' => $selectedAppsid]);
        $delete = 0;
        $model = [];
        foreach ($collection as $item) {
            $this->deleteById($item->getAuctionId());
            $delete++;
        }
        $this->messageManager->addSuccess(__('A total of %1 Records have been deleted.', $delete));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * [deleteById description]
     * @param  [type] $id [auction id]
     * @return [type]     [delete auction]
     */
    private function deleteById($id)
    {
        $item = $this->Auctionmodel->load($id);
        $item->delete();
        return;
    }
}
