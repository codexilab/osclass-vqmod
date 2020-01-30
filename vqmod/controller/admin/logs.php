<?php
/*
 * Copyright 2019 CodexiLab
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
 
/**
 * Controller Integration mods system with VQmod
 */
class CAdminVQModLogs extends AdminSecBaseModel
{

    //Business Layer...
    public function doModel()
    {

        switch (Params::getParam('plugin_action')) {
            case 'download_vqmod_log':
                osc_csrf_check();

                $file = Params::getParam('file');
                $path = vqmod_logs_path();
                $filepath = $path.$file;

                if (preg_match('/^.*\.(log)$/i', $file) && (file_exists($filepath))) {
                    header('Content-Type: application/log');
                    header('Content-Disposition: attachment; filename="'.$file.'"');
                    readfile($filepath);
                    exit;
                } else {
                    ob_get_clean();
                    $this->redirectTo(osc_route_admin_url('vqmod-admin-mods'));
                }
                break;

            default:
                $logs = vqmod_get_logs();
                $this->_exportVariableToView('logs', $logs);
                break;
        }
    }
    
}