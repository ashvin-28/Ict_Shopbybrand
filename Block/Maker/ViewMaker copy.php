<?php

namespace Ict\Shopbybrand\Block\Maker;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

class ViewMaker extends Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
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
        ).'ict/shopbybrand/maker/image' ;
    }
}
