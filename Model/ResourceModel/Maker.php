<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime as LibDateTime;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Ict\Shopbybrand\Model\Maker as MakerModel;
use Magento\Framework\Event\ManagerInterface;
use Magento\Catalog\Model\Product;
use Ict\Shopbybrand\Model\Maker\Product as MakerProduct;
use Ict\Shopbybrand\Model\Maker\Category as MakerCategory;

class Maker extends AbstractDb
{
    /**
     * @var Store|null
     */
    private $store = null;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LibDateTime
     */
    private $dateTime;

    /**
     * @var string
     */
    private $makerProductTable;

    /**
     * @var string
     */
    private $makerCategoryTable;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var MakerProduct
     */
    private $makerProduct;

    /**
     * @var MakerCategory
     */
    private $makerCategory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param LibDateTime $dateTime
     * @param ManagerInterface $eventManager
     * @param MakerProduct $makerProduct
     * @param MakerCategory $makerCategory
     */
    public function __construct(
        Context $context,
        DateTime $date,
        StoreManagerInterface $storeManager,
        LibDateTime $dateTime,
        ManagerInterface $eventManager,
        MakerProduct $makerProduct,
        MakerCategory $makerCategory
    ) {
        $this->date             = $date;
        $this->storeManager     = $storeManager;
        $this->dateTime         = $dateTime;
        $this->eventManager     = $eventManager;
        $this->makerProduct    = $makerProduct;
        $this->makerCategory   = $makerCategory;
        parent::__construct($context);
        $this->makerProductTable  = $this->getTable('ict_shopbybrand_maker_product');
        // $this->makerCategoryTable = $this->getTable('ict_shopbybrand_maker_category');
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ict_shopbybrand_maker', 'maker_id');
    }

    /**
     * Process maker data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['maker_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('ict_shopbybrand_maker_store'), $condition);
        return parent::_beforeDelete($object);
    }

    /**
     * Before save callback
     *
     * @param AbstractModel|\Ict\Shopbybrand\Model\Maker $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        foreach (['dob'] as $field) {
            $value = !$object->getData($field) ? null : $object->getData($field);
            $object->setData($field, $this->dateTime->formatDate($value));
        }
        $object->setUpdatedAt($this->date->gmtDate());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->gmtDate());
        }
        $urlKey = $object->getData('url_key');
        if ($urlKey == '') {
            $urlKey = $object->getName();
        }
        $urlKey = $object->formatUrlKey($urlKey);
        $object->setUrlKey($urlKey);
        $validKey = false;
        while (!$validKey) {
            if ($this->getIsUniqueMakerToStores($object)) {
                $validKey = true;
            } else {
                $parts = explode('-', $urlKey);
                $last = $parts[count($parts) - 1];
                if (!is_numeric($last)) {
                    $urlKey = $urlKey.'-1';
                } else {
                    $suffix = '-'.($last + 1);
                    unset($parts[count($parts) - 1]);
                    $urlKey = implode('-', $parts).$suffix;
                }
                $object->setData('url_key', $urlKey);
            }
        }
        return parent::_beforeSave($object);
    }

    /**
     * Assign maker to store views
     *
     * @param AbstractModel|\Ict\Shopbybrand\Model\Maker $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveStoreRelation($object);
        $this->saveProductRelation($object);
        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        // $this->saveCategoryRelation($object);
        return parent::_afterSave($object);
    }

    /**
     * Load an object using 'url_key' field if there's no field specified and value is not numeric
     *
     * @param AbstractModel|\Ict\Shopbybrand\Model\Maker $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'url_key';
        }
        return parent::load($object, $value, $field);
    }

    /**
     * Perform operations after object load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Ict\Shopbybrand\Model\Maker $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = [
                Store::DEFAULT_STORE_ID,
                (int)$object->getStoreId()
            ];
            $select->join(
                [
                    'ict_shopbybrand_maker_store' => $this->getTable('ict_shopbybrand_maker_store')
                ],
                $this->getMainTable() . '.maker_id = ict_shopbybrand_maker_store.maker_id',
                []
            )//TODO: check if is_active filter is needed
                ->where('is_active = ?', 1)
                ->where(
                    'ict_shopbybrand_maker_store.store_id IN (?)',
                    $storeIds
                )
                ->order('ict_shopbybrand_maker_store.store_id DESC')
                ->limit(1);
        }
        return $select;
    }

    /**
     * Retrieve load select with filter by url_key, store and activity
     *
     * @param string $urlKey
     * @param int|array $store
     * @param int $isActive
     * @return \Magento\Framework\DB\Select
     */
    protected function getLoadByUrlKeySelect($urlKey, $store, $isActive = null)
    {
        $select = $this->getConnection()
            ->select()
            ->from(['maker' => $this->getMainTable()])
            ->join(
                ['maker_store' => $this->getTable('ict_shopbybrand_maker_store')],
                'maker.maker_id = maker_store.maker_id',
                []
            )
            ->where(
                'maker.url_key = ?',
                $urlKey
            )
            ->where(
                'maker_store.store_id IN (?)',
                $store
            );
        if ($isActive !== null) {
            $select->where('maker.is_active = ?', $isActive);
        }
        return $select;
    }

