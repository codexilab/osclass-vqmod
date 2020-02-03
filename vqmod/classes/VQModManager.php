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
 * vQmod Manager for Osclass
 */
class VQModManager
{
	
	private static $instance;

    /**
     * Singleton Pattern
     * 
     * @access public 
     */
	public static function newInstance()
	{
		if (!self::$instance instanceof self) {
			self::$instance = new self;
		}
		return self::$instance;
	}

    /**
     * VQModManager::newInstance()->status();
     *
     * @return bool
     * @description Check that the affected files are correctly modified
     */
    public function status()
    {
        $filesContent = array(
            file_get_contents(osc_base_path() . 'index.php'),
            file_get_contents(osc_admin_base_path() . 'index.php')
        );

        $started = 0;
        $installed = 0;
        
        foreach ($filesContent as $fileContent) {

            preg_match('~//VirtualQMOD 
    \$vqmod = ABS_PATH . \'vqmod/vqmod.php\'; 
    if \(isset\(\$vqmod\) && file_exists\(\$vqmod\)\) require_once\(\$vqmod\); if \(class_exists\(\"VQMod"\)\) VQMod::bootup\(\);~', $fileContent, $VQMod_started);

            if ($VQMod_started) {
                $started++;
            }
            
            preg_match('~if \(isset\(\$vqmod\) && file_exists\(\$vqmod\) && class_exists\(\"VQMod"\)\) : require_once\(VQMod::modCheck\(([^";]+)\)\); else : require_once ([^";]+); endif;~', $fileContent, $VQMod_installed);

            if ($VQMod_installed) {
                $installed++;
            }
        
        }

        // Check if exist vqmod folder in the root
        if (file_exists(vqmod_path()) && is_dir(vqmod_path()))
            // Check that the affected files are correctly modified
            if ($started == 2 && $installed == 2) return true;

        // If none of the two previous cases occur
        return false;
    }

    /**
     * VQModManager::newInstance()->purgeCache($checked_cache, $mods_cache, $vqmod_cache);
     *
     * @param bool $checked_cache Check if you want to delete checked.cache file
     * @param bool $mods_cache Check if you want to delete mods.cache file
     * @param bool $vqmod_cache Check if you want to delete overwritten files
     * @description Purge cache
     */
    public function purgeCache($checked_cache = null, $mods_cache = null, $vqmod_cache = null)
    {
        if ($checked_cache == null) $checked_cache = true;
        if ($mods_cache == null) $mods_cache = true;
        if ($vqmod_cache == null) $vqmod_cache = true;
        
        if ($checked_cache == true) {
            $file = vqmod_path().'checked.cache';
            if (file_exists($file))
                @unlink($file);
        }

        if ($mods_cache == true) {
            $file = vqmod_path().'mods.cache';
            if (file_exists($file))
                @unlink($file);
        }

        if ($vqmod_cache == true) {
            $vqcache_dir = vqmod_cache_path();
            if (file_exists($vqcache_dir) && is_dir($vqcache_dir)) {
                $files = glob($vqcache_dir.'*');
                foreach ($files as $file) { // iterate files
                    if (is_file($file))
                        @unlink($file); // delete file
                }
            }
        }
    }

	public function install()
    {
        // Counter errors of initialitation
        $write_errors = array();

        // Preparing the environment
        $VQModPath = VQMOD_PLUGIN_PATH . 'vqmod/';
        if (!is_writeable($VQModPath)) {
            chmod($VQModPath, 0777);
        }
        if (!is_writeable($VQModPath)) {
            $write_errors[] = $VQModPath.' could not change to writable';
        }

        // Copy entire the original and clean vqmod folder to the root
        if (!copyr($VQModPath, vqmod_path())) {
            $write_errors[] = $VQModPath.' could not be copied to the root';
        }

        if(!empty($write_errors)) {
            return(implode('<br />', $write_errors));
        }


        // Verify path is correct
        if(empty(osc_base_path())) return('ERROR - COULD NOT DETERMINE CENTRAL PATH CORRECTLY - ' . dirname(__FILE__));

        if ($this->status()) {
            return 'VQMOD ALREADY INSTALLED!';
        }

        // Get original permissions of index files
        $mainIndexPerms = substr(sprintf('%o', fileperms(osc_base_path() . 'index.php')), -4);
        $adminIndexPerms = substr(sprintf('%o', fileperms(osc_admin_base_path() . 'index.php')), -4);

        if(!is_writeable(osc_base_path() . 'index.php')) @chmod(osc_base_path() . 'index.php', 0777);
        if(!is_writeable(osc_admin_base_path() . 'index.php')) @chmod(osc_admin_base_path() . 'index.php', 0777);

        
        if(!is_writeable(osc_base_path() . 'index.php')) {
            return 'index.php is not writable in ' . osc_base_path();
        }
        if(!is_writeable(osc_admin_base_path() . 'index.php')) {
            return 'index.php is not writable in ' . osc_admin_base_path();
        }


        $changes = 0;
        $writes = 0;
        $i = 0;

        // Create new UGRSR class
        $u = new UGRSR(osc_base_path());

        // Set file searching to off
        $u->file_search = false;

        /*** START ITERATION 1 ***/
        $u->addFile('index.php');
        $u->addFile(basename(osc_admin_base_path()) . '/index.php');

        // Pattern to add vqmod include
        $pattern_array = array();

        // Pattern to run required files through vqmod
        $pattern_array['pattern'] = '/require_once([^";]+);/';
        $pattern_array['replace'] = 'if (isset($vqmod) && file_exists($vqmod) && class_exists("VQMod")) : require_once(VQMod::modCheck($1)); else : require_once$1; endif;';
        $u->addPattern($pattern_array['pattern'], $pattern_array['replace']);

        // Get number of changes during run
        $result = $u->run();
        $writes += $result['writes'];
        $changes += $result['changes'];
        $i++; $i++; // Add one more because in this iteration there are two files that are being modified
        /***** END ITERATION *****/


        /*** START ITERATION 2 ***/
        $u->clearPatterns();
        $u->resetFileList();

        // Add catalog index files to files to include
        $u->addFile('index.php');

        $pattern_array['pattern'] = '~define\(\'CLI\', true\);
    }~';

        $pattern_array['replace'] = 'define(\'CLI\', true);
    }

