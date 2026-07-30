<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Maker;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ict\Shopbybrand\Model\Maker\Rss;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    public const META_DESCRIPTION_CONFIG_PATH = 'ict_shopbybrand/maker/meta_description';

    public const META_KEYWORDS_CONFIG_PATH = 'ict_shopbybrand/maker/meta_keywords';

    public const BREADCRUMBS_CONFIG_PATH = 'ict_shopbybrand/maker/breadcrumbs';

    public const ENABLED_CONFIG_PATH = 'ict_shopbybrand/maker/enabled';

    /**
     * @var Rss
     */
    protected $rss;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Rss $rss
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Rss $rss,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->rss = $rss;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Display maker list page
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if ($this->scopeConfig->isSetFlag(self::ENABLED_CONFIG_PATH, ScopeInterface::SCOPE_STORE)==1) {

            $resultPage = $this->resultPageFactory->create();
            
            if ($this->scopeConfig->isSetFlag(self::BREADCRUMBS_CONFIG_PATH, ScopeInterface::SCOPE_STORE)) {
                /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbsBlock */
                $breadcrumbsBlock = $resultPage->getLayout()->getBlock('breadcrumbs');
                if ($breadcrumbsBlock) {
                    $breadcrumbsBlock->addCrumb(
                        'home',
                        [
                            'label'    => __('Home'),
                            'link'     => $this->_url->getUrl('')
                        ]
                    );
                    $breadcrumbsBlock->addCrumb(
                        'makers',
                        [
                            'label'    => __('Shopbybrand'),
                        ]
                    );
                }
            }
            return $resultPage;
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
