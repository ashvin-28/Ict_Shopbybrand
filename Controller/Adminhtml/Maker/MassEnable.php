<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Adminhtml\Maker;

class MassEnable extends MassDisable
{

    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 brands have been enabled';

    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while enabling brands.';

    /**
     * @var bool
     */
    protected $isActive = true;
}
