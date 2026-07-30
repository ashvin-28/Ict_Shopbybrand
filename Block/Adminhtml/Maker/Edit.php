<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Block\Adminhtml\Maker;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends FormContainer
{
    /**
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize maker edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'maker_id';
        $this->_blockGroup = 'Ict_Shopbybrand';
        $this->_controller = 'adminhtml_maker';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Shopbybrand'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Shopbybrand'));
    }

    /**
     * Retrieve text for header element depending on loaded maker
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \Ict\Shopbybrand\Model\Maker $maker */
        $maker = $this->coreRegistry->registry('ict_shopbybrand_maker');
        if ($maker->getId()) {
            return __("Edit Shopbybrand '%1'", $this->escapeHtml($maker->getName()));
        }
        return __('New Shopbybrand');
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('maker_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'maker_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'maker_content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
}
