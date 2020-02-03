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
 * Controller mods system with VQmod
 */
class CAdminVQMod extends AdminSecBaseModel
{

    //Business Layer...
    public function doModel()
    {

        switch (Params::getParam('plugin_action')) {
            case 'install_vqmod':
                osc_add_flash_info_message(VQModManager::newInstance()->install(), 'admin');
                ob_get_clean();
                $this->redirectTo(osc_route_admin_url('vqmod-admin-mods'));
                break;

            case 'uninstall_vqmod':
                osc_add_flash_info_message(VQModManager::newInstance()->uninstall(), 'admin');
                ob_get_clean();
                $this->redirectTo(osc_route_admin_url('vqmod-admin-mods'));
                break;

            case 'add_mod':
                $path = vqmod_xml_path();

                if(!is_writeable($path)) {
                    @chmod($path, 0777);
                }

                $zip = Params::getFiles("mod");
                if(isset($zip['size']) && $zip['size']!=0) {
                    // 1) Check if the repeated files (file.xml and file.xml.etc are the same files)
                    $zipFiles = array(); $pathFiles = array(); $allFiles = array(); $dups = array();
                    $za = new ZipArchive();
                    
                    // 2) Open temporal zip file
                    $za->open($zip['tmp_name']);

                    // 3) Go through all the files and collect all the names in an array (files)
                    if ($za->numFiles > 0) {
                        for ($i = 0; $i < $za->numFiles; $i++) { 
                            $stat = $za->statIndex($i);
                            $zipFiles[] = current(explode(".", basename($stat['name'])));
                            // Adiotinally, rename (.disabled) by default all mods before put in the xml folder
                            $za->renameName(basename($stat['name']), current(explode(".", basename($stat['name']))).'.xml.disabled');
                        }
                    }

                    $za->close(); // Close zip! we not need more

                    // 4) Prepare xml mods to merge and detect repeated, as well as from the zip and xml path
                    $mods = vqmod_get_mods();
                    if ($mods) {
                        foreach ($mods as $mod) {
                            $pathFiles[] = current(explode(".", $mod));
                        }
                    }
                    $allFiles = array_merge($zipFiles, $pathFiles);
                    
                    // 5) Go through the list of names in the array and compare duplicates, if they exist, collect them in another array (dups)
                    if ($allFiles) {
                        foreach (array_count_values($allFiles) as $val => $c) {
                            if($c > 1) $dups[] = $val;
                        }
                    }

                    // 6) Account from array the number of duplicates
                    if (count($dups) <= 0) {
                        (int) $status = osc_unzip_file($zip['tmp_name'], $path);
                    } else {
                        $status = 4;
                    }

                    @unlink($zip['tmp_name']);
                } else {
                    $status = 3;
                }
                switch ($status) {
                    case(0):    $msg = __("The xml mods folder is not writable", 'vqmod');
                                osc_add_flash_error_message($msg, 'admin');
                    break;
                    case(1):    $msg = __("The mod file has been uploaded correctly", 'vqmod');
                                osc_add_flash_ok_message($msg, 'admin');
                    break;
                    case(2):    $msg = __("The zip file is not valid", 'vqmod');
                                osc_add_flash_error_message($msg, 'admin');
                    break;
                    case(3):    $msg = __("No file was uploaded", 'vqmod');
                                osc_add_flash_error_message($msg, 'admin');
                    break;
                    case(4):    $msg = __("There are files repeated", 'vqmod');
                                osc_add_flash_error_message($msg, 'admin');
                    break;
                    case(-1):
                    default:    $msg = __("There was a problem adding the mod", 'vqmod');
                                osc_add_flash_error_message($msg, 'admin');
                    break;
                }
                ob_get_clean();
                $this->redirectTo(osc_route_admin_url('vqmod-admin-mods'));
                break;

            case 'enable':
                $path = vqmod_xml_path();

                $enabled = 0;
                $mods = Params::getParam('id');

                if (!is_array($mods)) {
                    osc_add_flash_error_message(__("Select a mod.", 'vqmod'), 'admin');
                } else {

                    foreach ($mods as $mod) {
                        $mod = $path.$mod;
                        if (file_exists($mod.'.xml.disabled') && !is_dir($mod.'.xml.disabled') && $mod.'.xml' != $path.'index.xml' && !file_exists($mod.'.xml')) {
                            if (rename($mod.'.xml.disabled', $mod.'.xml')) $enabled++;
                        }
                    }

                    if ($enabled > 0) {
                        osc_add_flash_ok_message(__("Mod(s) files(s) have been enabled.", 'vqmod'), 'admin');
                    } else {
                        osc_add_flash_error_message(__("No mod file have been enabled.", 'vqmod'), 'admin');
                    }
                }
                ob_get_clean();
                $this->redirectTo($_SERVER['HTTP_REFERER']);
                break;

            case 'disable':
                $path = vqmod_xml_path();

                $enabled = 0;
                $mods = Params::getParam('id');

                if (!is_array($mods)) {
                    osc_add_flash_error_message(__("Select a mod.", 'vqmod'), 'admin');
                } else {

                    foreach ($mods as $mod) {
                        $mod = $path.$mod;
                        if (file_exists($mod.'.xml') && !is_dir($mod.'.xml') && $mod.'.xml' != $path.'index.xml' && !file_exists($mod.'.xml.disabled')) {
                            if (rename($mod.'.xml', $mod.'.xml.disabled')) $enabled++;
                        }
                    }

                    if ($enabled > 0) {
                        osc_add_flash_ok_message(__("Mod(s) files(s) have been disabled.", 'vqmod'), 'admin');
                    } else {
                        osc_add_flash_error_message(__("No mod file have been disabled.", 'vqmod'), 'admin');
                    }
                }
                ob_get_clean();
                $this->redirectTo($_SERVER['HTTP_REFERER']);
                break;

            case 'delete':
                $path = vqmod_xml_path();

                $deleted = 0;
                $mods = Params::getParam('id');

                if (!is_array($mods)) {
                    osc_add_flash_error_message(__("Select a mod.", 'vqmod'), 'admin');
                } else {
                    // Enabled and disabled versions are the same file instance
                    foreach ($mods as $mod) {
                        // Delete enabled version
                        $file = $path.$mod.'.xml';
                        if (file_exists($file) && !is_dir($file) && $file != $path.'index.xml') {
                            if (!is_writeable($file)) @chmod($file, 0777);
                            if (unlink($file)) $deleted++;
                        }
                        
                        // Delete disabled version
                        $file = $path.$mod.'.xml.disabled';
                        if (file_exists($file) && !is_dir($file)) {
                            if (!is_writeable($file)) @chmod($file, 0777);
                            if (unlink($file)) $deleted++;
                        }
                    }

                    if ($deleted > 0) {
                        osc_add_flash_ok_message(__("Mod(s) files(s) have been deleted.", 'vqmod'), 'admin');
                    } else {
                        osc_add_flash_error_message(__("No mod file have been deleted.", 'vqmod'), 'admin');
                    }
                }
                ob_get_clean();
                $this->redirectTo($_SERVER['HTTP_REFERER']);
                break;

            case 'purge_cache':
                $write_notif = array();

                if (Params::getParam('purge_vqmod_cache')) {
                    $vqcache_dir = vqmod_cache_path();
                    $deleted = 0;
                    if (file_exists($vqcache_dir) && is_dir($vqcache_dir)) {
                        $files = glob($vqcache_dir.'*');
                        foreach($files as $file){ // iterate files
                            if(is_file($file))
                                if (@unlink($file)) $deleted++; // delete file
                        }
                        
                        $countFiles = count($files);
                        if ($countFiles == $deleted) {
                            $write_notif[] = __("- The entire cache was deleted", 'vqmod');
                        } else {
                            $write_notif[] = __('- Only '.$deleted.' of '.$countFiles.' files could be deleted', 'vqmod');    
                        }
                    } else {
                        $write_notif[] = __("The cache is empty", 'vqmod');
                    }
                }

                if (Params::getParam('purge_checked_cache')) {
                    $file = vqmod_path().'checked.cache';
                    $deleted = 0;
                    if (file_exists($file)) {
                        if (@unlink($file)) $deletd++;
                    }

                    if ($deleted) {
                        $write_notif[] = __("- checked.cache was successfully deleted", 'vqmod');
                    } else {

                        $write_notif[] = __("- checked.cache already not exists", 'vqmod');
                    }
                }

                if (Params::getParam('purge_mods_cache')) {
                    $file = vqmod_path().'mods.cache';
                    $deleted = 0;
                    if (file_exists($file)) {
                        if (@unlink($file)) $deletd++;
                    }

                    if ($deleted) {
                        $write_notif[] = __("- mods.cache was successfully deleted", 'vqmod');
                    } else {
                        $write_notif[] = __("- mods.cache already not exists", 'vqmod');
                    }
                }

                if (empty($write_notif)) {
                    osc_add_flash_info_message(__("Select an option", 'vqmod'), 'admin');
                } else {
                    osc_add_flash_info_message(implode('<br />', $write_notif), 'admin');
                }
                ob_get_clean();
                $this->redirectTo(osc_route_admin_url('vqmod-admin-mods'));
                break;
            
            default:
                $numLogs = count(vqmod_get_logs());
                $this->_exportVariableToView('numLogs', $numLogs);

                // DataTable
                require_once VQMOD_PLUGIN_PATH . "classes/datatables/ModsDataTable.php";

                $modsDataTable = new ModsDataTable();
                $modsDataTable->table();
                $aData = @$modsDataTable->getData();
                $this->_exportVariableToView('aData', $aData);

                $bulk_options = array(
                    array('value' => '', 'data-dialog-content' => '', 'label' => __("Bulk actions", 'vqmod')),
                    array('value' => 'enable', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected mod file?', 'vqmod'), strtolower(__("Enable", 'vqmod'))), 'label' => __("Enable", 'vqmod')),
                    array('value' => 'disable', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected mod file?', 'vqmod'), strtolower(__("Disable", 'vqmod'))), 'label' => __("Disable", 'vqmod')),
                    array('value' => 'delete', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected mod file?', 'vqmod'), strtolower(__("Delete", 'vqmod'))), 'label' => __("Delete", 'vqmod'))
                );

                $bulk_options = osc_apply_filter("mods_bulk_filter", $bulk_options);
                $this->_exportVariableToView('bulk_options', $bulk_options);

                $status = VQModManager::newInstance()->status();
                $this->_exportVariableToView('status', $status);
                break;
        }
    }
    
}