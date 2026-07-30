<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Model\Maker;

use Ict\Shopbybrand\Model\ResourceModel\Maker\CollectionFactory;
use Magento\Catalog\Model\Product as ProductModel;

class Product
{
    /**
     * @var CollectionFactory
     */
    private $makerCollectionFactory;

    /**
     * @param CollectionFactory $makerCollectionFactory
     */
    public function __construct(CollectionFactory $makerCollectionFactory)
    {
        $this->makerCollectionFactory = $makerCollectionFactory;
    }

    /**
     * Get selected makers for a product.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Ict\Shopbybrand\Model\Maker[]
     */
    public function getSelectedMakers(ProductModel $product)
    {
        if (!$product->hasSelectedMakers()) {
            $makers = [];
            foreach ($this->getSelectedMakersCollection($product) as $maker) {
                $makers[] = $maker;
            }
            $product->setSelectedMakers($makers);
        }
        return $product->getData('selected_makers');
    }

    /**
     * Get selected makers collection for a product.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Ict\Shopbybrand\Model\ResourceModel\Maker\Collection
     */
    public function getSelectedMakersCollection(ProductModel $product)
    {
        $collection = $this->makerCollectionFactory->create()
            ->addProductFilter($product);
        return $collection;
    }
}
