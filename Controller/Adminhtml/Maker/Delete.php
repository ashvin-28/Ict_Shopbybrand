<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Adminhtml\Maker;

use Ict\Shopbybrand\Controller\Adminhtml\Maker;

class Delete extends Maker
{
    /**
     * Delete the requested maker
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('maker_id');
        if ($id) {
            $name = "";
            try {
                /** @var \Ict\Shopbybrand\Model\Maker $maker */
                $maker = $this->makerFactory->create();
                $maker->load($id);
                $name = $maker->getName();
                $maker->delete();
                $this->messageManager->addSuccess(__('The maker has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_ict_shopbybrand_maker_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('ict_shopbybrand/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_ict_shopbybrand_maker_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('ict_shopbybrand/*/edit', ['maker_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a maker to delete.'));
        // go to grid
        $resultRedirect->setPath('ict_shopbybrand/*/');
        return $resultRedirect;
    }
}
