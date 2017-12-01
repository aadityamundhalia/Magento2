<?php

namespace Project\Dhl\Setup;

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

        if (!$setup->getConnection()->isTableExists($setup->getTable('zdhl_orders'))) {
            $table = $setup->getConnection()
                           ->newTable($setup->getTable('zdhl_orders'))
                           ->addColumn(
                                       'zdhl_id',
                                       \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                       null,
                                       ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                                       'Zdhl ID'
                                       )
                           ->addColumn(
                               'totalValue',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Total Value'
                           )
                           ->addColumn(
                               'countryCode',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Country Code'
                           )
                           ->addColumn(
                               'email',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Email'
                           )
                           ->addColumn(
                               'phone',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Phone'
                           )
                           ->addColumn(
                               'postcode',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Postcode'
                           )
                           ->addColumn(
                               'state',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'State'
                           )
                           ->addColumn(
                               'city',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'City'
                           )
                           ->addColumn(
                               'address2',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Address 2'
                           )
                           ->addColumn(
                               'address1',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Address 1'
                           )
                           ->addColumn(
                               'name',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Name'
                           )
                           ->addColumn(
                               'orderNo',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Order No'
                           )
                           ->addColumn(
                               'weight',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Weight'
                           )
                           ->addColumn(
                               'productCode',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Product Code'
                           )
                           ->addColumn(
                               'fileCreated',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'File Created'
                           )
                           ->addColumn(
                               'id',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Id'
                           )
                           ->addColumn(
                               'createdAt',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Created At'
                           )
                           ->addColumn(
                               'fileCreatedAt',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'File Created At'
                           )
                           ->addColumn(
                               'packageNo',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               1000,
                               ['nullable' => true, 'default' => ''],
                               'Package No'
                           )
                ->setComment('DHL Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');

            $setup->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
