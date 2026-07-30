<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Block\Adminhtml\Maker\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * Initialize tabs block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('maker_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Shopbybrand Information'));
    }
}
