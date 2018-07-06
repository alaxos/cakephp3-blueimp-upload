<?php
namespace CakephpBlueimpUpload\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Alaxos\Lib\FileTool;

/**
 * Uploader component
 */
class UploaderComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Contains last upload errors
     *
     * @var array
     */
    protected $_upload_errors = [];


    public function upload($upload_folder, $options = array())
    {
        $default_options = ['auto_subfolder'     => true,
                            'override_by_name'   => false,
                            'accepted_mimetypes' => []
                           ];

        $options = array_merge($default_options, $options);

        $controller = $this->_registry->getController();

        $upload_table = $controller->loadModel('CakephpBlueimpUpload.Uploads');

        /*
         * Reset errors
         */
        $this->_upload_errors = [];

        $request = $this->getController()->getRequest();

        if($request->is(['post', 'put']))
        {
            $upload_id      = $request->getHeaderLine('X-Upload-id');
            $content_length = $request->getHeaderLine('Content-Length');
            $content_range  = $request->getHeaderLine('Content-Range');
            $content_type   = $request->contentType();; // this is insecure !

            $files = $request->getData('files');
            if(isset($files))
            {
                foreach($files as $uploaded_file)
                {
                    if(is_uploaded_file($uploaded_file['tmp_name']))
                    {
                        $valid_mimetype = true;
                        $mimetype = null;
                        if(!empty($options['accepted_mimetypes']))
                        {
                            $mimetype = $uploaded_file['type'];

                            if(!in_array($mimetype, $options['accepted_mimetypes']))
                            {
                                $valid_mimetype = false;
                            }
                        }

                        if($valid_mimetype)
                        {
                            $original_filename = $uploaded_file['name'];

                            $total_filesize = null;
                            if(stripos($content_range, '/') !== false)
                            {
                                $total_filesize = substr($content_range, stripos($content_range, '/') + 1);
                            }

                            $chunk_size = $uploaded_file['size'];

                            if(!empty($upload_id) && !empty($original_filename) && !empty($chunk_size))
                            {
                                /*
                                 * Check if a file belonging to the same upload already exists in database
                                 */
                                $existing_upload = $upload_table->find('all')->where(['upload_id' => $upload_id])->first();
                                if(!empty($existing_upload))
                                {
                                    /*
                                     * This POST is a new file part
                                     */
                                    $upload_resume = true;
                                    $new_file      = false;
                                }
                                else
                                {
                                    if($options['override_by_name'])
                                    {
                                        /*
                                         * Check if a file with the same name already exists
                                         * -> the upload will override the record in the database with the new file metadata
                                         */
                                        $existing_upload = $upload_table->find('all')->where(['original_filename' => $original_filename])->first();
                                    }

                                    /*
                                     * This POST is a brand new file upload
                                     */
                                    $upload_resume = false;
                                    $new_file      = true;
                                }

                                /****/

                                $unique_filename   = $upload_id . '_' . $original_filename;

                                if($options['auto_subfolder'])
                                {
                                    $subfolder = date('Y-m-d');
                                    if(!is_dir($upload_folder . DS . $subfolder))
                                    {
                                        mkdir($upload_folder . DS . $subfolder);
                                        chmod($upload_folder . DS . $subfolder, 0777);
                                    }

                                    $uploaded_filepath = $upload_folder . DS . $subfolder . DS . $unique_filename;
                                }
                                else
                                {
                                    $subfolder = null;
                                    $uploaded_filepath = $upload_folder . DS . $unique_filename;
                                }

                                /****/

                                $uploaded = false;

                                if($new_file)
                                {
                                    /*
                                     * Move the first part of the file
                                     */
                                    if(move_uploaded_file($uploaded_file['tmp_name'], $uploaded_filepath))
                                    {
                                        $uploaded = true;
                                    }
                                }
                                elseif($upload_resume)
                                {
                                    /*
                                     * -> append the uploaded data to the already existing file part
                                     */
                                    if(file_put_contents($uploaded_filepath, fopen($uploaded_file['tmp_name'], 'r'), FILE_APPEND))
                                    {
                                        $uploaded = true;
                                    }
                                }

                                /****/

                                if($uploaded)
                                {
                                    $upload = $upload_table->newEntity();

                                    $upload->original_filename    = $original_filename;
                                    $upload->unique_filename      = $unique_filename;
                                    $upload->subfolder            = $subfolder;
                                    $upload->mimetype             = FileTool::getMimetype($uploaded_filepath);
                                    $upload->size                 = filesize($uploaded_filepath);
                                    $upload->upload_id            = $upload_id;
                                    $upload->label                = null;

                                    if( (!empty($total_filesize) && filesize($uploaded_filepath) == $total_filesize) || empty($total_filesize) )
                                    {
                                        /*
                                         * Note: it seems that when $total_filesize is not set, the file is uploaded in only one POST request -> it is always complete
                                         */
                                        $upload->complete = true;
                                        $upload->hash     = sha1_file($uploaded_filepath);
                                    }
                                    else
                                    {
                                        $upload->complete = false;
                                    }

                                    if(!empty($existing_upload))
                                    {
                                        $upload->id = $existing_upload->id;
                                    }

                                    if(!$upload_table->save($upload))
                                    {
                                        $this->_upload_errors = array_merge($this->_upload_errors, $upload->validationErrors);
                                    }
                                }
                                else
                                {
                                    $this->_upload_errors[] = __d('cakephp_blueimp_upload', 'some part of the file could not be saved');
                                }
                            }
                            else
                            {
                                $this->_upload_errors[] = __d('cakephp_blueimp_upload', 'some upload metadata are missing');
                            }
                        }
                        else
                        {
                            $this->_upload_errors[] = sprintf(__d('cakephp_blueimp_upload', 'this file type (%s) can not be uploaded'), $mimetype);
                        }
                    }
                    else
                    {
                        $this->_upload_errors[] = __d('cakephp_blueimp_upload', 'upload file not found');
                    }
                }
            }
            else
            {
                $this->_upload_errors[] = __d('cakephp_blueimp_upload', 'No data to upload found. Check that the max chunk size is not to high.');
            }
        }
        else
        {
            $this->_upload_errors[] = sprintf(__d('cakephp_blueimp_upload', "'%s' method is not valid to upload", strtoupper($request->getMethod())));
        }

        if(empty($this->_upload_errors))
        {
            return $upload;
        }
        else
        {
            return false;
        }
    }

    public function getUploadErrors()
    {
        return $this->_upload_errors;
    }
}
