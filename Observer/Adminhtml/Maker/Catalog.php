<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Observer\Adminhtml\Maker;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Ict\Shopbybrand\Model\ResourceModel\Maker;

abstract class Catalog implements ObserverInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Maker
     */
    private $makerResource;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var JsHelper
     */
    private $jsHelper;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Maker $makerResource
     * @param JsHelper $jsHelper
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Maker $makerResource,
        JsHelper $jsHelper,
        Registry $coreRegistry
    ) {
        $this->context        = $context;
        $this->makerResource = $makerResource;
        $this->jsHelper       = $jsHelper;
        $this->coreRegistry   = $coreRegistry;
    }
}
