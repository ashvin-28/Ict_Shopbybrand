<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Model\Adminhtml\Maker;

use Magento\Framework\Registry;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Backend\App\Action\Context;
use Ict\Shopbybrand\Model\ResourceModel\Maker;
use Magento\Framework\Event\Observer as EventObserver;

class Observer
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * Url builder instance, currently unused.
     *
     * @var mixed
     */
    private $urlBuilder;

    /**
     * @var JsHelper
     */
    private $jsHelper;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Maker
     */
    private $makerResource;

    /**
     * @param Registry $coreRegistry
     * @param JsHelper $jsHelper
     * @param Context $context
     * @param Maker $makerResource
     */
    public function __construct(
        Registry $coreRegistry,
        JsHelper $jsHelper,
        Context $context,
        Maker $makerResource
    ) {
        $this->coreRegistry   = $coreRegistry;
        $this->jsHelper       = $jsHelper;
        $this->context        = $context;
        $this->makerResource = $makerResource;
    }

    /**
     * Save product data.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function saveProductData(EventObserver $observer)
    {
        $post = $this->context->getRequest()->getPost('makers', -1);
        if ($post != '-1') {
            $post = $this->jsHelper->decodeGridSerializedInput($post);
            $product = $this->coreRegistry->registry('product');
            $this->makerResource->saveMakerProductRelation($product, $post);
        }
        return $this;
    }
    /**
     * Add category maker grid tab.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function addCategoryTab(EventObserver $observer)
    {
        $tabs = $observer->getEvent()->getTabs();
        $container = $tabs->getLayout()->createBlock(
            \Magento\Backend\Block\Template::class,
            'category.maker.grid.wrapper'
        );
        /** @var \Magento\Backend\Block\Template  $container */
        $container->setTemplate('Ict_Shopbybrand::catalog/category/maker.phtml');
        $tab = $tabs->getLayout()->createBlock(
            \Ict\Shopbybrand\Block\Adminhtml\Catalog\Category\Tab\Maker::class,
            'category.ict_shopbybrand.maker.grid'
        );

        $container->setChild('grid', $tab);
        $content = $container->toHtml();
        $tabs->addTab('ict_shopbybrand_makers', [
            'label'     => __('Shopbybrand'),
            'content'   => $content,
        ]);
        return $this;
    }

    /**
     * save category data
     * @param $observer
     * @return $this
     */
    // public function saveCategoryData($observer) {
    //     $post = $this->context->getRequest()->getPost('category_ict_shopbybrand_makers', -1);
    //     if ($post != '-1') {
    //         $post = json_decode($post, true);
    //         $category = $this->coreRegistry->registry('category');
    //         $this->makerResource->saveMakerCategoryRelation($category, $post);
    //     }
    //     return $this;
    // }
}