    //VirtualQMOD 
    $vqmod = ABS_PATH . \'vqmod/vqmod.php\'; 
    if (isset($vqmod) && file_exists($vqmod)) require_once($vqmod); if (class_exists("VQMod")) VQMod::bootup();';

        $u->addPattern($pattern_array['pattern'], $pattern_array['replace']);

        $result = $u->run();
        $writes += $result['writes'];
        $changes += $result['changes'];
        $i++;
        /***** END ITERATION *****/


        /*** START ITERATION 3 ***/
        $u->clearPatterns();
        $u->resetFileList();

        // Add catalog index files to files to include
        $u->addFile(basename(osc_admin_base_path()) . '/index.php');

        // Pattern to add vqmod include
        $pattern_array['pattern'] = '~define\(\'OC_ADMIN\', true\);~';

        $pattern_array['replace'] = 'define(\'OC_ADMIN\', true);

    //VirtualQMOD 
    $vqmod = ABS_PATH . \'vqmod/vqmod.php\'; 
    if (isset($vqmod) && file_exists($vqmod)) require_once($vqmod); if (class_exists("VQMod")) VQMod::bootup();';

        $u->addPattern($pattern_array['pattern'], $pattern_array['replace']);

        $result = $u->run();
        $writes += $result['writes'];
        $changes += $result['changes'];
        $i++;
        /***** END ITERATION *****/

        // Restore original permissions of index files
        @chmod(osc_base_path() . 'index.php', $mainIndexPerms);
        @chmod(osc_admin_base_path() . 'index.php', $adminIndexPerms);

