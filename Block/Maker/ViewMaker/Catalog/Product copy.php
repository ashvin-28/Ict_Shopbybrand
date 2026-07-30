<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Block\Maker\ViewMaker\Catalog;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Url\Helper\Data as UrlHelper;

class Product extends ListProduct
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var Visibility
     */
    private $productVisibility;

    /**
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    private $productCollection;

    /**
     * Constructor.
     *
     * @param Visibility $productVisibility
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UrlHelper $urlHelper
     * @param array $data
     */
    public function __construct(
        Visibility $productVisibility,
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        UrlHelper $urlHelper,
        array $data = []
    ) {
        $this->productVisibility = $productVisibility;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
        $this->setTabTitle();
    }

    /**
     * Get the current maker from the registry.
     *
     * @return \Ict\Shopbybrand\Model\Maker
     */
    public function getMaker()
    {
        return $this->coreRegistry->registry('current_maker');
    }

    /**
     * Get the product collection for the current maker.
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getProductCollection()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
        if (is_null($this->productCollection)) {
            $collection = $this->getMaker()->getSelectedProductsCollection()
                ->setStore($this->_storeManager->getStore())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addStoreFilter()
                ->addUrlRewrite()
                ->setVisibility($this->productVisibility->getVisibleInCatalogIds());
            $collection->getSelect()->order('position');
            $this->productCollection = $collection;
        }
        return $this->productCollection;
    }

    /**
     * Set the tab title, including the product count.
     *
     * @return $this
     */
    public function setTabTitle()
    {
        $title = $this->getCollectionSize()
            ? __('Products %1', '<span class="counter">' . $this->getCollectionSize() . '</span>')
            : __('Products');
        $this->setTitle($title);
        return $this;
    }

    /**
     * Get the number of products in the collection.
     *
     * @return int
     */
    public function getCollectionSize()
    {
        return $this->_getProductCollection()->getSize();
    }

    /**
     * Set the pager fragment before rendering the toolbar.
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        /** @var \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar */
        $toolbar = $this->getChildBlock('toolbar');
        /** @var \Magento\Theme\Block\Html\Pager $pager */
        $pager = $toolbar->getChildBlock('product_list_toolbar_pager');
        $pager->setFragment('ict_shopbybrand.maker.view.product');
        return $this;
    }
}
