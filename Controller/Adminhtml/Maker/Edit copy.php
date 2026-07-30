<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Adminhtml\Maker;

use Ict\Shopbybrand\Controller\Adminhtml\Maker as MakerController;
use Magento\Framework\Registry;
use Ict\Shopbybrand\Model\MakerFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

class Edit extends MakerController
{

    /**
     * Backend session instance (not populated by the constructor)
     *
     * @var mixed
     */
    protected $backendSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     *
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param MakerFactory $makerFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        MakerFactory $makerFactory,
        Date $dateFilter,
        Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($registry, $makerFactory, $dateFilter, $context);
    }

    /**
     * Check whether the action is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ict_Shopbybrand::maker');
    }

    /**
     * Edit maker page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('maker_id');
        /** @var \Ict\Shopbybrand\Model\Maker $maker */
        $maker = $this->initMaker();
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ict_Shopbybrand::maker');
        $resultPage->getConfig()->getTitle()->set((__('Shopbybrand')));
        if ($id) {
            $maker->load($id);
            if (!$maker->getId()) {
                $this->messageManager->addError(__('This maker no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'ict_shopbybrand/*/edit',
                    [
                        'maker_id' => $maker->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $maker->getId() ? $maker->getName() : __('New Maker');
        $resultPage->getConfig()->getTitle()->append($title);
        $data = $this->backendSession->getData('ict_shopbybrand_maker_data', true);
        if (!empty($data)) {
            $maker->setData($data);
        }
        return $resultPage;
    }
}
