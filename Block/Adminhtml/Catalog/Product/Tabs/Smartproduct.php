<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Smartproductselector\Block\Adminhtml\Catalog\Product\Tabs;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Smartproduct extends Generic implements TabInterface
{
    protected $_template = 'smartproduct.phtml';

    protected $_systemStore;

    protected $_scopeConfig;

    protected $helper;

    protected $smartRelated;

    protected $smartUpsell;

    protected $smartCrossell;

    protected $rule;

    protected $ruleProduct;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Emipro\Smartproductselector\Helper\Data $helper,
        \Emipro\Smartproductselector\Model\Rule $rule,
        \Emipro\Smartproductselector\Model\Ruleproduct $ruleProduct,
        \Emipro\Smartproductselector\Model\Smartrelated $smartRelated,
        \Emipro\Smartproductselector\Model\Smartupsell $smartUpsell,
        \Emipro\Smartproductselector\Model\Smartcrossell $smartCrossell,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->helper = $helper;
        $this->rule = $rule;
        $this->ruleProduct = $ruleProduct;
        $this->smartRelated = $smartRelated;
        $this->smartUpsell = $smartUpsell;
        $this->smartCrossell = $smartCrossell;
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

    public function getReqest()
    {
        return $this->getRequest()->getParams();
    }

    public function getRelated()
    {
        $request = $this->getReqest();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeconfigVal = $this->_scopeConfig->getValue("smartproductselector/smarproductconfig/productCountAdmin", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $smartRelatedProduct = [];
        if ($scopeconfigVal == 0) {
            if (array_key_exists("id", $request)) {
                $smartRelated = $this->helper->getSmartRelatedProduct($request["id"]);
                if ($smartRelated) {
                    foreach ($smartRelated as $value) {
                        $smartRelatedProduct[] = $value->getId();
                    }
                }
                return $smartRelatedProduct;
            }
        } else {
            if (array_key_exists("id", $request)) {
                $smartRelated = $this->smartRelated->load($request["id"], 'pro_id')->getData();
                if ($smartRelated) {
                    $ruleProductRe = $this->ruleProduct->load($smartRelated["rule_id"])->getData();
                    $ruleRe = $this->rule->load($ruleProductRe['rule_id'])->getData();
                    $smartRelatedProduct = explode(',', $smartRelated['frontpro_id']);
                }
            }
            return $smartRelatedProduct;
        }
    }

    public function getUpsell()
    {
        $request = $this->getReqest();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeconfigVal = $this->_scopeConfig->getValue("smartproductselector/smarproductconfig/productCountAdmin", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $smartUpsellProduct = [];
        if ($scopeconfigVal == 0) {
            if (array_key_exists("id", $request)) {
                $smartUpsell = $this->helper->getUpsellProduct($request['id']);
                if ($smartUpsell) {
                    foreach ($smartUpsell as $value) {
                        $smartUpsellProduct[] = $value->getId();
                    }
                }
                return $smartUpsellProduct;
            }
        } else {
            if (array_key_exists("id", $request)) {
                $smartUpsell = $this->smartUpsell->load($request["id"], 'pro_id')->getData();
                if ($smartUpsell) {
                    $ruleProductUp = $this->ruleProduct->load($smartUpsell["rule_id"])->getData();
                    $ruleUp = $this->rule->load($ruleProductUp['rule_id'])->getData();
                    if ($ruleUp['is_active'] == 1) {
                        $smartUpsellProduct = explode(',', $smartUpsell['frontpro_id']);
                    }
                }
            }
            return $smartUpsellProduct;
        }
    }

    public function getCrossell()
    {
        $request = $this->getReqest();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeconfigVal = $this->_scopeConfig->getValue("smartproductselector/smarproductconfig/productCountAdmin", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $smartCrosssellProduct = [];
        if ($scopeconfigVal == 0) {
            if (array_key_exists("id", $request)) {
                $smartCrosssellProduct = $this->helper->getCrosssellProduct([$request["id"]]);
                return $smartCrosssellProduct;
            }
        } else {
            if (array_key_exists("id", $request)) {
                $smartCrosssell = $this->smartCrossell->load($request["id"], 'pro_id')->getData();
                if ($smartCrosssell) {
                    $ruleProductCr = $this->ruleProduct->load($smartCrosssell["rule_id"])->getData();
                    $ruleCr = $this->rule->load($ruleProductCr['rule_id'])->getData();
                    if ($ruleCr['is_active'] == 1) {
                        $smartCrosssellProduct = explode(',', $smartCrosssell['frontpro_id']);
                    }
                }
            }
            return $smartCrosssellProduct;
        }
    }
}
