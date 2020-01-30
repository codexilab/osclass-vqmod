<?php
/**
 * vQmod Manager for Osclass
 * @author CodexiLab
 */
class VQModManager
{
	
	private static $instance;

    public static $path;
    public static $admin_path;

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
	
	function __construct() {
        // Get directory two above installation directory (e. g.: /var/www/html/osclass/)
        self::$path = realpath(dirname(__FILE__) . '/../../../../') . '/';

        // CHANGE THIS IF YOU EDIT YOUR ADMIN FOLDER NAME
        self::$admin_path = 'oc-admin';
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
            file_get_contents(self::$path . 'index.php'),
            file_get_contents(self::$path . self::$admin_path . '/index.php')
        );

        $started = 0;
        $installed = 0;
        
        foreach ($filesContent as $fileContent) {

            preg_match('~//VirtualQMOD 
    \$vqmod = ABS_PATH . \'oc-content/plugins/vqmod/classes/VQMod.php\'; 
    if \(isset\(\$vqmod\) && file_exists\(\$vqmod\)\) require_once\(\$vqmod\); if \(class_exists\(\"VQMod"\)\) VQMod::bootup\(\);~', $fileContent, $VQMod_started);

            if ($VQMod_started) {
                $started++;
            }
            
            preg_match('~if \(isset\(\$vqmod\) && file_exists\(\$vqmod\) && class_exists\(\"VQMod"\)\) : require_once\(VQMod::modCheck\(([^";]+)\)\); else : require_once ([^";]+); endif;~', $fileContent, $VQMod_installed);

            if ($VQMod_installed) {
                $installed++;
            }
        
        }

        if ($started == 2 && $installed == 2) return true;
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
        // Preparing the environment
        $VQModPath = VQMOD_PATH . 'vqmod/';
        if(!is_writeable($VQModPath)) {
            chmod($VQModPath, 0777);
        }
        if(!is_writeable($VQModPath)) {
            $write_errors[] = $VQModPath.' could not change to writable';
        }

        // Counters
        $write_errors = array();
        $changes = 0;
        $writes = 0;
        $i = 0;

        // Verify path is correct
        if(empty(self::$path)) return('ERROR - COULD NOT DETERMINE CENTRAL PATH CORRECTLY - ' . dirname(__FILE__));

        if ($this->status()) {
            return 'VQMOD ALREADY INSTALLED!';
        }

        // Get original permissions of index files
        $mainIndexPerms = substr(sprintf('%o', fileperms(self::$path . 'index.php')), -4);
        $adminIndexPerms = substr(sprintf('%o', fileperms(self::$path . self::$admin_path . '/index.php')), -4);

        if(!is_writeable(self::$path . 'index.php')) @chmod(self::$path . 'index.php', 0777);
        if(!is_writeable(self::$path . self::$admin_path . '/index.php')) @chmod(self::$path . self::$admin_path . '/index.php', 0777);

        
        if(!is_writeable(self::$path . 'index.php')) {
            $write_errors[] = 'index.php not writeable in ' . self::$path;
        }
        if(!is_writeable(self::$path . self::$admin_path . '/index.php')) {
            $write_errors[] = 'index.php not writeable in ' . self::$admin_path;
        }

        if(!empty($write_errors)) {
            return(implode('<br />', $write_errors));
        }

        // Create new UGRSR class
        $u = new UGRSR(self::$path);

        // Set file searching to off
        $u->file_search = false;

        /*** START ITERATION 1 ***/
        $u->addFile('index.php');
        $u->addFile(self::$admin_path . '/index.php');

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
    $vqmod = ABS_PATH . \'oc-content/plugins/vqmod/classes/VQMod.php\'; 
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
        $u->addFile(self::$admin_path . '/index.php');

        // Pattern to add vqmod include
        $pattern_array['pattern'] = '~define\(\'OC_ADMIN\', true\);~';

        $pattern_array['replace'] = 'define(\'OC_ADMIN\', true);

    //VirtualQMOD 
    $vqmod = ABS_PATH . \'oc-content/plugins/vqmod/classes/VQMod.php\'; 
    if (isset($vqmod) && file_exists($vqmod)) require_once($vqmod); if (class_exists("VQMod")) VQMod::bootup();';

        $u->addPattern($pattern_array['pattern'], $pattern_array['replace']);

        $result = $u->run();
        $writes += $result['writes'];
        $changes += $result['changes'];
        $i++;
        /***** END ITERATION *****/

        // Restore original permissions of index files
        @chmod(self::$path . 'index.php', $mainIndexPerms);
        @chmod(self::$admin_path . '/index.php', $adminIndexPerms);

        // Output result to user
        if(!$changes) return('VQMOD ALREADY INSTALLED!');
        if($writes != $i) return('ONE OR MORE FILES COULD NOT BE WRITTEN IN '. self::$path);
        return('VQMOD HAS BEEN INSTALLED ON YOUR SYSTEM!');
    }

    public function uninstall()
    {
        // Counters
        $write_errors = array();
        $changes = 0;
        $writes = 0;
        $i = 0;

        // Verify path is correct
        if(empty(self::$path)) return('ERROR - COULD NOT DETERMINE CENTRAL PATH CORRECTLY - ' . dirname(__FILE__));

        // Get original permissions of index files
        $mainIndexPerms = substr(sprintf('%o', fileperms(self::$path . 'index.php')), -4);
        $adminIndexPerms = substr(sprintf('%o', fileperms(self::$path . self::$admin_path . '/index.php')), -4);

        if(!is_writeable(self::$path . 'index.php')) @chmod(self::$path . 'index.php', 0777);
        if(!is_writeable(self::$path . self::$admin_path . '/index.php')) @chmod(self::$path . self::$admin_path . '/index.php', 0777);

        if(!is_writeable(self::$path . 'index.php')) {
            $write_errors[] = 'index.php not writeable in ' . self::$path;
        }
        if(!is_writeable(self::$path . self::$admin_path . '/index.php')) {
            $write_errors[] = 'index.php not writeable in ' . self::$admin_path;
        }

        if(!empty($write_errors)) {
            return(implode('<br />', $write_errors));
        }

        // Create new UGRSR class
        $u = new UGRSR(self::$path);

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
    \$vqmod = ABS_PATH . \'oc-content/plugins/vqmod/classes/VQMod.php\'; 
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
        $u->addFile(self::$admin_path . '/index.php');

        $pattern_array['pattern'] = '~define\(\'OC_ADMIN\', true\);

    //VirtualQMOD 
    \$vqmod = ABS_PATH . \'oc-content/plugins/vqmod/classes/VQMod.php\'; 
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
        $u->addFile(self::$admin_path . '/index.php');

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
        @chmod(self::$path . 'index.php', $mainIndexPerms);
        @chmod(self::$admin_path . '/index.php', $adminIndexPerms);

        // Output result to user
        if(!$changes) return('VQMOD ALREADY UNINSTALLED!');
        if($writes != $i) return('ONE OR MORE FILES COULD NOT BE WRITTEN IN '. self::$path);
        return('VQMOD HAS BEEN UNINSTALLED ON YOUR SYSTEM!');
    }
}