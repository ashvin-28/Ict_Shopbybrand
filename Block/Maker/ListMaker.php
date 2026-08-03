<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Block\Maker;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlFactory;
use Magento\Store\Model\StoreManagerInterface;
use Ict\Shopbybrand\Model\ResourceModel\Maker\CollectionFactory as MakerCollectionFactory;

class ListMaker extends Template
{
    /**
     * @var MakerCollectionFactory
     */
    private $makerCollectionFactory;

    /**
     * @var UrlFactory
     */
    private $urlFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor.
     *
     * @param MakerCollectionFactory $makerCollectionFactory
     * @param UrlFactory $urlFactory
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        MakerCollectionFactory $makerCollectionFactory,
        UrlFactory $urlFactory,
        StoreManagerInterface $storeManager,
        Context $context,
        array $data = []
    ) {
        $this->makerCollectionFactory = $makerCollectionFactory;
        $this->urlFactory = $urlFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Load the makers
     */
    protected function _construct()
    {
        parent::_construct();
        $shopbybrand = $this->getRequest()->getPost("maker-search");
        if ($shopbybrand) {
            /** @var \Ict\Shopbybrand\Model\ResourceModel\Maker\Collection $makers */
            $makers = $this->makerCollectionFactory->create()->addFieldToSelect('*')
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('name', ["like"=>"%".$shopbybrand."%"])
                ->addStoreFilter($this->storeManager->getStore()->getId())
                ->setOrder('name', 'ASC');
                
            $this->setMakers($makers);
        } else {
            /** @var \Ict\Shopbybrand\Model\ResourceModel\Maker\Collection $makers */
            $makers = $this->makerCollectionFactory->create()->addFieldToSelect('*')
                ->addFieldToFilter('is_active', 1)
                ->addStoreFilter($this->storeManager->getStore()->getId())
                ->setOrder('name', 'ASC');
            $this->setMakers($makers);
        }
    }

    /**
     * Get media URL for brands
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ).'ict/shopbybrand/maker/image' ;
    }

    /**
     * Whether the brand search box is enabled via configuration.
     *
     * @return bool
     */
    public function isSearchEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            'ict_shopbybrand/maker/searchshopbybrand',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Whether the alphabetical (A-Z) filter is enabled via configuration.
     *
     * @return bool
     */
    public function isAlphabetEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            'ict_shopbybrand/maker/charshopbybrand',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Whether the layered-navigation brand block (sidebar) is enabled via configuration.
     *
     * @return bool
     */
    public function isLayeredNavEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            'ict_shopbybrand/maker/layershopbybrand',
            ScopeInterface::SCOPE_STORE
        );
    }
}
