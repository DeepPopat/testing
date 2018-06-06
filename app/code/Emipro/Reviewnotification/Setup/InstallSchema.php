<?php
namespace Emipro\Reviewnotification\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('emipro_review_notification_log'))
            ->addColumn('id', Table::TYPE_SMALLINT, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'News ID')
            ->addColumn('numberof_review', Table::TYPE_SMALLINT, null, ['nullable' => false], 'Item ID')
            ->setComment('Emipro Review Notification');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
