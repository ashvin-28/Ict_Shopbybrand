<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Install schema
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (!$installer->tableExists('ict_shopbybrand_maker')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('ict_shopbybrand_maker'));
            $table->addColumn(
                'maker_id',
                Table::TYPE_INTEGER,
                null,
                [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                'Maker ID'
            )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable'  => false,],
                    'Maker Name'
                )
                ->addColumn(
                    'url_key',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable'  => false,],
                    'Maker Url Key'
                )
                ->addColumn(
                    'banner_text',
                    Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Banner Text'
                )
                ->addColumn(
                    'avatar',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Maker Avatar'
                )
                ->addColumn(
                    'logobanner',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Logo Banner'
                )
                ->addColumn(
                    'is_active',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable'  => false,
                        'default'   => '1',
                    ],
                    'Is Maker Active'
                )
                ->addColumn(
                    'is_featured',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable'  => false,
                        'default'   => '0',
                    ],
                    'Is Featured'
                )
                ->addColumn(
                    'categories_ids',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Categories Ids'
                )
                ->setComment('Shopbybrand makers');
            $installer->getConnection()->createTable($table);

        }

        // Create Makers to Store table
        if (!$installer->tableExists('ict_shopbybrand_maker_store')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('ict_shopbybrand_maker_store'));
            $table->addColumn(
                'maker_id',
                Table::TYPE_INTEGER,
                null,
                [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'   => true,
                    ],
                'Maker ID'
            )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned'  => true,
                        'nullable'  => false,
                        'primary'   => true,
                    ],
                    'Store ID'
                )
                ->addIndex(
                    $installer->getIdxName('ict_shopbybrand_maker_store', ['store_id']),
                    ['store_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'ict_shopbybrand_maker_store',
                        'maker_id',
                        'ict_shopbybrand_maker',
                        'maker_id'
                    ),
                    'maker_id',
                    $installer->getTable('ict_shopbybrand_maker'),
                    'maker_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('ict_shopbybrand_maker_store', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Maker To Store Link Table');
            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('ict_shopbybrand_maker_product')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('ict_shopbybrand_maker_product'));
            $table->addColumn(
                'maker_id',
                Table::TYPE_INTEGER,
                null,
                [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'   => true,
                    ],
                'Maker ID'
            )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'   => true,
                    ],
                    'Product ID'
                )
                ->addColumn(
                    'position',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'default' => '0'
                    ],
                    'Position'
                )
                ->addIndex(
                    $installer->getIdxName('ict_shopbybrand_maker_product', ['product_id']),
                    ['product_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'ict_shopbybrand_maker_product',
                        'maker_id',
                        'ict_shopbybrand_maker',
                        'maker_id'
                    ),
                    'maker_id',
                    $installer->getTable('ict_shopbybrand_maker'),
                    'maker_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'ict_shopbybrand_maker_product',
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName(
                        'ict_shopbybrand_maker_product',
                        [
                            'maker_id',
                            'product_id'
                        ],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'maker_id',
                        'product_id'
                    ],
                    [
                        'type' => AdapterInterface::INDEX_TYPE_UNIQUE
                    ]
                )
                ->setComment('Maker To product Link Table');
            $installer->getConnection()->createTable($table);
        }
    }
}
