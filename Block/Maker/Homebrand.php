<?php
namespace Ict\Shopbybrand\Block\Maker;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlFactory;
use Ict\Shopbybrand\Model\ResourceModel\Maker\CollectionFactory as MakerCollectionFactory;

class Homebrand extends Template
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
     * @var \Magento\Framework\App\Action\Context
     */
    private $_fullactionpath;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param MakerCollectionFactory $makerCollectionFactory
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\App\Action\Context $FullActionname
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        MakerCollectionFactory $makerCollectionFactory,
        UrlFactory $urlFactory,
        \Magento\Framework\App\Action\Context $FullActionname,
        Context $context,
        array $data = []
    ) {
        $this->makerCollectionFactory = $makerCollectionFactory;
        $this->urlFactory = $urlFactory;
        $this->_fullactionpath = $FullActionname;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * Load the makers
     */
    protected function _construct()
    {
        parent::_construct();
        /** @var \Ict\Shopbybrand\Model\ResourceModel\Maker\Collection $makers */
        $makers = $this->makerCollectionFactory->create()->addFieldToSelect('*')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('is_featured', 1)
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->setOrder('maker_id', 'DESC');
        $this->setMakers($makers);
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
     * Get full action name
     *
     * @return string
     */
    public function getFullActionName()
    {
        return $this->_fullactionpath->getRequest()->getFullActionName();
    }

    /**
     * Get is_featured flag from configuration
     *
     * @return mixed
     */
    public function getIsFeatured()
    {
        return $this->_scopeConfig->getValue('ict_shopbybrand/maker/featured', ScopeInterface::SCOPE_STORE);
    }
}
