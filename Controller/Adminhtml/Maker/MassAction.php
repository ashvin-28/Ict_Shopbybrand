<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Adminhtml\Maker;

use Magento\Framework\Exception\LocalizedException;
use Ict\Shopbybrand\Controller\Adminhtml\Maker;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Ict\Shopbybrand\Model\MakerFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Ui\Component\MassAction\Filter;
use Ict\Shopbybrand\Model\ResourceModel\Maker\CollectionFactory;
use Ict\Shopbybrand\Model\Maker as MakerModel;

abstract class MassAction extends Maker
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var string
     */
    protected $successMessage = 'Mass Action successful on %1 records';

    /**
     * @var string
     */
    protected $errorMessage = 'Mass Action failed';

    /**
     * MassAction constructor.
     *
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param MakerFactory $makerFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        Registry $registry,
        MakerFactory $makerFactory,
        Date $dateFilter,
        Context $context
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($registry, $makerFactory, $dateFilter, $context);
    }

    /**
     * Perform the mass action on a single maker
     *
     * @param MakerModel $maker
     * @return mixed
     */
    abstract protected function doTheAction(MakerModel $maker);

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $maker) {
                $this->doTheAction($maker);
            }
            $this->messageManager->addSuccess(__($this->successMessage, $collectionSize));
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($this->errorMessage));
        }
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('ict_shopbybrand/*/index');
        return $redirectResult;
    }
}
