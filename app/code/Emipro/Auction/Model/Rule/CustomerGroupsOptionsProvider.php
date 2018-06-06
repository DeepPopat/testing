<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Auction\Model\Rule;

class CustomerGroupsOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_customerGroup;

    /**
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
    ) {
        $this->_customerGroup = $customerGroup;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $customerGroups = $this->_customerGroup->toOptionArray();
        $options = [];
        foreach ($customerGroups as $key => $value) {
            if ($value['value'] != 0) {
                $options[] = [
                    'value' => $value['value'],
                    'label' => $value['label'],
                ];
            }
        }
        return $options;
    }
}
