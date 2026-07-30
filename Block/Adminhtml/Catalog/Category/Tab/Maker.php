<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Block\Adminhtml\Catalog\Category\Tab;

use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Ict\Shopbybrand\Model\ResourceModel\Maker\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Ict\Shopbybrand\Model\Maker\Category as MakerCategory;

/**
 * @method Maker setCategoryMakers(array $makers)
 */
class Maker extends ExtendedGrid
{
    /**
     * @var CollectionFactory
     */
    private $makerCollectionFactory;

    /**
     * @var MakerCategory
     */
    private $makerCategory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param CollectionFactory $makerCollectionFactory
     * @param MakerCategory $makerCategory
     * @param Registry $registry
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param array $data
     */
    public function __construct(
        CollectionFactory $makerCollectionFactory,
        MakerCategory $makerCategory,
        Registry $registry,
        Context $context,
        BackendHelper $backendHelper,
        array $data = []
    ) {
        $this->makerCollectionFactory = $makerCollectionFactory;
        $this->makerCategory = $makerCategory;
        $this->registry = $registry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize the grid block.
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('catalog_category_ict_shopbybrand_maker');
        $this->setDefaultSort('maker_id');
        $this->setUseAjax(true);
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(['in_makers'=>1]);
        }
    }

    /**
     * Get current category
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->makerCollectionFactory->create();
        if ($this->getCategory()->getId()) {
            $constraint = 'related.category_id='.$this->getCategory()->getId();
        } else {
            $constraint = 'related.category_id=0';
        }
        $collection->getSelect()->joinLeft(
            ['related' => $collection->getTable('ict_shopbybrand_maker_category')],
            'related.maker_id=main_table.maker_id AND '.$constraint,
            ['position']
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_makers',
            [
                'header_css_class'  => 'a-center',
                'type'  => 'checkbox',
                'name'  => 'in_makers',
                'values'=> $this->_getSelectedMakers(),
                'align' => 'center',
                'index' => 'maker_id'
            ]
        );
        $this->addColumn(
            'maker_id',
            [
                'header'=> __('Id'),
                'type'  => 'number',
                'align' => 'left',
                'index' => 'maker_id',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header'=> __('Name'),
                'align' => 'left',
                'index' => 'name',
            ]
        );
        $this->addColumn(
            'position',
            [
                'header'        => __('Position'),
                'name'          => 'position',
                'width'         => 60,
                'type'        => 'number',
                'validate_class'=> 'validate-number',
                'index'         => 'position',
                'editable'      => true,
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Get selected makers
     *
     * @return array
     */
    protected function _getSelectedMakers()
    {
        $makers = $this->getCategoryMakers();
        if (!is_array($makers)) {
            $makers = array_keys($this->getSelectedMakers());
        }
        return $makers;
    }

    /**
     * Get selected makers with their position
     *
     * @access public
     * @return array
     */
    public function getSelectedMakers()
    {
        $makers = [];
        $selected = $this->makerCategory->getSelectedMakers($this->getCategory());
        if (!is_array($selected)) {
            $selected = [];
        }
        foreach ($selected as $maker) {
            /** @var \Ict\Shopbybrand\Model\Maker $maker */
            $makers[$maker->getId()] = $maker->getPosition();
        }
        return $makers;
    }

    /**
     * Get row URL
     *
     * @param \Ict\Shopbybrand\Model\Maker $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return '#';
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'ict_shopbybrand/catalog_category/makersGrid',
            ['id'=>$this->getCategory()->getId()]
        );
    }

    /**
     * Add column filter to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_makers') {
            $makerIds = $this->_getSelectedMakers();
            if (empty($makerIds)) {
                $makerIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('maker_id', ['in'=>$makerIds]);
            } else {
                if ($makerIds) {
                    $this->getCollection()->addFieldToFilter('maker_id', ['nin'=>$makerIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
}
