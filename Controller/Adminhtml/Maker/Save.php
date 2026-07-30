<?php
namespace Ict\Shopbybrand\Controller\Adminhtml\Maker;

use Magento\Framework\Registry;
use Ict\Shopbybrand\Controller\Adminhtml\Maker;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Ict\Shopbybrand\Model\MakerFactory;
use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Ict\Shopbybrand\Model\Maker\Image as ImageModel;
use Ict\Shopbybrand\Model\Maker\File as FileModel;
use Ict\Shopbybrand\Model\Upload;
use Magento\Backend\Helper\Js as JsHelper;

class Save extends Maker
{

    /**
     * @var MakerFactory
     */
    protected $makerFactory;

    /**
     * @var ImageModel
     */
    protected $imageModel;

    /**
     * @var FileModel
     */
    protected $fileModel;

    /**
     * @var Upload
     */
    protected $uploadModel;

    /**
     * @var JsHelper
     */
    protected $jsHelper;

    /**
     * Save constructor.
     *
     * @param JsHelper $jsHelper
     * @param ImageModel $imageModel
     * @param FileModel $fileModel
     * @param Upload $uploadModel
     * @param Registry $registry
     * @param MakerFactory $makerFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        JsHelper $jsHelper,
        ImageModel $imageModel,
        FileModel $fileModel,
        Upload $uploadModel,
        Registry $registry,
        MakerFactory $makerFactory,
        Date $dateFilter,
        Context $context
    ) {
        $this->jsHelper = $jsHelper;
        $this->imageModel = $imageModel;
        $this->fileModel = $fileModel;
        $this->uploadModel = $uploadModel;
        parent::__construct($registry, $makerFactory, $dateFilter, $context);
    }

    /**
     * Run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('maker');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->filterData($data);
            $maker = $this->initMaker();
            $maker->setData($data);
            
            $avatar = $this->uploadModel->uploadFileAndGetName('avatar', $this->imageModel->getBaseDir(), $data);
            $logobanner = $this->uploadModel->uploadFileAndGetName(
                'logobanner',
                $this->imageModel->getBaseDir(),
                $data
            );
            
            $maker->setAvatar($avatar);
            $maker->setLogobanner($logobanner);
            
            $products = $this->getRequest()->getPost('products', -1);
            if ($products != -1) {
                $maker->setProductsData($this->jsHelper->decodeGridSerializedInput($products));
            }
            $this->_eventManager->dispatch(
                'ict_shopbybrand_maker_prepare_save',
                [
                    'maker' => $maker,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $maker->save();
                $this->messageManager->addSuccess(__('The brand has been saved.'));
                $this->_getSession()->setIctShopbybrandMakerData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'ict_shopbybrand/*/edit',
                        [
                            'maker_id' => $maker->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('ict_shopbybrand/*/');
                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the maker.'));
            }

            $this->_getSession()->setIctShopbybrandMakerData($data);
            $resultRedirect->setPath(
                'ict_shopbybrand/*/edit',
                [
                    'maker_id' => $maker->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('ict_shopbybrand/*/');
        return $resultRedirect;
    }
}
