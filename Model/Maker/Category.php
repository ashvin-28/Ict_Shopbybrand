<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Model\Maker;

use Ict\Shopbybrand\Model\ResourceModel\Maker\CollectionFactory;
use Magento\Catalog\Model\Category as CategoryModel;

class Category
{
    /**
     * @var CollectionFactory
     */
    private $makerCollectionFactory;

    /**
     * @param CollectionFactory $makerCollectionFactory
     */
    public function __construct(
        CollectionFactory $makerCollectionFactory
    ) {
        $this->makerCollectionFactory = $makerCollectionFactory;
    }

    /**
     * Get selected makers for a category.
     *
     * @access public
     * @param \Magento\Catalog\Model\Category $category
     * @return mixed
     */
    public function getSelectedMakers(CategoryModel $category)
    {
        if (!$category->hasSelectedMakers()) {
            $makers = [];
            foreach ($this->getSelectedMakersCollection($category) as $maker) {
                $makers[] = $maker;
            }
            $category->setSelectedMakers($makers);
        }
        return $category->getData('selected_makers');
    }

    /**
     * Get selected makers collection for a category.
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Ict\Shopbybrand\Model\ResourceModel\Maker\Collection
     */
    public function getSelectedMakersCollection(CategoryModel $category)
    {
        $collection = $this->makerCollectionFactory->create()->addFieldToFilter(
            "categories_ids",
            ["like" => "%".$category->getId()."%"]
        );
        return $collection;
    }
}
