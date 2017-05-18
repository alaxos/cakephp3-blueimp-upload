<?php
namespace CakephpBlueimpUpload\View\Helper;

use Cake\View\Helper;
use Cake\View\View;

/**
 * BlueimpUpload helper
 */
class BlueimpUploadHelper extends Helper
{
    public $helpers = ['Html', 'Form'];

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    public function chunked($upload_id, $options = [])
    {
//         debug($this->request);

        $default_options = [
            'upload_url'                     => null,
            'input_file_text'                => __d('cakephp_blueimp_upload', 'Upload'),
            'success_message'                => __d('cakephp_blueimp_upload', 'Upload successful'),
            'max_chunk_size'                 => 2 * 1024 * 1024,
            'multiple_select'                => true,
            'csrf_token'                     => null,
            'default_js'                     => true,
            'default_css'                    => true,
            'display_progress'               => true,
            'hide_progressbar_after_upload'  => true,
            'auto_submit'                    => true,

            'add'                            => null,
            'done'                           => null,
            'fail'                           => null,
            'always'                         => null,
            'submit'                         => null,
            'upload_success_callback'        => null,

            'template'                       => null,
            'input_button_selector'          => null,
            'progress_bar_zone_selector'     => null,
            'progress_bar_selector'          => null,
            'size_uploaded_selector'         => null,
            'notification_selector'          => null,
            'submit_btn_selector'            => null,
            'files_list_selector'            => null
        ];

        $options = array_merge($default_options, $options);

        $options['csrf_token']                 = isset($options['csrf_token'])                 ? $options['csrf_token']                 : $this->request->param('_csrfToken');
        $options['input_button_selector']      = isset($options['input_button_selector'])      ? $options['input_button_selector']      : '#' . $upload_id . ' input[type=file]';
        $options['progress_bar_zone_selector'] = isset($options['progress_bar_zone_selector']) ? $options['progress_bar_zone_selector'] : '#' . $upload_id . ' .progress-bar-zone';
        $options['progress_bar_selector']      = isset($options['progress_bar_selector'])      ? $options['progress_bar_selector']      : '#' . $upload_id . ' .progress';
        $options['size_uploaded_selector']     = isset($options['size_uploaded_selector'])     ? $options['size_uploaded_selector']     : '#' . $upload_id . ' .size';
        $options['notification_selector']      = isset($options['notification_selector'])      ? $options['notification_selector']      : '#' . $upload_id . '_notification';

        if (!$options['auto_submit']) {
            $options['submit_btn_selector']    = isset($options['submit_btn_selector'])        ? $options['submit_btn_selector']        : '#' . $upload_id . '_submit_btn';
            $options['files_list_selector']    = isset($options['files_list_selector'])        ? $options['files_list_selector']        : '#' . $upload_id . '_files_list';
        }

        /*************************/

        if($options['default_js'])
        {
            $this->Html->script('CakephpBlueimpUpload.blueimp-jquery-file-upload/js/vendor/jquery.ui.widget', ['block' => true]);
            $this->Html->script('CakephpBlueimpUpload.blueimp-jquery-file-upload/js/jquery.iframe-transport', ['block' => true]);
            $this->Html->script('CakephpBlueimpUpload.blueimp-jquery-file-upload/js/jquery.fileupload', ['block' => true]);
            $this->Html->script('CakephpBlueimpUpload.chunked', ['block' => true]);
        }

        if($options['default_css'])
        {
            $this->Html->css('CakephpBlueimpUpload.default', ['block' => true]);
        }

        if(!isset($options['template']))
        {
            $template   = [];
            $template[] = '<div id="{upload-id}">';
            $template[] = '';
            $template[] = '    <span class="btn btn-default fileinput-button">';
            $template[] = '        <span>{input_file_text}</span>';
            if($options['multiple_select'])
            {
                $template[] = '        <input type="file" name="files[]" data-url="{data-url}" multiple>';
            }
            else
            {
                $template[] = '        <input type="file" name="files[]" data-url="{data-url}">';
            }
            $template[] = '    </span>';
            $template[] = '';
            $template[] = '    <div class="progress-bar-zone" style="display:none;">';
            $template[] = '';
            $template[] = '        <div class="progress">';
            $template[] = '            <div class="progress-bar bar progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width:2em;">';
            $template[] = '                <span class="percent">0%</span>';
            $template[] = '            </div>';
            $template[] = '        </div>';
            $template[] = '        <div style="margin:-15px 0 10px 0;">';
            $template[] = '            <span class="size"></span>';
            $template[] = '        </div>';
            $template[] = '    </div>';
            $template[] = '';
            $template[] = '    <div id="{notification-id}" style="display:none;padding:5px;">';
            $template[] = '    </div>';
            $template[] = '';
            $template[] = '</div>';

            $options['template'] = implode("\n", $template);
        }

        /*************************/

        $html = $options['template'];
        $html = str_ireplace('{upload-id}',       $upload_id,                   $html);
        $html = str_ireplace('{notification-id}', $upload_id . '_notification', $html);
        $html = str_ireplace('{input_file_text}', $options['input_file_text'],  $html);
        $html = str_ireplace('{data-url}',        $options['upload_url'],       $html);

        /*************************/

        $js   = [];
        $js[] = '$(document).ready(function(){';
        $js[] = '    ';
        $js[] = '    var options = {';
        $js[] = '        "input_button_selector" : "' . $options['input_button_selector'] .'",';
        $js[] = '        "maxChunkSize"          : ' . $options['max_chunk_size'] . ',';
        $js[] = '        "sequentialUploads"     : true,';
        $js[] = '        "dataType"              : "json",';
        $js[] = '        "progress_bar_selector" : "' . $options['progress_bar_selector'] . '",';
        $js[] = '        "display_progress"      : ' . ($options['display_progress'] ? 'true' : 'false');
        $js[] = '    }';
        $js[] = '    ';

        /**
         * 'add' callback
         */

        if(!isset($options['add']))
        {
            $js[] = '    options["add"] = function(e, data){';
            $js[] = '        ';
            $js[] = '        if(typeof(data.headers) == "undefined"){';
            $js[] = '            data.headers = {}';
            $js[] = '        }';
            $js[] = '        ';
            $js[] = '        data.headers["X-Upload-id"] = ChunkedFileUpload.generate_upload_id();';

            if(isset($options['csrf_token']))
            {
                $js[] = '        data.headers["X-CSRF-Token"] = "' . $options['csrf_token'] . '";';
            }

            $js[] = '        ';
            if($options['display_progress'])
            {
                $js[] = '        ChunkedFileUpload.resetProgressBar("' . $options['progress_bar_selector'] . '", "' . $options['size_uploaded_selector'] . '")';
                $js[] = '        ChunkedFileUpload.showProgressBar("' . $options['progress_bar_zone_selector'] . '");';
            }

            $js[] = '        ChunkedFileUpload.hideNotification("' . $options['notification_selector'] . '");';
            $js[] = '        ';

            if($options['auto_submit']) {
                $js[] = '        data.submit();';
            } else {
                $js[] = '        var selected_filenames = [];';
                $js[] = '        $("' . $options['files_list_selector'] . '").find("div.filename").each(function(index, data){';
                $js[] = '            selected_filenames.push($(this).html());';
                $js[] = '        });';
                $js[] = '        ';

                $js[] = '        $.each(data.files, function (index, file) {';
                $js[] = '            ';
                $js[] = '            if ($.inArray(file.name, selected_filenames) == -1) {';
                $js[] = '                ';
                $js[] = '                $("' . $options['submit_btn_selector'] . '").on("click", function () {';
                $js[] = '                   if(data.files.length > 0) {';
                $js[] = '                       data.submit();';
                $js[] = '                   }';
                $js[] = '                });';
                $js[] = '                ';
                $js[] = '                var file_row = "<div class=\"row file-item\">";';
                $js[] = '                file_row += "<div class=\"col-md-10 filename\">";';
                $js[] = '                file_row += file.name;';
                $js[] = '                file_row += "</div>";';
                $js[] = '                file_row += "<div class=\"col-md-2\">";';
                $js[] = '                file_row += "<span class=\"delete-file-item-btn\">X</span>";';
                $js[] = '                file_row += "</div>";';
                $js[] = '                file_row += "</div>";';
                $js[] = '                ';
                $js[] = '                file_row = $(file_row);';
                $js[] = '                ';
                $js[] = '                $("' . $options['files_list_selector'] . '").append(file_row);';
                $js[] = '                ';
                $js[] = '                file_row.find(".delete-file-item-btn").click(function(){';
                $js[] = '                    data.files.length = 0;';
                $js[] = '                    file_row.remove();';
                $js[] = '                });';
                $js[] = '                ';
                $js[] = '                data.context = file_row;';
                $js[] = '                ';
                $js[] = '            } else {';
                $js[] = '                return false;';
                $js[] = '            }';
                $js[] = '            ';
                $js[] = '        });';
            }

            $js[] = '    };';
        }
        else
        {
            $js[] = '    options["add"] = ' . $options['add'];
        }

        /***
         * 'progressall' callback
         */

        $js[] = '';
        if(!isset($options['progressall']))
        {
            $js[] = '    options["progressall"] = function(e, data){';
            $js[] = '        ';

            if($options['display_progress'])
            {
                $js[] = '        ChunkedFileUpload.setBarData(data, "' . $options['progress_bar_selector'] . '", "' . $options['size_uploaded_selector'] . '");';
                $js[] = '        ';
            }

            $js[] = '        if(data.loaded == data.total)';
            $js[] = '        {';

                if($options['hide_progressbar_after_upload'])
                {
                    $js[] = '            $("' . $options['progress_bar_zone_selector'] . '").fadeOut(200, function(){';
                    $js[] = '                ChunkedFileUpload.showSuccessMessage("' . $options['notification_selector'] . '", "' . $options['progress_bar_zone_selector'] . '", "' . str_replace('"', '\"', $options['success_message']) . '");';
                    $js[] = '            });';
                }
                else
                {
                    $js[] = '            ChunkedFileUpload.showSuccessMessage("' . $options['notification_selector'] . '", "' . $options['progress_bar_zone_selector'] . '", "' . str_replace('"', '\"', $options['success_message']) . '");';
                }

            if(isset($options['upload_success_callback']))
            {
                $js[] = '            ';
                $js[] = '            ' . $options['upload_success_callback'] . '(e, data);';
            }

            $js[] = '        }';
            $js[] = '    };';
        }
        else
        {
            $js[] = '    options["progressall"] = ' . $options['progressall'];
        }

        /***
         * 'done' callback
         */

        $js[] = '';

        if(isset($options['done'])){

            $js[] = '    options["done"] = ' . $options['done'];

        } elseif (!$options['auto_submit']) {

            /*
             * Default done callback clear the upload all button
             */
            $js[] = '    options["done"] = $("' . $options['submit_btn_selector'] . '").off("click");';

        }

        /**
         * 'fail' callback
         */

        $js[] = '';

        if(isset($options['fail']))
        {
            $js[] = '    options["fail"] = ' . $options['fail'];
        }

        /**
         * 'always' callback
         */

        $js[] = '';

        if(isset($options['always']))
        {
            $js[] = '    options["always"] = ' . $options['always'];
        }


        /**
         * 'submit' callback
         */

        $js[] = '';

        if(isset($options['submit']))
        {
            $js[] = '    options["submit"] = ' . $options['submit'];
        }

        /***/

        $js[] = '';
        $js[] = '    ChunkedFileUpload.initialize(options);';
        $js[] = '});';


        /*************************/

        $content   = [];
        $content[] = $html;
        $content[] = $this->Html->scriptBlock(implode("\n", $js));

        return implode("\n", $content);
    }
}
