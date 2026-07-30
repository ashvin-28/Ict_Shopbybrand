<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Adminhtml\Maker;

use Magento\Backend\App\Action\Context;
use Ict\Shopbybrand\Model\MakerFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Ict\Shopbybrand\Controller\Adminhtml\Maker as MakerController;
use Magento\Framework\Registry;
use Ict\Shopbybrand\Model\Maker;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

class InlineEdit extends MakerController
{

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * InlineEdit constructor.
     *
     * @param JsonFactory $jsonFactory
     * @param Registry $registry
     * @param MakerFactory $makerFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Registry $registry,
        MakerFactory $makerFactory,
        Date $dateFilter,
        Context $context
    ) {
        $this->jsonFactory = $jsonFactory;
        parent::__construct($registry, $makerFactory, $dateFilter, $context);
    }

    /**
     * Save maker data inline from the grid
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $makerId) {
            /** @var \Ict\Shopbybrand\Model\Maker $maker */
            $maker = $this->makerFactory->create()->load($makerId);
            try {
                $makerData = $this->filterData($postItems[$makerId]);
                $maker->addData($makerData);

                $maker->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithMakerId($maker, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithMakerId($maker, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithMakerId(
                    $maker,
                    __('Something went wrong while saving the page.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Build an error message prefixed with the maker ID
     *
     * @param Maker $maker
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithMakerId(Maker $maker, $errorText)
    {
        return '[Maker ID: ' . $maker->getId() . '] ' . $errorText;
    }
}
