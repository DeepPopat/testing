<?php
namespace Emipro\Smartproductselector\Block\Adminhtml\Grid\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Newcondition extends Generic implements TabInterface
{
    protected $_systemStore;

    protected $_template = 'newcondition.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\ObjectManagerInterface $objectMan,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->objectMan = $objectMan;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Actions');
    }

    public function getTabTitle()
    {
        return __('Actions');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function getPostformUrl()
    {
        return $this->_urlBuilder->getUrl("smartproductselector/index/productgrid");
    }

    public function getPostformKey()
    {
        return $this->getRequest()->getParam('key');
    }

    public function getPostId()
    {
        return $this->getRequest()->getParam('rule_id');
    }

    public function getSmartProductData()
    {
        $ruleId = $this->getPostId();
        $smartProductData = $this->objectMan->create("Emipro\Smartproductselector\Model\Rule")->load($ruleId);
        return $smartProductData->getData();
    }
}
