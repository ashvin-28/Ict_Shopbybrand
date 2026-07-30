<?php

/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Block\Adminhtml\Maker\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Category extends GenericForm implements TabInterface
{
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Ict\Shopbybrand\Model\Maker $maker */
        $maker = $this->_coreRegistry->registry('ict_shopbybrand_maker');

        $form   = $this->_formFactory->create();
        $form->setHtmlIdPrefix('maker_');
        $form->setFieldNameSuffix('maker');
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Categories'),
            'class' => 'fieldset-wide'
        ]);
        /* $fieldset->addField('categories_ids', '\Ict\Shopbybrand\Block\Adminhtml\Helper\Category', array(
            'name'  => 'categories_ids',
            'label'     => __('Categories'),
            'title'     => __('Categories'),

        )); */

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryIds = implode(
            ", ",
            $objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class)
                ->addFieldToFilter(
                    'level',
                    ['gt' => 0]
                )->getAllIds()
        );
        if ($maker->getCategoriesIds() == "") {
            $maker->setCategoriesIds('');
        }
        $fieldset->addField(
            'categories_ids',
            'text',
            [
                'name' => 'categories_ids',
                'label' => __('Categories'),
                // 'after_element_html' => '<a id="category_link" href="javascript:void(0)" '
                //     . 'onclick="toggleMainCategories()"><img src="'
                //     . $this->getBaseUrl()
                //     . 'pub/static/adminhtml/Magento/backend/en_US/images/rule_chooser_trigger.gif" '
                //     . 'alt="" class="v-middle rule-chooser-trigger" title="Select Categories"></a>
                // <div id="main_categories_select" style="display:none"></div>
                'after_element_html' => '<a id="category_link" href="javascript:void(0)" onclick="toggleMainCategories()" class="action-default" style="margin-left:8px;">' . __('Select Categories') . '</a>
<div id="main_categories_select" style="display:none"></div>
                    <script type="text/javascript">
                    function toggleMainCategories(check){
                        
                        var cate = jQuery("#main_categories_select");
                        if(jQuery("#main_categories_select").css("display") == "none" || (check ==1) || (check == 2)){
                        jQuery("#categories_check").css("display","block");
                            var url = "' . $this->getUrl('ict_shopbybrand/maker/category') . '";
                            
                            var params = jQuery("#maker_categories_ids").val();
                            var parameters = {"form_key": FORM_KEY,"catids":params };
                            
                            jQuery.ajax({
                                url : url,
                                data: parameters,
                                type : "post",
                                showLoader : true,
                                success : function(result) {
                                    jQuery("#main_categories_select").html(result.suggetion);
                                    jQuery("#main_categories_select").css("display","block");
                                    setTimeout(function(){ 
                                    jQuery(".x-tree-node-icon").replaceWith("<input type='
                    . '\'checkbox\' name=\'chk\' class=\'chkbox\' />");
                                    }, 500);
                                }
                            });
                        if(cate.css("display") == "none"){
                            cate.css("display","block");
                        }else{
                            cate.css("display","none");
                        }
                    }else{
                        cate.css("display","none");
                        jQuery("#categories_check").css("display","none");
                    }
                };
            require(["jquery", "jquery/ui"], function($){     
                jQuery("body").on("click","#main_categories_select .category-checkbox",function(){ 
                    var checked = "";
                    jQuery("#main_categories_select .category-checkbox").each(function(){
                        if(jQuery(this).prop("checked") == true){
                            checked += jQuery(this).val()+",";    
                        }
                    });
                var finalcategory = checked.slice(0,-1);
              
                jQuery("#maker_categories_ids").val(finalcategory);
                });
            });
        </script>
            '
            ]
        );

        // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
        if (is_null($maker->getCategoriesIds())) {
            $maker->setCategoriesIds($maker->getCategoryIds());
        }

        $form->addValues($maker->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Categories');
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
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
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
}
