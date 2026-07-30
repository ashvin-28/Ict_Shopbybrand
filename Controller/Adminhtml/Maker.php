<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Ict\Shopbybrand\Model\MakerFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

abstract class Maker extends Action
{

    /**
     * @var MakerFactory
     */
    protected $makerFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Date
     */
    protected $dateFilter;

    /**
     * Constructor
     *
     * @param Registry $registry
     * @param MakerFactory $makerFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        MakerFactory $makerFactory,
        Date $dateFilter,
        Context $context
    ) {
        $this->coreRegistry = $registry;
        $this->makerFactory = $makerFactory;
        $this->dateFilter = $dateFilter;
        parent::__construct($context);
    }

    /**
     * Initialize maker model from request
     *
     * @return \Ict\Shopbybrand\Model\Maker
     */
    protected function initMaker()
    {
        $makerId  = (int) $this->getRequest()->getParam('maker_id');
        /** @var \Ict\Shopbybrand\Model\Maker $maker */
        $maker    = $this->makerFactory->create();
        if ($makerId) {
            $maker->load($makerId);
        }
        $this->coreRegistry->register('ict_shopbybrand_maker', $maker);
        return $maker;
    }

    /**
     * Filter dates
     *
     * @param array $data
     * @return array
     */
    public function filterData($data)
    {
        if (isset($data['dob'])) {
            $data['dob'] = $this->dateFilter->filter($data['dob']);
        }
        if (isset($data['awards'])) {
            if (is_array($data['awards'])) {
                $data['awards'] = implode(',', $data['awards']);
            }
        }
        return $data;
    }
}
