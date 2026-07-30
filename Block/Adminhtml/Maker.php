<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Maker extends Container
{
    /**
     * Initialize maker grid container
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_maker';
        $this->_blockGroup = 'Ict_Shopbybrand';
        $this->_headerText = __('Shopbybrand');
        $this->_addButtonLabel = __('Create New Shopbybrand');
        parent::_construct();
    }

    /**
     * Retrieve category ids
     *
     * @return mixed
     */
    public function getCategoryIds()
    {
        return $this->getProducts();
    }
}
