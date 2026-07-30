<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Model\Maker\Source;

use Magento\Framework\Option\ArrayInterface;
use Ict\Shopbybrand\Model\Maker;

class IsActive implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs.
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Maker::STATUS_ENABLED,
                'label' => __('Yes')
            ],[
                'value' => Maker::STATUS_DISABLED,
                'label' => __('No')
            ],
        ];
    }

    /**
     * Get options as key value pair.
     *
     * @return array
     */
    public function getOptions()
    {
        $_tmpOptions = $this->toOptionArray();
        $_options = [];
        foreach ($_tmpOptions as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
}
