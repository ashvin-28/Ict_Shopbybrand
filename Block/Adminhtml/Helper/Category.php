<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Block\Adminhtml\Helper;

use Magento\Framework\Data\Form\Element\Multiselect;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Backend\Helper\Data as DataHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Escaper;

class Category extends Multiselect
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var DataHelper
     */
    private $backendData;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param CollectionFactory $collectionFactory
     * @param DataHelper $backendData
     * @param LayoutInterface $layout
     * @param EncoderInterface $jsonEncoder
     * @param AuthorizationInterface $authorization
     * @param ElementFactory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DataHelper $backendData,
        LayoutInterface $layout,
        EncoderInterface $jsonEncoder,
        AuthorizationInterface $authorization,
        ElementFactory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->backendData = $backendData;
        $this->layout = $layout;
        $this->jsonEncoder = $jsonEncoder;
        $this->authorization = $authorization;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Get no display
     *
     * @return bool
     */
    public function getNoDisplay()
    {
        $isNotAllowed = !$this->authorization->isAllowed('Magento_Catalog::categories');
        return $this->getData('no_display') || $isNotAllowed;
    }

    /**
     * Get values for select
     *
     * @return array
     */
    public function getValues()
    {
        $collection = $this->_getCategoriesCollection();
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = explode(',', $values);
        }
        $collection->addAttributeToSelect('name');
        $collection->addIdFilter($values);
        $options = [];
        foreach ($collection as $category) {
            /** @var \Magento\Catalog\Model\Category $category */
            $options[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }
        return $options;
    }

    /**
     * Get categories collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected function _getCategoriesCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * Attach category suggest widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        if (!$this->isAllowed()) {
            return '';
        }
        $htmlId = $this->getHtmlId();
        $suggestPlaceholder = __('start typing to search category');
        $selectorOptions = $this->jsonEncoder->encode($this->_getSelectorOptions());
        $newCategoryCaption = __('New Category');
        /** @var Magento\Backend\Block\Widget\Button $button */
        $button = $this->layout->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'add_category_button',
                'label' => $newCategoryCaption,
                'title' => $newCategoryCaption,
                'onclick' => 'jQuery("#new-category").modal("openModal")',
                'disabled' => $this->getDisabled(),
            ]
        );
        $return = <<<HTML
    <input id="{$htmlId}-suggest" placeholder="$suggestPlaceholder" />
    <script>
        require(["jquery", "mage/mage"], function($){
            $('#{$htmlId}-suggest').mage('treeSuggest', {$selectorOptions});
        });
    </script>
HTML;
        return $return . $button->toHtml();
    }

    /**
     * Get selector options
     *
     * @return array
     */
    protected function _getSelectorOptions()
    {
        return [
            'source' => $this->backendData->getUrl('catalog/category/suggestCategories'),
            'valueField' => '#' . $this->getHtmlId(),
            'className' => 'category-select',
            'multiselect' => true,
            'showAll' => true
        ];
    }

    /**
     * Whether permission is granted
     *
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->authorization->isAllowed('Magento_Catalog::categories');
    }
}
