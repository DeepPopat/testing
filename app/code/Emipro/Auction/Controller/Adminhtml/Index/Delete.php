<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Auction\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Delete extends Action
{
    protected $modelAuction;

    /**
     * @param Action\Context $context
     * @param \Emipro\Auction\Model\Auction $model
     */
    public function __construct(
        Action\Context $context,
        \Emipro\Auction\Model\Auction $model
    ) {
        parent::__construct($context);
        $this->modelAuction = $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Emipro_Auction::index_delete');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('auction_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->modelAuction;
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Record deleted'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('Record does not exist'));
        return $resultRedirect->setPath('*/*/');
    }
}
