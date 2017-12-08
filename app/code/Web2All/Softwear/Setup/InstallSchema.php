<?php
namespace Web2All\Softwear\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        
        $table = $setup->getConnection()->newTable($setup->getTable('web2all_softwear_synclog'));
        $table->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        );
        $table->addColumn(
            'has_errors',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['default' => false, 'nullable' => false],
            'Sync had errors'
        );
        $table->addColumn(
            'is_completed',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['default' => false, 'nullable' => false],
            'Sync processed all products'
        );
        $table->addColumn(
            'num_products_updated',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Number of updated products'
        );
        $table->addColumn(
            'num_products_processed',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Number of updated products'
        );
        $table->addColumn(
            'log_data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['default' => null, 'nullable' => false],
            'Logging data (warnings/errors)'
        );
        $table->addColumn(
            'start_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Sync start time'
        );
        $table->addColumn(
            'end_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
            'Sync end time'
        );
        $setup->getConnection()->createTable($table);
        
        $table = $setup->getConnection()->newTable($setup->getTable('web2all_softwear_syncstate'));
        $table->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        );
        $table->addColumn(
            'is_running',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['default' => false, 'nullable' => false],
            'Sync is running'
        );
        $table->addColumn(
            'start_beginning',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['default' => false, 'nullable' => false],
            'The next sync has to start from the beginning'
        );
        $table->addColumn(
            'start_page',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'At which page should the next Sync start'
        );
        $table->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Last state update'
        );
        $setup->getConnection()->createTable($table);
        
        $setup->endSetup();
    }
}
