<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Block\Adminhtml\Maker\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Config\Model\Config\Source\Yesno as BooleanOptions;

class Maker extends GenericForm implements TabInterface
{
    /**
     * @var WysiwygConfig
     */
    private $wysiwygConfig;

    /**
     * @var BooleanOptions
     */
    private $booleanOptions;

    /**
     * Maker constructor.
     *
     * @param WysiwygConfig $wysiwygConfig
     * @param BooleanOptions $booleanOptions
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        WysiwygConfig $wysiwygConfig,
        BooleanOptions $booleanOptions,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->wysiwygConfig    = $wysiwygConfig;
        $this->booleanOptions   = $booleanOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Ict\Shopbybrand\Model\Maker $maker */
        $maker = $this->_coreRegistry->registry('ict_shopbybrand_maker');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('maker_');
        $form->setFieldNameSuffix('maker');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Shopbybrand Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        $fieldset->addType('image', \Ict\Shopbybrand\Block\Adminhtml\Maker\Helper\Image::class);
        $fieldset->addType('file', \Ict\Shopbybrand\Block\Adminhtml\Maker\Helper\File::class);

        if ($maker->getId()) {
            $fieldset->addField(
                'maker_id',
                'hidden',
                ['name' => 'maker_id']
            );
        }
        $fieldset->addField(
            'name',
            'text',
            [
                'name'      => 'name',
                'label'     => __('Name'),
                'title'     => __('Name'),
                'required'  => true,
            ]
        );
        $fieldset->addField(
            'url_key',
            'text',
            [
                'name'      => 'url_key',
                'label'     => __('URL Key'),
                'title'     => __('URL Key'),
            ]
        );
        if ($this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name'      => 'stores[]',
                    'value'     => $this->_storeManager->getStore(true)->getId()
                ]
            );
            $maker->setStoreId($this->_storeManager->getStore(true)->getId());
        }
        $fieldset->addField(
            'is_active',
            'select',
            [
                'label'     => __('Is Active'),
                'title'     => __('Is Active'),
                'name'      => 'is_active',
                'required'  => true,
                'options'   => $maker->getAvailableStatuses(),
            ]
        );
        $fieldset->addField(
            'is_featured',
            'select',
            [
                'label'     => __('Is Featured'),
                'title'     => __('Is Featured'),
                'name'      => 'is_featured',
                'required'  => true,
                'options'   => $maker->getAvailableStatuses(),
            ]
        );
       
        $fieldset->addField(
            'banner_text',
            'editor',
            [
                'name'      => 'banner_text',
                'label'     => __('Banner Text'),
                'title'     => __('Banner Text'),
                'style'     => 'height:26em',
                'required'  => true,
                'config'    => $this->wysiwygConfig->getConfig()
            ]
        );
        
        $fieldset->addField(
            'avatar',
            'image',
            [
                'name'        => 'avatar',
                'label'       => __('Logo'),
                'title'       => __('Logo'),
            ]
        );
        $fieldset->addField(
            'logobanner',
            'image',
            [
                'name'        => 'logobanner',
                'label'       => __('Logo Banner'),
                'title'       => __('Logo banner'),
            ]
        );

        $makerData = $this->_session->getData('ict_shopbybrand_maker_data', true);
        if ($makerData) {
            $maker->addData($makerData);
        } else {
            if (!$maker->getId()) {
                $maker->addData($maker->getDefaultValues());
            }
        }
        $form->addValues($maker->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
    
    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Shopbybrand');
    }
}