        // Output result to user
        if(!$changes) return('VQMOD ALREADY INSTALLED!');
        if($writes != $i) return('ONE OR MORE FILES COULD NOT BE WRITTEN IN '. osc_base_path() . ' OR '. osc_admin_base_path());
        return('VQMOD HAS BEEN INSTALLED ON YOUR SYSTEM!');
    }

    public function uninstall()
    {
        // Verify path is correct
        if(empty(osc_base_path())) return('ERROR - COULD NOT DETERMINE CENTRAL PATH CORRECTLY - ' . dirname(__FILE__));

        /* Remove vqmod folder from root
        if (!osc_deleteDir(vqmod_path())) {
            $write_errors[] = 'vqmod/ folder could not be removed from the root: ' . osc_base_path();
        }*/

        // Get original permissions of index files
        $mainIndexPerms = substr(sprintf('%o', fileperms(osc_base_path() . 'index.php')), -4);
        $adminIndexPerms = substr(sprintf('%o', fileperms(osc_base_path() . 'index.php')), -4);

        if(!is_writeable(osc_base_path() . 'index.php')) @chmod(osc_base_path(). 'index.php', 0777);
        if(!is_writeable(osc_admin_base_path() . 'index.php')) @chmod(osc_admin_base_path() . 'index.php', 0777);

        if(!is_writeable(osc_base_path() . 'index.php')) {
            return 'index.php is not writable in ' . osc_base_path();
        }
        if(!is_writeable(osc_admin_base_path() . 'index.php')) {
            return 'index.php is not writable in ' . osc_admin_base_path();
        }


        // Counters
        $changes = 0;
        $writes = 0;
        $i = 0;

        // Create new UGRSR class
        $u = new UGRSR(osc_base_path());

        // Set file searching to off
        $u->file_search = false;

        /*** START ITERATION 1 ***/
        // Add catalog index files to files to include
        $u->addFile('index.php');

        // Pattern to add vqmod include
        $pattern_array = array();

        $pattern_array['pattern'] = '~define\(\'CLI\', true\);
    }

    //VirtualQMOD 
    \$vqmod = ABS_PATH . \'vqmod/vqmod.php\'; 
    if \(isset\(\$vqmod\) && file_exists\(\$vqmod\)\) require_once\(\$vqmod\); if \(class_exists\(\"VQMod"\)\) VQMod::bootup\(\);~';

        $pattern_array['replace'] = 'define(\'CLI\', true);
    }';

        $u->addPattern($pattern_array['pattern'], $pattern_array['replace']);

        $result = $u->run();
        $writes += $result['writes'];
        $changes += $result['changes'];
        $i++;
        /***** END ITERATION *****/


        /*** START ITERATION 2 ***/
        $u->clearPatterns();
        $u->resetFileList();

        // Add catalog index files to files to include
        $u->addFile(basename(osc_admin_base_path()) . '/index.php');

        $pattern_array['pattern'] = '~define\(\'OC_ADMIN\', true\);

    //VirtualQMOD 
    \$vqmod = ABS_PATH . \'vqmod/vqmod.php\'; 
    if \(isset\(\$vqmod\) && file_exists\(\$vqmod\)\) require_once\(\$vqmod\); if \(class_exists\(\"VQMod"\)\) VQMod::bootup\(\);~';

        $pattern_array['replace'] = 'define(\'OC_ADMIN\', true);';

        $u->addPattern($pattern_array['pattern'], $pattern_array['replace']);

        $result = $u->run();
        $writes += $result['writes'];
        $changes += $result['changes'];
        $i++;
        /***** END ITERATION *****/


        /*** START ITERATION 4 ***/
        $u->clearPatterns();
        $u->resetFileList();

        $u->addFile('index.php');
        $u->addFile(basename(osc_admin_base_path()) . '/index.php');

        // Pattern to run required files through vqmod
        $pattern_array['pattern'] = '~if \(isset\(\$vqmod\) && file_exists\(\$vqmod\) && class_exists\(\"VQMod"\)\) : require_once\(VQMod::modCheck\(([^";]+)\)\); else : require_once([^";]+); endif;~';
        $pattern_array['replace'] = 'require_once$1;';
        
        $u->addPattern($pattern_array['pattern'], $pattern_array['replace']);

        // Get number of changes during run
        $result = $u->run();
        $writes += $result['writes'];
        $changes += $result['changes'];
        $i++; $i++; // Add one more because in this iteration there are two files that are being modified
        /***** END ITERATION *****/

        // Restore original permissions of index files
        @chmod(osc_base_path() . 'index.php', $mainIndexPerms);
        @chmod(osc_admin_base_path() . 'index.php', $adminIndexPerms);

        // Output result to user
        if(!$changes) return('VQMOD ALREADY UNINSTALLED!');
        if($writes != $i) return('ONE OR MORE FILES COULD NOT BE WRITTEN IN '. osc_base_path() . ' OR ' . osc_admin_base_path());
        return('VQMOD HAS BEEN UNINSTALLED ON YOUR SYSTEM!');
    }
}