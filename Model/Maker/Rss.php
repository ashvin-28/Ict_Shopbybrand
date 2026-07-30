<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Model\Maker;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Rss
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Check whether RSS feed for makers is enabled.
     *
     * @return bool
     */
    public function isRssEnabled()
    {
        return
            $this->scopeConfig->getValue('rss/config/active', ScopeInterface::SCOPE_STORE) &&
            $this->scopeConfig->getValue('ict_shopbybrand/maker/rss', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get maker RSS feed link.
     *
     * @return string
     */
    public function getRssLink()
    {
        return $this->urlBuilder->getUrl(
            'ict_shopbybrand/maker/rss',
            ['store' => $this->storeManager->getStore()->getId()]
        );
    }
}
