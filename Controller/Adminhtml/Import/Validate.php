<?php
/**
 * @author Ict Team
 * @copyright Copyright (c) 2017 Ict (http://icreativetechnologies.com/)
 * @package Ict_Shopbybrand
 */

namespace Ict\Shopbybrand\Controller\Adminhtml\Import;

use Ict\Shopbybrand\Controller\Adminhtml\ImportResult as ImportResultController;
use Ict\Shopbybrand\Block\Adminhtml\Import\Frame\Result as ImportResultBlock;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Validate extends ImportResultController
{

    /**
     * Validate uploaded CSV file
     *
     * @return \Magento\Framework\Controller\ResultInterface
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
            // common actions
            $resultBlock->addAction(
                'show',
                'import_validation_container'
            );
            $destinationPath = $this->getDestinationPath();
            try {
                $target = $destinationPath;
                /** Allowed extension types */
                $uploader->setAllowedExtensions($this->allowedExtensions);
                /** rename file name if already exists */
                $uploader->setAllowRenameFiles(true);
                /** upload file in folder "mycustomfolder" */
                $result = $uploader->save($target);
                if ($result['file']) {
                    $resultBlock->addSuccess(__('File has been successfully uploaded.'));
                    $resultBlock->addUploadedFile($result['file']);
                    $validationResult = $this->validateSource($destinationPath.'/'.$result['file']);
                }
            } catch (\Exception $e) {
                $resultBlock->addError($e->getMessage());
            }
            return $resultLayout;
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
     * Validate CSV source file content and collect errors
     *
     * @param string $csv_path
     * @return array
     */
    protected function validateSource($csv_path = '')
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
         $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
         /** @var $resultBlock ImportResultBlock */
         $resultBlock = $resultLayout->getLayout()->getBlock('import.frame.result');
         $resultBlock->addUploadedFile($csv_path);
        $data = $this->_fileCsv->getData($csv_path);

        $csv_validation = $resultBlock->checkCSV($data[0]);
        
        $errors = [];

        $invalid_rows_cnt = $total_errors_cnt = 0;

        if (!empty($csv_validation)) {
            $errors[] = $resultBlock->addError(
                __("Invalid CSV. Check Fields : ".implode(" , ", $csv_validation))
            );
        }

        if (count($data) <= 1) {
            $errors[] = $resultBlock->addError(__("CSV File is empty."));
        } else {
            $fields_arr = [];

            foreach ($resultBlock->fields_require as $key => $value) {
                $fields_arr[] = [
                    'key' => $value["key"],
                    'rows' => [],
                    'messages' => $value["message"],
                    'data_check' => []
                ];
            }

            for ($i=1, $ic=count($data)-1; $i<=$ic; $i++) {
                $csv_validation_row = $resultBlock->checkCSVRow($data[$i]);
                if (!empty($csv_validation_row)) {
                    $invalid_rows_cnt++;
                    foreach ($resultBlock->fields_require as $key => $value) {
                        if (in_array($value["key"], $csv_validation_row)) {
                            $fields_arr[$key]['rows'][] = $i;
                        }
                    }
                }

                $csv_data_validation_row = $resultBlock->checkCSVDataRow($data[$i]);
                if (!empty($csv_data_validation_row)) {
                    $invalid_rows_cnt++;
                    $check_data_validation_row = array_keys($csv_data_validation_row);
                    foreach ($resultBlock->fields_require as $key => $value) {
                        if (in_array($value["key"], $check_data_validation_row)) {
                            $fields_arr[$key]['data_check'][] = $i;
                        }
                    }
                }
            }

            foreach ($fields_arr as $key => $value) {
                if (!empty($value["rows"])) {
                      $total_errors_cnt += count($value["rows"]);
                      $error_msg = __("Please make sure attribute `".$value['key']."` in rows : ");
                      $error_msg .= implode(" , ", $value["rows"]);
                      $errors[] = $resultBlock->addError($error_msg);
                }

                if (!empty($value["data_check"])) {
                      $total_errors_cnt += count($value["data_check"]);
                      $error_msg = $value["messages"] .' in rows : ';
                      $error_msg .= implode(" , ", $value["data_check"]);
                      $errors[] = $resultBlock->addError($error_msg);
                }
            }

            if (!empty($errors)) {
                $resultBlock->addError(
                    __('Data validation is failed. Please fix errors and re-upload the file..')
                );
            } else {
                $resultBlock->addSuccess(
                    __('File is valid! To start import process press "Import" button'),
                    true
                );
            }

            $resultBlock->addNotice(
                __(
                    'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                    count($data)-1,
                    count($data)-1,
                    $invalid_rows_cnt,
                    $total_errors_cnt
                )
            );
        }

        return $errors;
    }

    /**
     * Get destination path for uploaded files
     *
     * @return string
     */
    protected function getDestinationPath()
    {
        $var_dir = $this->fileSystem->getDirectoryRead(DirectoryList::VAR_DIR);
        $this->io->checkAndCreateFolder($var_dir->getAbsolutePath('/import/categories'));
        return $var_dir->getAbsolutePath('/import/categories');
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
