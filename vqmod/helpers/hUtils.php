<?php
/**
 * VQMod Helpers
 * @author CodexiLab
 */

/**
 * Get installation path vQmod
 * vqmod_path()
 */
function vqmod_path() {
    return VQMOD_PATH . 'vqmod/';
}

/**
 * Get vQmod's xml file path
 * vqmod_xml_path()
 */
function vqmod_xml_path() {
    return VQMOD_PATH . 'vqmod/xml/';
}

/**
 * Get the vQmod logs path.
 * vqmod_logs_path()
 */
function vqmod_logs_path() {
    return VQMOD_PATH . 'vqmod/logs/';
}

/**
 * Get the vQmod cache path
 */
function vqmod_cache_path() {
    return VQMOD_PATH . 'vqmod/vqcache/';
}

/**
 * Empty a file.
 *
 * @param string $file
 */
function vqmod_empty_file($file) {
    $result = false;
    if (!is_writable($file)) @chmod($file, 0777);
    $f = @fopen($file, "r+");
    if ($f !== false) {
        if (ftruncate($f, 0)) $result = true;
        fclose($f);
    }
    return $result;
}

/**
 * Get source file of a file (parsed with htmlspecialchars).
 *
 * @param string $file
 */
function vqmod_source_file($file) {
    $source = "";
    if (file_exists($file) && !is_dir($file)) {
        $source = htmlspecialchars(file_get_contents($file));
    }
    return $source;
}

/**
 * Format a file size information from bytes (default) to:
 *
 * - Kilobytes (kB)
 * - Megabytes (Mb)
 * - Gigabytes (GB)
 * - Terabytes (TB)
 *
 * @param int $bytes
 * @param int $precision default value 1
 */
function formatBytes($bytes, $precision = 1) {
    $base       = log($bytes, 1024);
    $suffixes   = array('bytes', 'kB', 'Mb', 'GB', 'TB');
    $units      = round(pow(1024, $base - floor($base)), $precision);
    $format     = $suffixes[floor($base)];
    if ($units.' '.$format == 'NAN bytes') {
        return '0 '.$format;
    }
    return $units.' '.$format;
}

/**
 * Get file size of a file.
 *
 * @param string $path
 * @param string $fileName
 */
function vqmod_get_filesize($path, $fileName) {
    $file = $path.$fileName;
    if (file_exists($file) && !is_dir($file)) {
        $filesize = filesize($file);
        return formatBytes($filesize);
    }
    return 0;
}

/**
 * Get a array of mods xml files.
 */
function vqmod_get_mods($path = null) {
    if ($path == null) $path = vqmod_xml_path();

    $mods = array();
    if(file_exists($path) && is_dir($path) && $gestor = opendir($path)) {
        while (($file = readdir($gestor)) !== false) { 
            if ((!is_file($file)) && ($file != '.') && ($file != '..')) {
                $mods[$file] = $file;
            }
        }; closedir($gestor);
    }
    return $mods;
}

/**
 * Get a array of logs files
 */
function vqmod_get_logs($path = null) {
    if ($path == null) $path = vqmod_logs_path();

    $logs = array();
    if(file_exists($path) && is_dir($path) && $gestor = opendir($path)) {
        while (($file = readdir($gestor)) !== false) { 
            if ((!is_file($file)) && ($file != '.') && ($file != '..') && (preg_match('/^.*\.(log)$/i', $file))) {
                $logs[$file] = $file;
            }
        }; closedir($gestor);
    }
    return $logs;
}