<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Emipro\Smartproductselector\Model\ResourceModel\Rule;

class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    /**
     * Set resource model
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {

        $this->_init('Emipro\Smartproductselector\Model\Rule', 'Emipro\Smartproductselector\Model\ResourceModel\Rule');
    }

    /**
     * Find product attribute in conditions or actions
     *
     * @param string $attributeCode
     * @return $this
     * @api
     */
    public function addAttributeInConditionFilter($attributeCode)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion();

        if ($version < '2.2.0') {
            $serializeAttrCode = serialize(['attribute' => $attributeCode]);
        } else {
            $serializer = $objectManager->get('Magento\Framework\Serialize\Serializer\Json');
            $serializeAttrCode = $serializer->serialize(['attribute' => $attributeCode]);
        }

        $match = sprintf('%%%s%%', substr($serializeAttrCode, 5, -1));
        $this->addFieldToFilter('conditions_serialized', ['like' => $match]);

        return $this;
    }
}
