<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Import extends Action
{
    /**
     * Check whether the current admin user is allowed to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ict_Shopbybrand::makers');
    }
}
