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
 * Helpers of vQmod for Osclass
 */

/**
 * Get path of vqmod folder production from the root
 */
function vqmod_path() {
    return osc_base_path() . 'vqmod/';
}

/**
 * Get vQmod's xml file path of production from the root
 */
function vqmod_xml_path() {
    return osc_base_path() . 'vqmod/xml/';
}

/**
 * Get the vQmod logs path of production from the root
 */
function vqmod_logs_path() {
    return osc_base_path() . 'vqmod/logs/';
}

/**
 * Get the vQmod cache path of production from the root
 */
function vqmod_cache_path() {
    return osc_base_path() . 'vqmod/vqcache/';
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @link        http://aidanlister.com/repos/v/function.copyr.php
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
if (!function_exists('copyr')) {
    function copyr($source, $dest) {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }
        
        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            copyr("$source/$entry", "$dest/$entry");
        }

        // Clean up
        $dir->close();
        return true;
    }
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
 * @param string $path Absolute route of file
 * @param string $fileName Name of file
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
 *
 * @param string $path Absolute route of file
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
 *
 * @param string $path Absolute route of file
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