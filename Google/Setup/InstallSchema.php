<?php

namespace Project\Google\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /**
         * Create table 'pfay_contacts'
         */

        if (!$setup->getConnection()->isTableExists($setup->getTable('project_google_keyword'))) {
            $table = $setup->getConnection()
                           ->newTable($setup->getTable('project_google_keyword'))
                           ->addColumn(
                                       'keyword_id',
                                       \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                       null,
                                       ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                                       'Keyword ID'
                                       )
                           ->addColumn(
                                       'name',
                                       \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                       100,
                                       ['nullable' => false, 'default' => 'simple'],
                                       'Name'
                                       )
                ->setComment('Google Keyword Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');

            $setup->getConnection()->createTable($table);
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('project_google_blacklist'))) {
            $table = $setup->getConnection()
                           ->newTable($setup->getTable('project_google_blacklist'))
                           ->addColumn(
                                       'blacklist_id',
                                       \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                       null,
                                       ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                                       'Blacklist ID'
                                       )
                           ->addColumn(
                                       'sku',
                                       \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                       100,
                                       ['nullable' => false, 'default' => 'simple'],
                                       'SkU'
                                       )
                           ->addColumn(
                                       'name',
                                       \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                       100,
                                       ['nullable' => false, 'default' => 'simple'],
                                       'Name'
                                       )
                ->setComment('Google Blacklist Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');

            $setup->getConnection()->createTable($table);
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('project_google_map'))) {
            $table = $setup->getConnection()
                           ->newTable($setup->getTable('project_google_map'))
                           ->addColumn(
                                       'catagory_id',
                                       \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                       100,
                                       ['nullable' => false, 'default' => 'simple'],
                                       'SkU'
                                       )
                           ->addColumn(
                                       'google_catagory',
                                       \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                       100,
                                       ['nullable' => false, 'default' => 'simple'],
                                       'Name'
                                       )
                ->setComment('Google Catagory Mapping Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');

            $setup->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
