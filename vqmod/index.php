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

/*
Plugin Name: vQmod™ for Osclass
Plugin URI: https://github.com/codexilab/osclass-vqmod
Description: vQmod™ (aka Virtual Quick Mod) is a new innovation in php modification override methods.
Version: 1.2.1
Author: CodexiLab
Author URI: https://github.com/codexilab
Short Name: vqmod
Plugin update URI: https://github.com/codexilab/osclass-vqmod
*/

	// Paths
	define('VQMOD_PLUGIN_FOLDER', 'vqmod/');
	define('VQMOD_PLUGIN_PATH', osc_plugins_path().VQMOD_PLUGIN_FOLDER);

	
	// Prepare model, controllers and helpers
	require_once VQMOD_PLUGIN_PATH . "oc-load.php";

	
	// URL routes
	osc_add_route('vqmod-admin-mods', VQMOD_PLUGIN_FOLDER.'admin/mods', VQMOD_PLUGIN_FOLDER.'admin/mods', VQMOD_PLUGIN_FOLDER.'views/admin/mods.php');
	osc_add_route('vqmod-admin-logs', VQMOD_PLUGIN_FOLDER.'admin/logs', VQMOD_PLUGIN_FOLDER.'admin/logs', VQMOD_PLUGIN_FOLDER.'views/admin/logs.php');

	
	// Headers in the admin panel
	osc_add_hook('admin_menu_init', function() {
		$VQMod_status = (VQModManager::newInstance()->status()) ? "on" : "off";
	    osc_add_admin_submenu_divider(
	        "plugins", __("vQmod ($VQMod_status)", 'vqmod'), "vqmod", "administrator"
	    );

	    osc_add_admin_submenu_page(
	        "plugins", __("Manage mods", 'vqmod'), osc_route_admin_url("vqmod-admin-mods"), "vqmod-admin-mods", "administrator"
	    );

	    osc_add_admin_submenu_page(
	        "plugins", __("Logs", 'vqmod'), osc_route_admin_url("vqmod-admin-logs"), "vqmod-admin-logs", "administrator"
	    );
	});


	// Load the controllers, depend of url route
	function vqmod_admin_controllers() {
		switch (Params::getParam("route")) {
			case 'vqmod-admin-mods':
				$filter = function($string) {
	                return __("vQmod™", 'vqmod');
	            };

	            // Page title (in <head />)
	            osc_add_filter("admin_title", $filter, 10);

	            // Page title (in <h1 />)
	            osc_add_filter("custom_plugin_title", $filter);

	            $do = new CAdminVQMod();
	            $do->doModel();
				break;

			case 'vqmod-admin-logs':
				$filter = function($string) {
	                return __("vQmod logs", 'vqmod');
	            };

	            // Page title (in <head />)
	            osc_add_filter("admin_title", $filter, 10);

	            // Page title (in <h1 />)
	            osc_add_filter("custom_plugin_title", $filter);

	            $do = new CAdminVQModLogs();
	            $do->doModel();
				break;
		}
	}
	osc_add_hook("renderplugin_controller", "vqmod_admin_controllers");

	
	// Add custom CSS Styles in oc-admin
	function vqmod_custom_css_admin() {
		if (Params::getParam('route') == "vqmod-admin-mods" || Params::getParam('route') == "vqmod-admin-logs") {
			osc_enqueue_style('fileManager', osc_base_url() . 'oc-content/plugins/' . VQMOD_PLUGIN_FOLDER . 'assets/css/admin/filemanager.css');
		}
	}
	osc_add_hook('init_admin', 'vqmod_custom_css_admin');


	/**
	 * The content of this function it will show by ajax request on this url:
	 * <?php echo osc_base_url(); ?>index.php?page=ajax&action=runhook&hook=vqmod_admin_ajax
	 */
	function vqmod_admin_ajax() {
		$do = new CVQModAdminAjax();
	    $do->doModel();
	}
	osc_add_hook("ajax_vqmod_admin_ajax", "vqmod_admin_ajax");


	/**
     * When a plugin is being deactivated:
     * 
	 * - a) Disable mod at time that disable other plugin with the same name
	 * - b) Purge cache
	 * - c) Disable vQmod if the plugin that is being deactivate is the same as this
	 *
	 * @param string $path e.g. my_plugin/index.php
	 */
    function before_vqmod_deactivate($path = null) {
    	// a) Disable mod
    	if ($path != null) {
    		$xmlModPath = vqmod_xml_path();
    		$mod = current(explode("/", $path)); 	// e.g. my_plugin
	    	$mod = $xmlModPath.$mod; 				// e.g. var/www/html/osclass/vqmod/vqmod/xml/my_plugin
	        if (file_exists($mod.'.xml') && !is_dir($mod.'.xml') && $mod.'.xml' != $xmlModPath.'index.xml' && $mod.'.xml' != $xmlModPath.current(explode("/", VQMOD_PLUGIN_FOLDER)).'.xml' && !file_exists($mod.'.xml.disabled')) {
	            @rename($mod.'.xml', $mod.'.xml.disabled');
	        	VQModManager::newInstance()->purgeCache();
	        }
    	}

        // b) Purge cache
    	VQModManager::newInstance()->purgeCache();

    	// c) Uninstall vQmod if the plugin that is being deactivate is the same as this
    	if ($path == VQMOD_PLUGIN_FOLDER.'index.php') {
    		osc_add_flash_info_message(VQModManager::newInstance()->uninstall(), 'admin');
    	}
    }
    osc_add_hook('before_plugin_deactivate', 'before_vqmod_deactivate');


    /**
     * When vQmod for Osclass is being unistalled: delete vqmod folder from the root
	 * @param string $path e.g. vqmod/index.php
	 */
    function before_vqmod_uninstall($path = null) {
    	if ($path == VQMOD_PLUGIN_FOLDER.'index.php') {
			if (!osc_deleteDir(vqmod_path())) {
				osc_add_flash_error_message(__("vqmod/ folder could not be removed from the root", 'vqmod'), 'admin');
			}
    	}
    }
    osc_add_hook('before_plugin_uninstall', 'before_vqmod_uninstall');


	// Uninstallation process
	function vqmod_uninstall() {
		Preference::newInstance()->delete(array('s_section' => 'vqmod'));
	}
	// Show an Uninstall link at plugins table
	osc_add_hook(osc_plugin_path(__FILE__) . '_uninstall', 'vqmod_uninstall');


	// Installation process
	function vqmod_install() {
		osc_set_preference('version', '1.2.1', 'vqmod', 'STRING');
	}
	// Register plugin's installation
	osc_register_plugin(osc_plugin_path(__FILE__), 'vqmod_install');