<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Used to disable cache for catalog_product_view layout for auction
 */
namespace Emipro\Auction\Controller\Product;

class View extends \Magento\Catalog\Controller\Product\View
{
    public function execute()
    {

        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        if ($this->getRequest()->isPost() && $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            $product = $this->_initProduct();
            if (!$product) {
                return $this->noProductRedirect();
            }
            if ($specifyOptions) {
                $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
                $this->messageManager->addNotice($notice);
            }
            if ($this->getRequest()->isAjax()) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode([
                        'backUrl' => $this->_redirect->getRedirectUrl(),
                    ])
                );
                return;
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererOrBaseUrl();
            return $resultRedirect;
        }

        // Prepare helper and params
        $params = new \Magento\Framework\DataObject();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        // Render page
        try {
            $page = $this->resultPageFactory->create();
            $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
            $page->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
            return $page;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->noProductRedirect();
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }

    }
}
