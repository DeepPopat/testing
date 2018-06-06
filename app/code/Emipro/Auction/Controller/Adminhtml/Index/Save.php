<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Save auction data
 */
namespace Emipro\Auction\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Request\DataPersistorInterface;

class Save extends \Magento\Backend\App\Action
{

    protected $_adminSession;
    protected $_auctionFactory;
    protected $_stdTimezone;
    protected $datetime;
    protected $_productFactory;
    protected $dataPersistor;
    /**
     * [__construct description]
     * @param Action\Context                              $context        [context]
     * @param \Emipro\Auction\Model\AuctionFactory        $auctionFactory [Auction collection]
     * @param \Magento\Backend\Model\Auth\Session         $adminSession   [admin session]
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $stdTimezone    [magento timezone]
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime       [magento datetime]
     * @param \Magento\Catalog\Model\ProductFactory       $productFactory [product collection]
     */
    public function __construct(
        Action\Context $context,
        \Emipro\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magento\Framework\Stdlib\DateTime\Timezone $stdTimezone,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->_auctionFactory = $auctionFactory;
        $this->_adminSession = $adminSession;
        $this->_stdTimezone = $stdTimezone;
        $this->datetime = $datetime;
        $this->_productFactory = $productFactory;
        $this->dataPersistor = $dataPersistor;
    }

    public function execute()
    {

        $data = $this->getRequest()->getPostValue();
        $product_name = $data['product_id'];
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $auction_id = $this->getRequest()->getParam('auction_id');
            $model = $this->_auctionFactory->create();
            $customer_group_ids = implode(",", $data['customer_group_ids']);
            $data['start_time'] = $this->_stdTimezone->date(new \DateTime($data['start_time']))->format('Y-m-d H:i:s');
            $data['end_time'] = $this->_stdTimezone->date(new \DateTime($data['end_time']))->format('Y-m-d H:i:s');
            if ($auction_id) {
                $model->load($auction_id);
                $data['product_id'] = $model->getProductId();
            } else {
                if (strpos($data['product_id'], "sku:") !== false) {
                    $pro_skustr = explode('sku:', $data['product_id']);
                    $data['product_id'] = str_replace(']', "", $pro_skustr[1]);
                }
                $product_id = $this->_productFactory->create()->getIdBySku($data['product_id']);
                if ($product_id) {
                    $collection = $this->_auctionFactory->create()->getCollection()->addFieldToFilter('product_id', $product_id);
                    $collection->addFieldToFilter('auction_id', ['neq' => $this->getRequest()->getParam('auction_id')]);

                    if ($collection->getSize()) {
                        $this->messageManager->addError(__('This product is already in other Auction.'));
                        return $resultRedirect->setPath('*/*/edit', ['auction_id' => $this->getRequest()->getParam('auction_id')]);
                    }
                    $data['product_id'] = $product_id;
                } else {
                    $this->messageManager->addError(__("Please Enter valid SKU."));
                    $data['product_id'] = $product_name;
                    $this->dataPersistor->set('auction_page', $data);
                    return $resultRedirect->setPath('*/*/add', ['auction_id' => $this->getRequest()->getParam('auction_id')]);
                }
            }

            if (isset($data['product_id'])) {
                $model->setProductId($data['product_id']);
            } elseif ($data['product_id'] == null) {
                $this->messageManager->addError(__('You have to choose the product first.'));
                $data['product_id'] = $product_name;
                $this->dataPersistor->set('auction_page', $data);
                return $resultRedirect->setPath('*/*/add', ['auction_id' => $this->getRequest()->getParam('auction_id')]);
            }
            $model->setTitle($data['title']);
            $model->setMinPrice($data['min_price']);
            $model->setReservedPrice($data['reserved_price']);
            $model->setStartTime($data['start_time']);
            $model->setEndTime($data['end_time']);
            $model->setMinPriceGap($data['min_price_gap']);
            $model->setMaxPriceGap($data['max_price_gap']);
            $model->setAutoExtend($data['auto_extend']);
            if ($data['auto_extend'] == 1) {
                $model->setAutoExtendTime($data['auto_extend_time']);
                $model->setAutoExtendTimeLeft($data['auto_extend_time_left']);
            }
            $model->setCustomerGroupIds($customer_group_ids);

            if (strtotime($data['end_time']) > strtotime($data['start_time'])) {
                try {
                    $model->save();
                    $this->messageManager->addSuccess(__('Auction saved successfully.'));
                    $this->dataPersistor->clear('auction_page');
                    $this->_session->setFormData(false);
                    if ($this->getRequest()->getParam('back')) {
                        return $resultRedirect->setPath('*/*/edit', ['auction_id' => $model->getId(), '_current' => true]);
                    }
                    return $resultRedirect->setPath('*/*/');
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
                }
            } else {
                $this->messageManager->addError(__('Please enter end date-time greater then start date-time.'));
                if (!$auction_id) {
                    $data['product_id'] = $product_name;
                }
                $this->dataPersistor->set('auction_page', $data);
                if (!$auction_id) {
                    return $resultRedirect->setPath('*/*/add', ['auction_id' => $this->getRequest()->getParam('auction_id')]);
                } else {
                    return $resultRedirect->setPath('*/*/edit', ['auction_id' => $this->getRequest()->getParam('auction_id')]);
                }
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['auction_id' => $this->getRequest()->getParam('auction_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
