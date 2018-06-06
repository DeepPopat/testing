<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Emipro\Reviewnotification\Controller\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;

class Post extends \Magento\Review\Controller\Product\Post
{
    /**
     * Submit new review action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $data = $this->reviewSession->getFormData(true);
        if ($data) {
            $rating = [];
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPostValue();
            $rating = $this->getRequest()->getParam('ratings', []);
        }
        if (($product = $this->initProduct()) && !empty($data)) {
            /** @var \Magento\Review\Model\Review $review */
            $review = $this->reviewFactory->create()->setData($data);
            $review->unsetData('review_id');
            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(Review::STATUS_PENDING)
                        ->setCustomerId($this->customerSession->getCustomerId())
                        ->setStoreId($this->storeManager->getStore()->getId())
                        ->setStores([$this->storeManager->getStore()->getId()])
                        ->save();

                    $allrating = [];
                    foreach ($rating as $ratingId => $optionId) {
                        $this->ratingFactory->create()
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->setCustomerId($this->customerSession->getCustomerId())
                            ->addOptionVote($optionId, $product->getId());
                        /*Emipro Code Start*/
                        $collection = $this->_objectManager->get('Magento\Review\Model\Rating')->getCollection();
                        $collection->addFieldToFilter('rating_id', $ratingId);
                        $sortoptionId = ($ratingId * 5);
                        if ($sortoptionId != 5) {
                            $optionId = $optionId - ($sortoptionId - 5);
                        }
                        if ($collection->getFirstItem()->getIsActive()) {
                            $valuestar = '';
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $optionId) {
                                    $valuestar .= '&#9733;';
                                } else {
                                    $valuestar .= '&#9734;';
                                }
                            }
                            $allrating[$collection->getFirstItem()->getRatingId()] = ['rating_code' =>
                                $collection->getFirstItem()->getRatingCode(), 'start' => $valuestar];
                        }
                    }
                    ksort($allrating);
                    $review->aggregate();
                    $this->messageManager->addSuccess(__('You submitted your review for moderation.'));
                    /*Emipro Code Start*/
                    $collection = $this->_objectManager->create('Emipro\Reviewnotification\Model\Notificationlog')
                        ->getCollection();
                    $log_id = $collection->getFirstItem()->getId();
                    $NumberofReview = $collection->getFirstItem()->getNumberofReview();
                    if ($log_id) {
                        $this->_objectManager->create('Emipro\Reviewnotification\Model\Notificationlog')
                            ->setId($log_id)
                            ->setNumberofReview($NumberofReview + 1)
                            ->save();
                    } else {
                        $this->_objectManager->create('Emipro\Reviewnotification\Model\Notificationlog')
                            ->setNumberofReview(1)
                            ->save();
                    }
                    $helper = $this->_objectManager->get('Emipro\Reviewnotification\Helper\Data');
                    $email_notification = $helper->getConfig('reviewnotification/config/email_notification');
                    if ($email_notification) {
                        $identity = $helper->getConfig('reviewnotification/config/identity');
                        $template = $helper->getConfig('reviewnotification/config/review_notification_template_email');
                        $email_receivers = $helper->getConfig('reviewnotification/config/email_receivers');
                        $sender_email = $helper->getConfig('trans_email/ident_' . $identity . '/email');
                        $sender_name = $helper->getConfig('trans_email/ident_' . $identity . '/name');
                        $approveurl = $this->storeManager->getStore()->getBaseUrl()
                        . 'reviewnotify/product/statuschange/id/'
                        . base64_encode($review->getId()) . '/status/' . base64_encode(1)
                        . '/entity/' . base64_encode($product->getId());
                        $disapproveurl = $this->storeManager->getStore()->getBaseUrl()
                        . 'reviewnotify/product/statuschange/id/'
                        . base64_encode($review->getId()) . '/status/' . base64_encode(3)
                        . '/entity/' . base64_encode($product->getId());
                        $postObject = new \Magento\Framework\DataObject();
                        $postObject->setProductName($product->getName());
                        $postObject->setCustomerName($data['nickname']);
                        $postObject->setReviewTitle($data['title']);
                        $postObject->setReviewDesc($data['detail']);
                        $postObject->setProductId($product->getId());
                        $postObject->setApproveUrl($approveurl);
                        $postObject->setDisapproveUrl($disapproveurl);
                        $postObject->setProductUrl($product->getProductUrl());
                        $postObject->setReviewId($review->getId());
                        $rattingdata = '';
                        if ($allrating) {
                            $rattingdata .= '<p><strong>Rating(s): </strong>';
                            foreach ($allrating as $singlerating) {
                                $rattingdata .= '<br><strong>' . $singlerating["rating_code"] .
                                ' :</strong><span style="font-size: 25px;"> '
                                . html_entity_decode($singlerating["start"]) . '</span>';
                            }
                            $rattingdata .= '</p>';
                        }
                        $postObject->setRatingData($rattingdata);
                        $variables = ['data' => $postObject];
                        $options = ['area' => "frontend", 'store' => $this->storeManager->getStore()->getId()];

                        $from = ["name" => $sender_name, "email" => $sender_email];
                        if ($email_receivers) {
                            $email_receivers = explode(',', $email_receivers);
                            if (!empty($email_receivers)) {
                                $transportTemplate = $this->_objectManager
                                    ->create('Magento\Framework\Mail\Template\TransportBuilder')
                                    ->setTemplateIdentifier($template)
                                    ->setTemplateOptions($options)
                                    ->setTemplateVars($variables)
                                    ->setFrom($from)
                                    ->addTo($email_receivers);

                                $transport = $transportTemplate->getTransport();
                                $transport->sendMessage();
                            }
                        }
                    }
                    /*Emipro Code End*/
                } catch (\Exception $e) {
                    $this->reviewSession->setFormData($data);
                    $this->messageManager->addError(__('We can\'t post your review right now.'));
                }
            } else {
                $this->reviewSession->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                } else {
                    $this->messageManager->addError(__('We can\'t post your review right now.'));
                }
            }
        }
        $redirectUrl = $this->reviewSession->getRedirectUrl(true);
        $resultRedirect->setUrl($redirectUrl ?: $this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }
}
