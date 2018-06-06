<?php
namespace Emipro\Smartproductselector\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /*
         * Create table 'emipro_smartproductselector_rules'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('emipro_smartproductselector_rules'))
        /*
         * Rule Information
         */
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'rule_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name'
            )
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Description'
            )
            ->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Is Active'
            )
            ->addColumn(
                'alternative_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Alternative Type'
            )
            ->addColumn(
                'rule_priority',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Priority'
            )
        /*
         * Conditions
         */
            ->addColumn(
                'conditions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Conditions Serialized'
            )
            ->addColumn(
                'actions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Actions Serialized'
            )
            ->addColumn(
                'stop_rules_processing',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Stop Rules Processing'
            )

        /*
         * Configure Product By SKU's
         */
            ->addColumn(
                'set_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Set By SKU'
            )
            ->addColumn(
                'sku_data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'SKU Data'
            )

        /*
         * Configure product attributes condition for display alternative products
         */
            ->addColumn(
                'attribute_conditions',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Attribute Conditions'
            )

        /*
         * Advanced Configuration
         */
            ->addColumn(
                'attribute_set_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 0],
                'Attribute Set Id'
            )
            ->addColumn(
                'cat_select',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Cat Select'
            )
            ->addColumn(
                'specific_category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Specific Category Id'
            )
            ->addColumn(
                'price_range',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Price Range'
            )
            ->addColumn(
                'below_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Below Price'
            )
            ->addColumn(
                'above_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Above Price'
            )

        /*
         * Configuration For Frontend
         */
            ->addColumn(
                'no_product',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                2,
                ['nullable' => false],
                'no Product'
            )
            ->addColumn(
                'product_attribute',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product Attribute'
            )
            ->addColumn(
                'short_order_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                2,
                ['nullable' => false],
                'Short Order By'
            )
            ->addColumn(
                'product_shorting',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product Shorting'
            )
            ->addColumn(
                'out_stock',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                2,
                ['nullable' => false],
                'out stock'
            )
            ->addIndex(
                $installer->getIdxName(
                    'emipro_smartproductselector_rules',
                    [
                        'is_active',
                        'rule_priority',
                    ]
                ),
                ['is_active', 'rule_priority']
            )
            ->setComment('Emipro Smartproductselector Rules');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'emipro_smartproductselector_products'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('emipro_smartproductselector_products'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'EntityId'
            )
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
            )
            ->addIndex(
                $installer->getIdxName(
                    'emipro_smartproductselector_products',
                    ['rule_id']
                ),
                ['rule_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'emipro_smartproductselector_products',
                    'rule_id',
                    'emipro_smartproductselector_rules',
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable('emipro_smartproductselector_rules'),
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )

            ->setComment('Emipro Smartproductselector Products');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'emipro_smartproductselector_related'
         */

        $table = $installer->getConnection()
            ->newTable($installer->getTable('emipro_smartproductselector_related'))
            ->addColumn(
                'related_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
                'Realted ID'
            )
            ->addColumn(
                'pro_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                100,
                [],
                'Product ID'
            )
            ->addColumn(
                'frontpro_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Frontend ID'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Apply Rule Updated At'
            )
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
            )
            ->addIndex(
                $installer->getIdxName(
                    'emipro_smartproductselector_related',
                    ['rule_id']
                ),
                ['rule_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'emipro_smartproductselector_related',
                    'rule_id',
                    'emipro_smartproductselector_products',
                    'id'
                ),
                'rule_id',
                $installer->getTable('emipro_smartproductselector_products'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Apply Rule Product ID');
        $installer->getConnection()->createTable($table);

        /**
         *  create table 'emipro_smartproductselector_upsell'
         */

        $table = $installer->getConnection()
            ->newTable($installer->getTable('emipro_smartproductselector_upsell'))
            ->addColumn(
                'upsell_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
                'Upsell ID'
            )
            ->addColumn(
                'pro_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                100,
                [],
                'Product ID'
            )
            ->addColumn(
                'frontpro_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Frontend ID'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Apply Rule Updated At'
            )
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
            )
            ->addIndex(
                $installer->getIdxName(
                    'emipro_smartproductselector_upsell',
                    ['rule_id']
                ),
                ['rule_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'emipro_smartproductselector_upsell',
                    'rule_id',
                    'emipro_smartproductselector_products',
                    'id'
                ),
                'rule_id',
                $installer->getTable('emipro_smartproductselector_products'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Apply Rule Product ID');
        $installer->getConnection()->createTable($table);

        /**
         * create table 'emipro_smartproductselector_crossell'
         */

        $table = $installer->getConnection()
            ->newTable($installer->getTable('emipro_smartproductselector_crossell'))
            ->addColumn(
                'crossell_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'Crossell ID'
            )
            ->addColumn(
                'pro_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                100,
                [],
                'Product ID'
            )
            ->addColumn(
                'frontpro_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Frontend ID'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Apply Rule Updated At'
            )
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
            )
            ->addIndex(
                $installer->getIdxName(
                    'emipro_smartproductselector_crossell',
                    ['rule_id']
                ),
                ['rule_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'emipro_smartproductselector_crossell',
                    'rule_id',
                    'emipro_smartproductselector_products',
                    'id'
                ),
                'rule_id',
                $installer->getTable('emipro_smartproductselector_products'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Apply Rule Product ID');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
