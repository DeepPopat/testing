<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */
/**
 * Add new attribute in Customer account section.
 */
namespace Emipro\Auction\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;
    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        ResourceConnection $resource,
        \Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->resource = $resource;
        $this->indexerFactory = $indexerFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'is_allow', [
            'type' => 'int',
            'label' => 'Allow for Auction',
            'input' => 'select',
            'source' => Boolean::class,
            'required' => true,
            'default' => '1',
            'sort_order' => 300,
            'user_defined' => true,
            'system' => false,
            'position' => 400,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
        ]);
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_allow')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'], //you can use other forms also ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
            ]);

        $attribute->save();
    }
}
