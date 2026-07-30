<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Ict\Shopbybrand\Model\Maker\Url;
use Ict\Shopbybrand\Model\Maker\Source\IsActive;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Ict\Shopbybrand\Model\ResourceModel\Maker\CollectionFactory as MakerCollectionFactory;

class Maker extends AbstractModel
{
    public const STATUS_ENABLED = 1;

    public const STATUS_DISABLED = 0;

    public const CACHE_TAG = 'ict_shopbybrand_maker';

    /**
     * @var Url
     */
    private $urlModel;

    /**
     * @var string
     */
    protected $_cacheTag = 'ict_shopbybrand_maker';

    /**
     * @var string
     */
    protected $eventPrefix = 'ict_shopbybrand_maker';

    /**
     * @var FilterManager
     */
    private $filter;

    /**
     * @var IsActive
     */
    private $statusList;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $productCollection;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    private $categoryCollection;

    /**
     * @var MakerCollectionFactory
     */
    private $makerCollectionFactory;

    /**
     * Constructor
     *
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param MakerCollectionFactory $makerCollectionFactory
     * @param FilterManager $filter
     * @param Url $urlModel
     * @param IsActive $statusList
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        MakerCollectionFactory $makerCollectionFactory,
        FilterManager $filter,
        Url $urlModel,
        IsActive $statusList,
        Context $context,
        Registry $registry,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->makerCollectionFactory    = $makerCollectionFactory;
        $this->filter                    = $filter;
        $this->urlModel                  = $urlModel;
        $this->statusList                = $statusList;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Ict\Shopbybrand\Model\ResourceModel\Maker::class);
    }

    /**
     * Check if maker url key exists, return maker id if maker exists
     *
     * @param string $urlKey
     * @param int $storeId
     * @return int
     */
    public function checkUrlKey($urlKey, $storeId)
    {
        return $this->_getResource()->checkUrlKey($urlKey, $storeId);
    }

    /**
     * Prepare maker's statuses.
     *
     * @return array options
     */
    public function getAvailableStatuses()
    {
        return $this->statusList->getOptions();
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get default maker values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        return [
            'is_active' => self::STATUS_ENABLED
        ];
    }

    /**
     * Sanitize the url key
     *
     * @param string $string
     * @return string
     */
    public function formatUrlKey($string)
    {
        return $this->filter->translitUrl($string);
    }

    /**
     * Get maker URL
     *
     * @return mixed
     */
    public function getMakerUrl()
    {
        return $this->urlModel->getMakerUrl($this);
    }

    /**
     * Check if maker is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getIsActive();
    }

    /**
     * Get products position
     *
     * @return array|mixed
     */
    public function getProductsPosition()
    {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('products_position');
        if ($array === null) {
            $array = $this->getResource()->getProductsPosition($this);
            $this->setData('products_position', $array);
        }
        return $array;
    }

    /**
     * Get selected products collection
     *
     * @param string $attributes
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getSelectedProductsCollection($attributes = '*')
    {
        if ($this->productCollection === null) {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect($attributes);
            $collection->joinField(
                'position',
                'ict_shopbybrand_maker_product',
                'position',
                'product_id=entity_id',
                '{{table}}.maker_id='.$this->getId(),
                'inner'
            );
            $this->productCollection = $collection;
        }
        return $this->productCollection;
    }

    /**
     * Get category ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $ids = $this->_getResource()->getCategoryIds($this);
            $this->setData('category_ids', $ids);
        }
        return (array) $this->_getData('category_ids');
    }

    /**
     * Get selected categories collection
     *
     * @param string $attributes
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getSelectedCategoriesCollection($attributes = '*')
    {
        if ($this->categoryCollection === null) {
            $collection = $this->categoryCollectionFactory->create();
            $collection->addAttributeToSelect($attributes);
            // $collection->joinField(
            //     'position',
            //     'ict_shopbybrand_maker_category',
            //     'position',
            //     'category_id=entity_id',
            //     '{{table}}.maker_id='.$this->getId(),
            //     'inner'
            // );
            $this->categoryCollection = $collection;
        }
        return $this->categoryCollection;
    }
}
