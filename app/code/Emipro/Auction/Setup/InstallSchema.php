<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Auction\Setup;

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

        $installer->getConnection()->dropTable($installer->getTable('emipro_auction'));
        $installer->getConnection()->dropTable($installer->getTable('emipro_auction_customer'));
        $installer->getConnection()->dropTable($installer->getTable('emipro_auction_bid'));

        /* Create table emipro_auction  */

        $table = $installer->getConnection()->newTable(
            $installer->getTable('emipro_auction')
        )->addColumn(
            'auction_id',
            Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Auction ID'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false],
            'Product Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Auction Title'
        )->addColumn(
            'min_price',
            Table::TYPE_DECIMAL,
            '12,2',
            ['nullable' => false, 'default' => '0.00'],
            'Minimum Price'
        )->addColumn(
            'reserved_price',
            Table::TYPE_DECIMAL,
            '12,2',
            ['nullable' => false, 'default' => '0.00'],
            'Reserved Price'
        )->addColumn(
            'min_price_gap',
            Table::TYPE_DECIMAL,
            '12,2',
            ['nullable' => false, 'default' => '0.00'],
            'Min Price Gap'
        )->addColumn(
            'max_price_gap',
            Table::TYPE_DECIMAL,
            '12,2',
            ['nullable' => true, 'default' => '0.00'],
            'Max Price Gap'
        )->addColumn(
            'auto_extend',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Auto Extend'
        )->addColumn(
            'auto_extend_time',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true, 'default' => '0'],
            'Auction Auto Extend Time'
        )->addColumn(
            'auto_extend_time_left',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true, 'default' => '0'],
            'Auction Auto Extend Time Left'
        )->addColumn(
            'start_time',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Auction starting time'
        )->addColumn(
            'end_time',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Auction ending time'
        )->addColumn(
            'created_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created Time'
        )->addColumn(
            'updated_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated Time'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Auction status'
        )->addColumn(
            'winner_customer_id',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false],
            'Winner Customer ID'
        )->addColumn(
            'customer_group_ids',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Customer Group ID'
        )->addIndex(
            $installer->getIdxName('emipro_auction', ['auction_id']),
            ['start_time']
        )->addIndex(
            $installer->getIdxName('emipro_auction', ['product_id']),
            ['start_time']
        )->addIndex(
            $installer->getIdxName('emipro_auction', ['status']),
            ['start_time']
        );
        $installer->getConnection()->createTable($table);

        /* End create table emipro_auction */

        /* Create table emipro_auction_customer */

        $table = $installer->getConnection()->newTable(
            $installer->getTable('emipro_auction_customer')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false],
            'Customer ID'
        )->addColumn(
            'watch_list',
            Table::TYPE_TEXT,
            2047,
            ['nullable' => true, 'default' => ''],
            'Watch list'
        );
        $installer->getConnection()->createTable($table);

        /* End create table emipro_auction_customer */

        /* Create table emipro_auction_bid */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('emipro_auction_bid')
        )->addColumn(
            'bid_id',
            Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Bid ID'
        )->addColumn(
            'auction_id',
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Auction ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Customer ID'
        )->addColumn(
            'bid_amount',
            Table::TYPE_DECIMAL,
            '16,2',
            ['nullable' => false, 'default' => '0.00'],
            'Bid Amount'
        )->addColumn(
            'created_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Bid Time'
        )->addIndex(
            $installer->getIdxName('emipro_auction_bid', ['bid_id']),
            ['bid_id']
        )->addIndex(
            $installer->getIdxName('emipro_auction_bid', ['auction_id']),
            ['auction_id']
        )->addIndex(
            $installer->getIdxName('emipro_auction_bid', ['customer_id']),
            ['customer_id']
        )->addForeignKey(
            $installer->getFkName(
                'emipro_auction_bid',
                'auction_id',
                'emipro_auction',
                'auction_id'
            ),
            'auction_id',
            $installer->getTable('emipro_auction'),
            'auction_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        /* End create table emipro_auction_bid */

        $installer->endSetup();
    }
}
