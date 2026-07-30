<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Adminhtml\Catalog\Category;

use Magento\Catalog\Controller\Adminhtml\Category;
use Magento\Backend\App\Action\Context;
// use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\View\Result\LayoutFactory;

class MakersGrid extends Category
{

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * MakersGrid constructor.
     *
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        // RedirectFactory $resultRedirectFactory,
        LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Render the category makers grid layout
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->_initCategory();
        $resultLayout = $this->resultLayoutFactory->create();
        /** @var \Ict\Shopbybrand\Block\Adminhtml\Catalog\Category\Tab\Maker $makersBlock */
        $makersBlock = $resultLayout->getLayout()->getBlock('category.ict_shopbybrand.maker.grid');
        if ($makersBlock) {
            $makersBlock->setCategoryMakers($this->getRequest()->getPost('category_ict_shopbybrand_makers', null));
        }
        return $resultLayout;
    }
}
