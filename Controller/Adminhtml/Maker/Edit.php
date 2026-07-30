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
use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

class Edit extends MakerController
{
    /**
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param MakerFactory $makerFactory
     * @param Date $dateFilter
     * @param BackendSession $backendSession
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        MakerFactory $makerFactory,
        Date $dateFilter,
        BackendSession $backendSession,
        Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->backendSession = $backendSession;
        parent::__construct($registry, $makerFactory, $dateFilter, $context);
    }

    /**
     * Is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ict_Shopbybrand::maker');
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
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
