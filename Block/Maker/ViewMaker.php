<?php

namespace Ict\Shopbybrand\Block\Maker;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class ViewMaker extends Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->storeManager = $storeManager;

        parent::__construct($context, $data);
    }

    /**
     * Get current maker.
     *
     * @return \Ict\Shopbybrand\Model\Maker
     */
    public function getCurrentMaker()
    {
        return $this->coreRegistry->registry('current_maker');
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
    /**
     * Whether the maker banner/description block is enabled via configuration.
     *
     * @return bool
     */
    public function isBannerEnabled()
    {
        return (bool)$this->_scopeConfig->getValue('ict_shopbybrand/maker/banners');
    }
}
