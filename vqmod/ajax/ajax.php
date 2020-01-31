<?php
/*
 * @author Adrian Olmedo <adrianolmedo.ve@gmail.com>
 * @copyright (c) 2020 CodexiLab
 *
 * This file is part of vQmod for Osclass.
 *
 * vQmod for Osclass is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * vQmod for Osclass is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with vQmod for Osclass.  If not, see <https://www.gnu.org/licenses/>.
 */
 
define('IS_AJAX', true);

class CVQModAdminAjax extends AdminSecBaseModel
{
	//Business Layer...
    public function doModel()
    {
        switch (Params::getParam("route")) {
            case 'file_source_iframe':
                $file = Params::getParam('file');
                $path = vqmod_xml_path();
                $file = $path.$file;
                $source = vqmod_source_file($file);
                $this->_exportVariableToView('source', $source);
                $this->doView('admin/file_source_iframe.php');
                break;

            case 'log_source_iframe':
                $file = Params::getParam('file');
                $path = vqmod_logs_path();
                $file = $path.$file;
                $source = vqmod_source_file($file);
                $this->_exportVariableToView('source', $source);
                $this->doView('admin/file_source_iframe.php');
                break;

            case 'empty_vqmod_log':
                $file = Params::getParam('file');
                $path = vqmod_logs_path();
                $file = $path.$file;
                if (vqmod_empty_file($file)) {
                    echo json_encode(array('error' => 0));
                } else {
                    echo json_encode(array('error' => 1, 'msg' => __("The file could not be emptied.", 'vqmod')));
                }
                break;

            case 'delete_vqmod_log':
                osc_csrf_check();
                $file = Params::getParam('file');
                $path = vqmod_logs_path();
                $file = $path.$file;
                $deleted = false;
                if (file_exists($file)) {
                    if (@unlink($file)) $deleted = true;
                }       
                if ($deleted) {
                    echo json_encode(array('error' => 0));
                } else {
                    echo json_encode(array('error' => 1, 'msg' => __("The file could not be deleted.", 'vqmod')));
                }
                break;
            
            default:
                echo __('no action defined');
                break;
        }
    }

    //hopefully generic...
    function doView($file)
    {
        include VQMOD_PATH . 'views/'.$file;
    }
}