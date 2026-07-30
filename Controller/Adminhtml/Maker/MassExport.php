<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Adminhtml\Maker;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ict\Shopbybrand\Model\ResourceModel\Maker\CollectionFactory as MakerCollectionFactory;

class MassExport extends Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
     */
    protected $resultPage;

    /**
     * @var MakerCollectionFactory
     */
    protected $makerCollectionFactory;

    /**
     * @var string
     */
    protected $successMessage = 'Mass Action successful on %1 records';

    /**
     * @var string
     */
    protected $errorMessage = 'Mass Action failed';

    /**
     * MassExport constructor.
     *
     * @param Context $context
     * @param MakerCollectionFactory $makerCollectionFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        MakerCollectionFactory $makerCollectionFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->makerCollectionFactory = $makerCollectionFactory;
    }

    /**
     * Export selected makers to a CSV file
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {

        $ids = $this->getRequest()->getPost('selected');
        try {
            // $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $heading = ['name','url_key','banner_text','logo','banner','active','featured'];
            $outputFile = "shopbybrand_". date('Ymd_His').".csv";
            // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
            $handle = fopen($outputFile, 'w');
            // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
            fputcsv($handle, $heading);

            $makers = $this->makerCollectionFactory->create()->addFieldToFilter("maker_id", ["in"=>$ids]);
            foreach ($makers as $maker) {
                $data = [
                    $maker->getName(),
                    $maker->getUrlKey(),
                    $maker->getBannerText(),
                    $maker->getAvatar(),
                    $maker->getLogobanner(),
                    $maker->getIsActive(),
                    $maker->getIsFeatured()
                ];
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
                fputcsv($handle, $data);
            }
            // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            if (file_exists($outputFile)) {
                //set appropriate headers
                // phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
                header('Content-Description: File Transfer');
                header('Content-Type: application/csv');
                header('Content-Disposition: attachment; filename='.basename($outputFile));
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($outputFile));
                // phpcs:enable Magento2.Functions.DiscouragedFunction.Discouraged
                ob_clean();
                flush();
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
                readfile($outputFile);
            }
            $this->messageManager->addSuccess(__($this->successMessage));
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
