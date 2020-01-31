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
 
$status     = __get('status');
$numLogs    = __get('numLogs');

$aData 	    = __get('aData');
$columns    = $aData['aColumns'];
$rows 		= $aData['aRows'];
?>
<h2 class="render-title">
	<?php if ($status) : ?>
		<a href="javascript:vqmod_dialog()" class="btn btn-mini"><?php _e("Disable", 'vqmod'); ?></a> status: on
	<?php else : ?>
		<a href="javascript:vqmod_dialog()" class="btn btn-mini"><?php _e("Enable", 'vqmod'); ?></a> status: off
	<?php endif; ?>| 
	<a href="javascript:vqmod_cache_dialog()" class="btn btn-mini">Purge cache</a>
	<a href="<?php echo osc_route_admin_url('vqmod-admin-logs'); ?>" class="btn btn-mini">Logs <?php if ($numLogs > 0) echo "($numLogs)"; ?></a>
</h2>

<!-- DataTable -->
<div class="relative">
    <div id="users-toolbar" class="table-toolbar"></div>

    <form id="datatablesForm" method="post" action="<?php echo osc_route_admin_url('vqmod-admin-mods'); ?>">
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="route" value="vqmod-admin-mods" />

        <!-- Bulk actions -->
        <div id="bulk-actions">
            <label>
                <?php osc_print_bulk_actions('bulk_actions', 'plugin_action', __get('bulk_options'), 'select-box-extra'); ?>
                <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html( __('Apply') ); ?>" />
            </label>
        </div>

        <div class="table-contains-actions">
            <table class="table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <?php foreach($columns as $k => $v) {
                            echo '<th class="col-'.$k.' '.($sort==$k?($direction=='desc'?'sorting_desc':'sorting_asc'):'').'">'.$v.'</th>';
                        }; ?>
                    </tr>
                </thead>
                <tbody>
                <?php if( count($rows) > 0 ) { ?>
                    <?php foreach($rows as $key => $row) {
                        $status = $row['status'];
                        $row['status'] = osc_apply_filter('datatable_mods_status_text', $row['status']);
                         ?>
                        <tr class="<?php echo osc_apply_filter('datatable_mods_status_class',  $status); ?>">
                            <?php foreach($row as $k => $v) { ?>
                                <td class="col-<?php echo $k; ?>"><?php echo $v; ?></td>
                            <?php }; ?>
                        </tr>
                    <?php }; ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="<?php echo count($columns)+1; ?>" class="text-center">
                            <p><?php _e("No data available in table", 'vqmod'); ?></p>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <div id="table-row-actions"></div> <!-- used for table actions -->
        </form>
    </div>
</div>
<br><?php $status 	= __get('status'); ?>
<div class="float-right">
	<a href="javascript:submit_mod_dialog();"><?php _e("Add mod file", 'vqmod'); ?></a>
</div>

<!-- Dialog when it want delete a vqmod file -->
<form id="dialog-vqmod" method="get" action="<?php echo osc_route_admin_url(true); ?>" class="has-form-actions hide" title="<?php echo osc_esc_html(__('vQmod 2.6.4')); ?>">
    <input type="hidden" name="page" value="plugins" />
    <input type="hidden" name="action" value="renderplugin" />
    <input type="hidden" name="route" value="vqmod-admin-mods" />
    <input type="hidden" name="plugin_action" value="<?php echo ($status) ? 'uninstall_vqmod' : 'install_vqmod'; ?>" />

    <div class="form-horizontal">
        <div class="form-row">
        	<?php if (!$status) : ?>
                <h2>Installation</h2>
                The following files will be modified:<br>
                <pre><?php echo realpath(dirname(__FILE__) . '/../../../../../') . '/index.php'; ?></pre>
                <pre><?php echo realpath(dirname(__FILE__) . '/../../../../../') . '/[admin_folder]/index.php'; ?></pre>
                To enable this module, it is necessary to install and initialize the vQmod library in your Osclass installation.
                <br><br>
                vQmod is a library that allows alterations or virtual modifications to the rest of your files without considerably modifying the originals.
                <br><br>
                You can remove the installation and revert the changes automatically when you disable this module.
                <br><br>
                For more information visit the vQmod™ project <a target="_blank" href="https://github.com/vqmod/vqmod/wiki">https://github.com/vqmod/vqmod/wiki</a>.
			<?php else : ?>
                <h2>Uninstallation</h2>
                The following files will be modified for its respective restoration: <br>
                <pre><?php echo realpath(dirname(__FILE__) . '/../../../../../') . '/index.php'; ?></pre>
                <pre><?php echo realpath(dirname(__FILE__) . '/../../../../../') . '/[admin_folder]/index.php'; ?></pre>
				vQmod will be uninstalled from the plugins system of this Osclass installation.
                <br><br>
                For more information visit the vQmod™ project <a target="_blank" href="https://github.com/vqmod/vqmod/wiki">https://github.com/vqmod/vqmod/wiki</a>.
			<?php endif; ?>
        </div>
        <div class="form-actions">
            <div class="wrapper">
                <a class="btn" href="javascript:void(0);" onclick="$('#dialog-vqmod').dialog('close');"><?php _e("Cancel", 'vqmod'); ?></a>
                <input id="vqmod-bootup-submit" type="submit" value="<?php echo osc_esc_html( __("Apply", 'vqmod') ); ?>" class="btn btn-submit" />
            </div>
        </div>
    </div>
</form>

<!-- Dialog vQmod pruge cache -->
<form id="dialog-vqmod-cache" method="get" action="<?php echo osc_route_admin_url(true); ?>" class="has-form-actions hide" title="vQmod purge cache">
    <input type="hidden" name="page" value="plugins" />
    <input type="hidden" name="action" value="renderplugin" />
    <input type="hidden" name="route" value="vqmod-admin-mods" />
    <input type="hidden" name="plugin_action" value="purge_cache" />

    <div class="form-horizontal">
        <div class="form-row">
        	<div class="form-label-checkbox">
            	<label><input type="checkbox" name="purge_vqmod_cache" value="1">vqmod/vqcache/*</label><br>
            	<label><input type="checkbox" name="purge_checked_cache" value="1">checked.cache</label><br>
            	<label><input type="checkbox" name="purge_mods_cache" value="1">mods.cache</label>
        	</div>
        </div>
        <div class="form-actions">
            <div class="wrapper">
            <a class="btn" href="javascript:void(0);" onclick="$('#dialog-vqmod-cache').dialog('close');"><?php _e("Cancel", 'vqmod'); ?></a>
            <input type="submit" value="Purge cache" class="btn btn-red" />
            </div>
        </div>
    </div>
</form>

<!-- Dialog for bulk actions of toolbar -->
<div id="dialog-bulk-actions" title="<?php echo __('Bulk actions'); ?>" class="has-form-actions hide">
    <div class="form-horizontal">
        <div class="form-row"></div>
        <div class="form-actions">
            <div class="wrapper">
                <a id="bulk-actions-cancel" class="btn" href="javascript:void(0);"><?php _e("Cancel", 'vqmod'); ?></a>
                <a id="bulk-actions-submit" href="javascript:void(0);" class="btn btn-red" ><?php echo osc_esc_html( __("Delete", 'vqmod') ); ?></a>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>

<form id="dialog-submit-mod" class="has-form-actions hide" title="<?php echo osc_esc_html(__("Add mod file", 'vqmod')); ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="page" value="plugins" />
    <input type="hidden" name="action" value="renderplugin" />
    <input type="hidden" name="route" value="vqmod-admin-mods" />
    <input type="hidden" name="plugin_action" value="add_mod" />

    <div class="form-horizontal">
        <div class="form-row">
            <div class="form-label"><?php _e("Mod file (.zip)", 'vqmod'); ?></div>
            <div class="form-controls">
                <div class="form-label-checkbox"><input type="file" name="mod" /></div>
            </div>
        </div>
        <div class="form-actions">
            <div class="wrapper">
            <a class="btn" href="javascript:void(0);" onclick="$('#dialog-submit-mod').dialog('close');"><?php _e("Cancel", 'vqmod'); ?></a>
            <input type="submit" value="<?php echo osc_esc_html( __("Upload", 'vqmod') ); ?>" class="btn btn-submit" />
            </div>
        </div>
    </div>
</form>

<!-- Dialog source file -->
<form id="dialog-source-file" method="get" action="<?php echo osc_route_admin_url(true); ?>" class="has-form-actions hide" title="<?php echo osc_esc_html(__("Source file", 'vqmod')); ?>">
    <input type="hidden" name="page" value="users" />
    <input type="hidden" name="action" value="save_source" />
    <input type="hidden" name="id[]" value="" />

    <div class="form-horizontal">
        <div class="form-row" id="show-source-file">
            Loading...
        </div>
        <div class="form-actions">
            <div class="wrapper">
            <a class="btn" href="javascript:void(0);" onclick="$('#dialog-source-file').dialog('close'); $('#show-source-file').html('Loading...');"><?php _e("Close", 'vqmod'); ?></a>
            <!--<input id="save-source-submit" type="submit" value="<?php echo osc_esc_html( __("Save changes", 'vqmod') ); ?>" class="btn btn-red" />-->
            <br><br>
            </div>
        </div>
    </div>
</form>

<!-- Dialog when it want delete a vqmod file -->
<form id="dialog-file-delete" method="get" action="<?php echo osc_route_admin_url(true); ?>" class="has-form-actions hide" title="<?php echo osc_esc_html(__("Delete mod", 'vqmod')); ?>">
    <input type="hidden" name="page" value="plugins" />
    <input type="hidden" name="action" value="renderplugin" />
    <input type="hidden" name="route" value="vqmod-admin-mods" />
    <input type="hidden" name="plugin_action" value="delete" />
    <input type="hidden" name="id[]" value="" />

    <div class="form-horizontal">
        <div class="form-row" id="delete-file-info"></div>
        <div class="form-actions">
            <div class="wrapper">
                <a class="btn" href="javascript:void(0);" onclick="$('#dialog-file-delete').dialog('close');"><?php _e("Cancel", 'vqmod'); ?></a>
                <input id="button-file-delete" type="submit" value="<?php echo osc_esc_html( __("Delete", 'vqmod') ); ?>" class="btn btn-red" />
            </div>
        </div>
    </div>
</form>

<!-- Dialog when it want activate a vqmod file -->
<form id="dialog-mod-enable" method="get" action="<?php echo osc_route_admin_url(true); ?>" class="has-form-actions hide" title="<?php echo osc_esc_html(__("Enable mod", 'vqmod')); ?>">
    <input type="hidden" name="page" value="plugins" />
    <input type="hidden" name="action" value="renderplugin" />
    <input type="hidden" name="route" value="vqmod-admin-mods" />
    <input type="hidden" name="plugin_action" value="enable" />
    <input type="hidden" name="id[]" value="" />

    <div class="form-horizontal">
        <div class="form-row">
            <?php _e("Are you sure you want to enable this mod?", 'vqmod'); ?>
        </div>
        <div class="form-actions">
            <div class="wrapper">
                <a class="btn" href="javascript:void(0);" onclick="$('#dialog-mod-enable').dialog('close');"><?php _e("Cancel", 'vqmod'); ?></a>
                <input type="submit" value="<?php echo osc_esc_html( __("Enable", 'vqmod') ); ?>" class="btn btn-red" />
            </div>
        </div>
    </div>
</form>

<!-- Dialog when it want deactivate a vqmod file -->
<form id="dialog-mod-disable" method="get" action="<?php echo osc_route_admin_url(true); ?>" class="has-form-actions hide" title="<?php echo osc_esc_html(__("Disable mod", 'vqmod')); ?>">
    <input type="hidden" name="page" value="plugins" />
    <input type="hidden" name="action" value="renderplugin" />
    <input type="hidden" name="route" value="vqmod-admin-mods" />
    <input type="hidden" name="plugin_action" value="disable" />
    <input type="hidden" name="id[]" value="" />

    <div class="form-horizontal">
        <div class="form-row">
            <?php _e("Are you sure you want to disable this mod?", 'vqmod'); ?>
        </div>
        <div class="form-actions">
            <div class="wrapper">
                <a class="btn" href="javascript:void(0);" onclick="$('#dialog-mod-disable').dialog('close');"><?php _e("Cancel", 'vqmod'); ?></a>
                <input type="submit" value="<?php echo osc_esc_html( __("Disable", 'vqmod') ); ?>" class="btn btn-red" />
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function () {
	// Dialog source file
    $("#dialog-vqmod").dialog({
        autoOpen: false,
        modal: true,
        width: "550px"
    });

    // Dialog source file
    $("#dialog-vqmod-cache").dialog({
        autoOpen: false,
        modal: true,
        width: "380px"
    });

	// Dialog source file
    $("#dialog-source-file").dialog({
        autoOpen: false,
        modal: true,
        width: "1000px",
        position: "top"
    });

	// Dialog delete
    $("#dialog-file-delete").dialog({
        autoOpen: false,
        modal: true
    });

    // Dialog enable
    $("#dialog-mod-enable").dialog({
        autoOpen: false,
        modal: true
    });

    // Dialog disable
    $("#dialog-mod-disable").dialog({
        autoOpen: false,
        modal: true
    });

	// Check_all Bulk actions
    $("#check_all").change(function() {
        var isChecked = $(this).prop("checked");
        $('.col-bulkactions input').each( function() {
            if(isChecked == 1) {
                this.checked = true;
            } else {
                this.checked = false;
            }
        });
    });

    // Dialog Bulk actions
    $("#dialog-bulk-actions").dialog({
        autoOpen: false,
        modal: true
    });
    $("#bulk-actions-submit").click(function() {
        $("#datatablesForm").submit();
    });
    $("#bulk-actions-cancel").click(function() {
        $("#datatablesForm").attr('data-dialog-open', 'false');
        $('#dialog-bulk-actions').dialog('close');
    });

    // Dialog bulk actions function
    $("#datatablesForm").submit(function() {
        if( $("#bulk_actions option:selected").val() == "" ) {
            return false;
        }

        if( $("#datatablesForm").attr('data-dialog-open') == "true" ) {
            return true;
        }

        $("#dialog-bulk-actions .form-row").html($("#bulk_actions option:selected").attr('data-dialog-content'));
        $("#bulk-actions-submit").html($("#bulk_actions option:selected").text());
        $("#datatablesForm").attr('data-dialog-open', 'true');
        $("#dialog-bulk-actions").dialog('open');
        return false;
    });

    // Dialog submit mod
    $("#dialog-submit-mod").dialog({
        autoOpen: false,
        modal: true,
        width: "550px"
    });

});

// VQMod Dialog  for enable or disable
function vqmod_dialog() {
    $("#dialog-vqmod").dialog('open');
    return false;
}

function vqmod_cache_dialog() {
    $("#dialog-vqmod-cache").dialog('open');
    return false;
}

function copy(element) {
    var el = document.getElementById(element);
    var range = document.createRange();
    range.selectNodeContents(el);
    var sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
    document.execCommand('copy');
    $("#copied").show().delay(1500).fadeOut();
    return false;
}

// Dialog assign function
function opensource(grid, file) {
    // Loading
    $('#show-source-file').html('<center>Loading...</center>');
    $("#dialog-source-file input[name='id[]']").attr('value', file);

    var fileName = file.substr(0, file.indexOf('.'));
    var fileFormat = file.substr(file.indexOf('.'));
    var status = (fileFormat == ".xml") ? "<?php echo __("Enabled", 'vqmod'); ?>" : "<?php echo __("Disabled", 'vqmod'); ?>";
    
    // Menu buttons
    var del         = '<a href="javascript:delete_file(\''+fileName+'\')"><?php echo __("Delete", 'vqmod'); ?></a>';
    var copy        = '<a href="javascript:void(0);" onclick="copy(\''+fileName+'\')"><?php echo __("Copy content", 'vqmod'); ?></a>';
    var enable      = '<a href="javascript:enable_mod_dialog(\''+fileName+'\')"><?php echo __("Enable", 'vqmod'); ?></a>';
    var disable     = '<a href="javascript:disable_mod_dialog(\''+fileName+'\')"><?php echo __("Disable", 'vqmod'); ?></a>';
    if (fileFormat == ".xml") {
        var control = disable;
    } else {
        var control = enable;
    }
    var close       = '<a href="javascript:void(0);" onclick="$(\'#dialog-source-file\').dialog(\'close\'); $(\'#show-source-file\').html(\'<center>Loading...</center>\');"><?php echo __("Close", 'vqmod'); ?></a>';

    $("#dialog-source-file").dialog('open');
    var url = '<?php echo osc_base_url(); ?>index.php?page=ajax&action=runhook&hook=vqmod_admin_ajax&route=file_source_iframe&file='+file;
    $.ajax({
        method: "GET",
        url: url,
        dataType: "html"
    }).done(function(data) {
        $("#show-source-file").html(fileName+" ("+status+") | "+del+" "+copy+" "+control+" "+close+"<span id=\"copied\">· <?php echo __("¡Copied!", 'vqmod'); ?></span> <textarea id=\""+fileName+"\" readonly>"+data+"</textarea>");
    });
}

// Dialog delete function
function delete_file(item_id) {
    $("#dialog-file-delete input[name='id[]']").attr('value', item_id);
    $("#dialog-file-delete").dialog('open');
    $("#delete-file-info").html("<center><?php echo __("Are you sure you want to delete this mod?", 'vqmod'); ?></center>");
    return false;
}

// Dialog activate function
function enable_mod_dialog(item_id) {
    $("#dialog-mod-enable input[name='id[]']").attr('value', item_id);
    $("#dialog-mod-enable").dialog('open');
    return false;
}

// Dialog deactivate function
function disable_mod_dialog(item_id) {
    $("#dialog-mod-disable input[name='id[]']").attr('value', item_id);
    $("#dialog-mod-disable").dialog('open');
    return false;
}

// Dialog for upload integration mod files
function submit_mod_dialog() {
    $("#dialog-submit-mod").dialog('open');
    return false;
}
</script>