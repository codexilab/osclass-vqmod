<?php
/**
 * @author Adrián Olmedo <adrianolmedo.ve@gmail.com>
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