    /**
     * Check if maker url_key exist, return maker id if maker exists
     *
     * @param string $urlKey
     * @param int $storeId
     * @return int
     */
    public function checkUrlKey($urlKey, $storeId)
    {
        $stores = [Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->getLoadByUrlKeySelect($urlKey, $stores, 1);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('maker.maker_id')
            ->order('maker_store.store_id DESC')
            ->limit(1);
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieves maker name from DB by passed url key.
     *
     * @param string $urlKey
     * @return string|bool
     */
    public function getMakerNameByUrlKey($urlKey)
    {
        $stores = [Store::DEFAULT_STORE_ID];
        if ($this->store) {
            $stores[] = (int)$this->getStore()->getId();
        }
        $select = $this->getLoadByUrlKeySelect($urlKey, $stores);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('maker.name')
            ->order('maker.store_id DESC')
            ->limit(1);
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieves maker name from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    public function getMakerNameById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('maker_id = :maker_id');
        $binds = ['maker_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }

    /**
     * Retrieves maker url key from DB by passed id.
     *
     * @param int $id
     * @return string|bool
     */
    public function getMakerUrlKeyById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'url_key')
            ->where('maker_id = :maker_id');
        $binds = ['maker_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $makerId
     * @return array
     */
    public function lookupStoreIds($makerId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->getTable('ict_shopbybrand_maker_store'),
            'store_id'
        )
            ->where(
                'maker_id = ?',
                (int)$makerId
            );
        return $adapter->fetchCol($select);
    }

    /**
     * Set store model
     *
     * @param Store $store
     * @return $this
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->store);
    }

    /**
     * Check if url key is unique
     *
     * @param AbstractModel|\Ict\Shopbybrand\Model\Maker $object
     * @return bool
     */
    public function getIsUniqueMakerToStores(AbstractModel $object)
    {
        if ($this->storeManager->hasSingleStore() || !$object->hasStores()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('stores');
        }
        $select = $this->getLoadByUrlKeySelect($object->getData('url_key'), $stores);
        if ($object->getId()) {
            $select->where('maker_store.maker_id <> ?', $object->getId());
        }
        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }
        return true;
    }

    /**
     * Get product positions for a maker
     *
     * @param MakerModel $maker
     * @return array
     */
    public function getProductsPosition(MakerModel $maker)
    {
        $select = $this->getConnection()->select()->from(
            $this->makerProductTable,
            ['product_id', 'position']
        )
        ->where(
            'maker_id = :maker_id'
        );
        $bind = ['maker_id' => (int)$maker->getId()];
        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * Save maker to store relation
     *
     * @param MakerModel $maker
     * @return $this
     */
    protected function saveStoreRelation(MakerModel $maker)
    {
        $oldStores = $this->lookupStoreIds($maker->getId());
        $newStores = (array)$maker->getStores();
        if (empty($newStores)) {
            $newStores = (array)$maker->getStoreId();
        }
        $table = $this->getTable('ict_shopbybrand_maker_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = [
                'maker_id = ?' => (int)$maker->getId(),
                'store_id IN (?)' => $delete
            ];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    'maker_id' => (int)$maker->getId(),
                    'store_id' => (int)$storeId
                ];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
        return $this;
    }

    /**
     * Save maker to product relation
     *
     * @param MakerModel $maker
     * @return $this
     */
    protected function saveProductRelation(MakerModel $maker)
    {
        $maker->setIsChangedProductList(false);
        $id = $maker->getId();
        $products = $maker->getProductsData();

        if ($products === null) {
            return $this;
        }
        $oldProducts = $maker->getProductsPosition();
        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);
        $update = array_intersect_key($products, $oldProducts);
        $_update = [];
        foreach ($update as $key => $settings) {
            if (isset($oldProducts[$key]) && isset($settings['position']) &&
                 $oldProducts[$key] != $settings['position']
            ) {
                $_update[$key] = $settings;
            }
        }
        $update = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['product_id IN(?)' => array_keys($delete), 'maker_id=?' => $id];
            $adapter->delete($this->makerProductTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $productId => $position) {
                $data[] = [
                    'maker_id' => (int)$id,
                    'product_id' => (int)$productId,
                    'position' => (int)$position
                ];
            }
            $adapter->insertMultiple($this->makerProductTable, $data);
        }

        if (!empty($update)) {
            foreach ($update as $productId => $position) {
                $where = ['maker_id = ?' => (int)$id, 'product_id = ?' => (int)$productId];
                $bind = ['position' => (int)$position['position']];
                $adapter->update($this->makerProductTable, $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $productIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'ict_shopbybrand_maker_change_products',
                ['maker' => $maker, 'product_ids' => $productIds]
            );
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $maker->setIsChangedProductList(true);
            $productIds = array_keys($insert + $delete + $update);
            $maker->setAffectedProductIds($productIds);
        }
        return $this;
    }

    /**
     * Save product to maker relation
     *
     * @param Product $product
     * @param array $makers
     * @return $this
     */
    public function saveMakerProductRelation(Product $product, $makers)
    {
        $product->setIsChangedMakerList(false);
        $id = $product->getId();
        if ($makers === null) {
            return $this;
        }
        $oldMakerObjects = $this->makerProduct->getSelectedMakers($product);
        if (!is_array($oldMakerObjects)) {
            $oldMakerObjects = [];
        }
        $oldMakers = [];
        foreach ($oldMakerObjects as $maker) {
            /** @var \Ict\Shopbybrand\Model\Maker $maker */
            $oldMakers[$maker->getId()] = ['position' => $maker->getPosition()];
        }
        $insert = array_diff_key($makers, $oldMakers);

        $delete = array_diff_key($oldMakers, $makers);

        $update = array_intersect_key($makers, $oldMakers);
        $toUpdate = [];
        foreach ($update as $productId => $values) {
            if (isset($oldMakers[$productId]) && $oldMakers[$productId]['position'] != $values['position']) {
                $toUpdate[$productId] = [];
                $toUpdate[$productId]['position'] = $values['position'];
            }
        }

        $update = $toUpdate;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['maker_id IN(?)' => array_keys($delete), 'product_id=?' => $id];
            $adapter->delete($this->makerProductTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $makerId => $position) {
                $data[] = [
                    'product_id' => (int)$id,
                    'maker_id' => (int)$makerId,
                    'position' => (int)$position['position']
                ];
            }
            $adapter->insertMultiple($this->makerProductTable, $data);
        }

        if (!empty($update)) {
            foreach ($update as $makerId => $position) {
                $where = ['product_id = ?' => (int)$id, 'maker_id = ?' => (int)$makerId];
                $bind = ['position' => (int)$position['position']];
                $adapter->update($this->makerProductTable, $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $makerIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'ict_shopbybrand_product_change_makers',
                ['product' => $product, 'maker_ids' => $makerIds]
            );
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $product->setIsChangedMakerList(true);
            $makerIds = array_keys($insert + $delete + $update);
            $product->setAffectedMakerIds($makerIds);
        }
        return $this;
    }

    // protected function saveCategoryRelation(MakerModel $maker)
    // {
    //     $maker->setIsChangedCategoryList(false);
    //     $id = $maker->getId();
    //     $categories = $maker->getCategoriesIds();

    //     if ($categories === null) {
    //         return $this;
    //     }
    //     $oldCategoryIds = $maker->getCategoryIds();
    //     $insert = array_diff_key($categories, $oldCategoryIds);
    //     $delete = array_diff_key($oldCategoryIds, $categories);

    //     $adapter = $this->getConnection();
    //     if (!empty($delete)) {
    //         $condition = array('category_id IN(?)' => $delete, 'maker_id=?' => $id);
    //         $adapter->delete($this->makerCategoryTable, $condition);
    //     }
    //     if (!empty($insert)) {
    //         $data = array();
    //         foreach ($insert as $categoryId) {
    //             $data[] = array(
    //                 'maker_id' => (int)$id,
    //                 'category_id' => (int)$categoryId,
    //                 'position' => 1
    //             );
    //         }
    //         $adapter->insertMultiple($this->makerCategoryTable, $data);
    //     }

    //     if (!empty($insert) || !empty($delete)) {
    //         $categoryIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
    //         $this->eventManager->dispatch(
    //             'ict_shopbybrand_maker_change_categories',
    //             array('maker' => $maker, 'category_ids' => $categoryIds)
    //         );
    //     }

    //     if (!empty($insert) /*|| !empty($update)*/ || !empty($delete)) {
    //         $maker->setIsChangedCategoryList(true);
    //         $categoryIds = array_keys($insert + $delete /* + $update*/);
    //         $maker->setAffectedCategoryIds($categoryIds);
    //     }
    //     return $this;
    // }

    /**
     * @param MakerModel $maker
     * @return array
     */
    // public function getCategoryIds(MakerModel $maker)
    // {
    //     $adapter = $this->getConnection();
    //     $select = $adapter->select()->from(
    //         $this->makerCategoryTable,
    //         'category_id'
    //     )
    //     ->where(
    //         'maker_id = ?',
    //         (int)$maker->getId()
    //     );
    //     return $adapter->fetchCol($select);
    // }

    /**
     * Save category to maker relation
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param array $makers
     * @return $this
     */
    public function saveMakerCategoryRelation($category, $makers)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category->setIsChangedMakerList(false);
        $id = $category->getId();
        if ($makers === null) {
            return $this;
        }
        $oldMakerObjects = $this->makerCategory->getSelectedMakers($category);
        if (!is_array($oldMakerObjects)) {
            $oldMakerObjects = [];
        }
        $oldMakers = [];
        foreach ($oldMakerObjects as $maker) {
            /** @var \Ict\Shopbybrand\Model\Maker $maker */
            $oldMakers[$maker->getId()] = $maker->getPosition();
        }
        $insert = array_diff_key($makers, $oldMakers);
        $delete = array_diff_key($oldMakers, $makers);
        $update = array_intersect_key($makers, $oldMakers);
        $update = array_diff_assoc($update, $oldMakers);

        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['maker_id IN(?)' => array_keys($delete), 'maker_id=?' => $id];
            $adapter->delete($this->makerCategoryTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $makerId => $position) {
                $data[] = [
                    'category_id' => (int)$id,
                    'maker_id' => (int)$makerId,
                    'position' => (int)$position
                ];
            }
            $adapter->insertMultiple($this->makerCategoryTable, $data);
        }

        if (!empty($update)) {
            foreach ($update as $makerId => $position) {
                $where = ['category_id = ?' => (int)$id, 'maker_id = ?' => (int)$makerId];
                $bind = ['position' => (int)$position];
                $adapter->update($this->makerCategoryTable, $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $makerIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'ict_shopbybrand_category_change_makers',
                ['category' => $category, 'maker_ids' => $makerIds]
            );
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $category->setIsChangedMakerList(true);
            $makerIds = array_keys($insert + $delete + $update);
            $category->setAffectedMakerIds($makerIds);
        }
        return $this;
    }
}
