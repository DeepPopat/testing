<?php
namespace Emipro\Smartproductselector\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager as ObjManager;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;
    private $objectMan;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $this->objectMan = ObjManager::getInstance();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Emipro_Smartproductselector::grid');
        $resultPage->addBreadcrumb(__('Smart Alternative Product Selector'), __('Smart Alternative Product Selector'));
        $resultPage->getConfig()->getTitle()->prepend(__('Smart Alternative Product Selector'));
        return $resultPage;
    }
}
