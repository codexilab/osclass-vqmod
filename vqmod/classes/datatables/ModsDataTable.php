<?php if ( ! defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

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

	class ModsDataTable extends DataTable
	{
		public function __construct()
        {
        	osc_add_filter('datatable_mods_status_class', array(&$this, 'row_class'));
            osc_add_filter('datatable_mods_status_text', array(&$this, '_status'));
        }

        /**
         * Build the table of all mods in the php file: views/admin/mods.php
         *
         * @access public
         * @param array $params
         * @return array
         */
        public function table($params = null)
        {
        	$this->addTableHeader();
            
            $mods = vqmod_get_mods();
            if (in_array('index.xml', $mods)) {
                unset($mods['index.xml']);
            }

            $total = count($mods);

            $start = $total;

            $this->start = intval($start);
            $this->limit = intval($total);

            $this->processData($mods);

            $this->total = $total;
            $this->total_filtered = $this->total;

            return @$this->getData();
        }

        private function addTableHeader()
        {
            $this->addColumn('status-border', '');
            $this->addColumn('status', __("Status", 'vqmod'));
            $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');

            $this->addColumn('title', __("Title", 'vqmod'));
            $this->addColumn('file-name', __("Item ID", 'vqmod'));
            $this->addColumn('author', __("Author", 'vqmod'));

            $dummy = &$this;
            osc_run_hook("admin_mods_table", $dummy);
        }

        private function processData($mods)
        {
            if(!empty($mods)) {

                $i = 0;
                foreach($mods as $aRow) {
                    $i++;
                    $row = array();
                    $options = array();

                    $mod = current(explode(".", $aRow));
                    $modParts = pathinfo(vqmod_xml_path().$aRow);
                    $status = ($modParts['extension'] == 'xml') ? 1 : 0;

                    $options[] = '<a href="#" onclick="opensource('.$i.', \''.$aRow.'\');return false;">' . __("View source", 'vqmod') . '</a>';
                    if ($status == 1) {
                        $options[] = '<a href="#" onclick="disable_mod_dialog(\''.$mod.'\');return false;">' . __("Disable", 'vqmod') . '</a>';
                    } else {
                        $options[] = '<a href="#" onclick="enable_mod_dialog(\''.$mod.'\');return false;">' . __("Enable", 'vqmod') . '</a>';
                    }
                    $options[] = '<a href="#" onclick="delete_file(\''.$mod.'\');return false;">' . __("Delete", 'vqmod') . '</a>';

                    $actions = '';
                    if (count($options) > 0) {
                        $options = osc_apply_filter('actions_manage_mods', $options, $aRow);
                        // create list of actions
                        $auxOptions = '<ul>'.PHP_EOL;
                        foreach( $options as $actual ) {
                            $auxOptions .= '<li>'.$actual.'</li>'.PHP_EOL;
                        }
                        $auxOptions  .= '</ul>'.PHP_EOL;

                        $actions = '<div class="actions">'.$auxOptions.'</div>'.PHP_EOL;
                    }

                    $xml = simplexml_load_file(vqmod_xml_path().$aRow);
                    $modVersion = (isset($xml->version)) ? (string) $xml->version : '';
                    $modTitle = (isset($xml->id)) ? (string) $xml->id : '';
                    $modTitle = ($modTitle != '') ? $modTitle." (v$modVersion)" : $mod;
                    $modAuthor = (isset($xml->author)) ? (string) $xml->author : '';
                    
                    
                    $row['status-border']   = '';

                    $row['status']          = $status;
                    $row['bulkactions']     = '<input type="checkbox" name="id[]" value="'.$mod.'" />';
                    $row['title']           = $modTitle . $actions;
                    $row['file-name']       = $mod;
                    $row['author']          = $modAuthor;

                    $row = osc_apply_filter('mods_processing_row', $row, $aRow);

                    $this->addRow($row);
                    $this->rawRows[] = $aRow;
                }

            }
        }

        public function _status($status)
        {
            return (!$status) ? __("Disabled", 'vqmod') : __("Enabled", 'vqmod');
        }

        /**
         * Get the status of the row. There are three status:
         *     - inactive
         *     - active
         */
        private function get_row_status_class($status)
        {
            return (!$status) ? 'status-inactive' : 'status-active';
        }

        public function row_class($status)
        {
            return $this->get_row_status_class($status);
        }   

	}