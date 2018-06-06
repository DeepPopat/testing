<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Used to Change Mass status
 */
namespace Emipro\Auction\Controller\Adminhtml\Index;

use Emipro\Auction\Model\Auction;
use Emipro\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassStatus extends \Magento\Backend\App\Action
{
    protected $filter;
    protected $collectionFactory;
    protected $Auctionmodel;
    /**
     * [__construct description]
     * @param Context           $context           [context object]
     * @param Filter            $filter            [Mass action filter]
     * @param CollectionFactory $collectionFactory [Auction collection]
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
        $parameterData = $this->getRequest()->getParams('status');
        $selectedAppsid = $this->getRequest()->getParams('status');
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
        $status = 0;
        $model = [];
        foreach ($collection as $item) {
            $this->setStatus($item->getAuctionId(), $this->getRequest()->getParam('status'));
            $status++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $status));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * [setStatus description]
     * @param [type] $id    [auction id]
     * @param [type] $Param [status value]
     */
    private function setStatus($id, $Param)
    {
        $item = $this->Auctionmodel->load($id);
        $item->setStatus($Param)->save();
        return;
    }
}
