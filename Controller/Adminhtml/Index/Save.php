<?php
namespace Emipro\Smartproductselector\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager as ObjManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

class Save extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{

    protected $_coreRegistry = null;

    protected $_dateFilter;
    private $objectMan;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        Date $dateFilter) {
        parent::__construct($context, $coreRegistry, $dateFilter);
    }

    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $this->objectMan = ObjManager::getInstance();
                $model = $this->objectMan->create('Emipro\Smartproductselector\Model\Rule');
                $data = $this->getRequest()->getPostValue();
                $id = $this->getRequest()->getParam('rule_id');

                if ($id) {
                    $applyrules = $model->load($id);
                    if ($id != $model->getId()) {
                        throw new LocalizedException(__('Wrong rule specified.'));
                    } else {
                        $model->setId($model->getRuleId());
                    }
                }
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
                if (isset($data["attribute_conditions"])) {
                    $attr_conditions = serialize($data["attribute_conditions"]);
                    unset($data["attribute_conditions"]);
                    $data["attribute_conditions"] = $attr_conditions;
                } else {
                    $data["attribute_conditions"] = '';
                }

                $model->loadPost($data);
                $this->objectMan->get('Magento\Backend\Model\Session')->setPageData($model->getData());
                $model->save();

                $this->messageManager->addSuccess(__('You saved the rule.'));
                $this->objectMan->get('Magento\Backend\Model\Session')->setPageData(false);

                if ($this->getRequest()->getParam('auto_apply')) {
                    $this->getRequest()->setParam('rule_id', $model->getRuleId());
                    $this->_forward('applyRules');
                } else {
                    $this->objectMan->create('Emipro\Smartproductselector\Model\Flag')->loadSelf()->setState(1)->save();
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', ['rule_id' => $model->getId()]);
                        return;
                    }
                }
                if ($model->isRuleBehaviorChanged()) {
                    $this->objectMan
                        ->create('Emipro\Smartproductselector\Model\Flag')
                        ->loadSelf()
                        ->setState(1)
                        ->save();
                }
                $this->_redirect('smartproductselector/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (LocalizedException $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->objectMan->get('Psr\Log\LoggerInterface')->critical($e);
                $this->objectMan->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('smartproductselector/*/edit', ['rule_id' => $this->getRequest()->getParam('rule_id')]);
            }
        }
        $this->_redirect('smartproductselector/*/');
    }
}
