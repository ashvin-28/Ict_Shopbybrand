<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Controller\ResultFactory;

class Save extends Action
{

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var array
     */
    protected $allowedExtensions = ['csv'];

    /**
     * @var string
     */
    protected $fileId = 'import_file';

    /**
     * @var File
     */
    protected $io;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param File $io
     */
    public function __construct(
        Action\Context $context,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
        File $io
    ) {
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->io = $io;
        parent::__construct($context);
    }
    
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        /** @var $resultBlock ImportResultBlock */
        $resultBlock = $resultLayout->getLayout()->getBlock('import.frame.result');
        /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => $this->fileId]);
        if ($data) {
            $destinationPath = $this->getDestinationPath();
            $resultRedirect = $this->resultRedirectFactory->create();
            try {
                $target = $destinationPath;
                /** Allowed extension types */
                $uploader->setAllowedExtensions($this->allowedExtensions);
                /** rename file name if already exists */
                $uploader->setAllowRenameFiles(true);
                /** upload file in folder "mycustomfolder" */
                $result = $uploader->save($target);
                if ($result['file']) {
                    $this->messageManager->addSuccess(__('File has been successfully uploaded'));
                    $resultBlock->validateSource($destinationPath.'/'.$result['file']);
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } elseif ($this->getRequest()->isPost() && empty($uploader->validateFile())) {
            $resultBlock->addError(__('The file was not uploaded.'));
            return $resultLayout;
        }

        $this->messageManager->addError(__('Sorry, but the data is invalid or the file is not uploaded.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('ict_shopbybrand/*/index');
        return $resultRedirect;
    }

    /**
     * Get destination path for uploaded files
     *
     * @return string
     */
    protected function getDestinationPath()
    {
        $media_path = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->io->checkAndCreateFolder($media_path->getAbsolutePath('/import/shopbybrand'));
        return $media_path->getAbsolutePath('/import/shopbybrand');
    }

    /**
     * Check whether the current admin user is allowed to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ict_Shopbybrand::makers');
    }
}
