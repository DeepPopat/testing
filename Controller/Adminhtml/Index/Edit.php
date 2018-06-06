<?php
namespace Emipro\Smartproductselector\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager as ObjManager;

class Edit extends \Magento\Backend\App\Action
{
    protected $_coreRegistry = null;

    protected $resultPageFactory;
    private $objectMan;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return true;
    }

    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Emipro_Smartproductselector::grid')
            ->addBreadcrumb(__('Emipro'), __('Emipro'))
            ->addBreadcrumb(__('Emipro - Smart Alternative Product Selector'), __('Emipro - Smart Alternative Product Selector'));
        return $resultPage;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('rule_id');
        $this->objectMan = ObjManager::getInstance();
        $model = $this->objectMan->create('Emipro\Smartproductselector\Model\Rule');

        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                $this->_redirect('smartproductselector/*');
                return;
            }
        }

        $data = $this->objectMan->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('emipro_smartproductselector_rule', $model);
        $this->_initAction();
        $title = $id ? __("Edit Rule '" . $model->getRuleName() . "'") : __("New Rule");
        $breadcrumb = $id ? __("Edit Rule '" . $model->getRuleName() . "'") : __("New Rule");
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__($title));
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }
}
