<?php

namespace Emipro\Smartproductselector\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager as ObjManager;
use Magento\Framework\Exception\LocalizedException;

class Delete extends \Magento\Backend\App\Action
{
    private $objectMan;

    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('rule_id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->objectMan = ObjManager::getInstance();
                $model = $this->objectMan->create('Emipro\Smartproductselector\Model\Rule');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The Rule has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['rule_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a rule to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
