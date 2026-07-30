<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Block\Catalog\Category;

use Magento\Framework\View\Element\Template;
use Ict\Shopbybrand\Model\Maker\Category as CategoryModel;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class ListMaker extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CategoryModel
     */
    private $categoryModel;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Ict\Shopbybrand\Model\ResourceModel\Maker\Collection
     */
    private $makerCollection;

    /**
     * Constructor.
     *
     * @param CategoryModel $categoryModel
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        CategoryModel $categoryModel,
        Registry $registry,
        StoreManagerInterface $storeManager,
        Context $context,
        array $data = []
    ) {
        $this->categoryModel = $categoryModel;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Get the collection of makers selected for the current category.
     *
     * @return \Ict\Shopbybrand\Model\ResourceModel\Maker\Collection
     */
    public function getMakerCollection()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
        if (is_null($this->makerCollection)) {
            $this->makerCollection = $this->categoryModel
                ->getSelectedMakersCollection($this->getCategory())
                ->addStoreFilter($this->storeManager->getStore()->getId())
                ->addFieldToFilter('is_active', 1);//TODO: use constant here
            // $this->makerCollection->getSelect()->order('related_category.position');
        }

        return $this->makerCollection;
    }

    /**
     * Get the current category from the registry.
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Get the anchor name used for the category maker list layout block.
     *
     * @return string
     */
    public function getAnchorName()
    {
        return 'catalog.category.list.ict.shopbybrand.maker';
    }

    /**
     * Get the media URL for brand images.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . 'ict/shopbybrand/maker/image';
    }
}
