<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Block\Catalog\Product;

use Ict\Shopbybrand\Model\Maker;
use Ict\Shopbybrand\Model\Maker\Product as MakerProduct;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\StoreManagerInterface;

class ListMaker extends Template
{

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var MakerProduct
     */
    private $makerProduct;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

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
     * @param MakerProduct $makerProduct
     * @param Registry $registry
     * @param BlockFactory $blockFactory
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        MakerProduct $makerProduct,
        Registry $registry,
        BlockFactory $blockFactory,
        StoreManagerInterface $storeManager,
        Context $context,
        array $data = []
    ) {
        $this->makerProduct = $makerProduct;
        $this->registry = $registry;
        $this->blockFactory = $blockFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
        $this->setTabTitle();
    }

    /**
     * Get the collection of makers selected for the current product.
     *
     * @return \Ict\Shopbybrand\Model\ResourceModel\Maker\Collection
     */
    public function getMakerCollection()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
        if (is_null($this->makerCollection)) {
            $collection = $this->makerProduct->getSelectedMakersCollection($this->getProduct());
            $collection->addStoreFilter($this->storeManager->getStore()->getId());
            $collection->addFieldToFilter('is_active', Maker::STATUS_ENABLED);
            $collection->getSelect()->order('position');
            $this->makerCollection = $collection;
        }
        return $this->makerCollection;
    }

    /**
     * Get the current product from the registry.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Prepare the layout by adding the maker list pager block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        /** @var \Magento\Theme\Block\Html\Pager $pager */
        $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class);
        $pager->setNameInLayout('ict_shopbybrand.maker.list.pager');
        $pager->setPageVarName('p-maker');
        $pager->setLimitVarName('l-maker');
        $pager->setFragment('catalog.product.list.ict.shopbybrand.maker');
        $pager->setCollection($this->getMakerCollection());
        $this->setChild('pager', $pager);
        return parent::_prepareLayout();
    }

    /**
     * Get the rendered HTML of the pager child block.
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Set the tab title shown on the product page, including the maker count.
     *
     * @return $this
     */
    public function setTabTitle()
    {
        $title = $this->getCollectionSize()
            ? __('Shopbybrand %1', '<span class="counter">' . $this->getCollectionSize() . '</span>')
            : __('Shopbybrand');
        $this->setTitle($title);
        return $this;
    }

    /**
     * Get the number of makers in the collection.
     *
     * @return int
     */
    public function getCollectionSize()
    {
        return $this->getMakerCollection()->getSize();
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
        ).'ict/shopbybrand/maker/image' ;
    }
}
