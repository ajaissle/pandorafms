<?php

// Pandora FMS - http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2011 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.


global $config;

include_once("include/functions_ui.php");
include_once("include/functions_db.php");
include_once("include/functions_netflow.php");

check_login ();

if (! check_acl ($config["id_user"], 0, "AW")) {
	db_pandora_audit("ACL Violation",
		"Trying to access event viewer");
	require ("general/noaccess.php");
	return;
}

$buttons['add'] = '<a href="index.php?sec=netf&sec2=godmode/netflow/nf_edit_form">'
		. html_print_image ("images/add.png", true, array ("title" => __('Add filter')))
		. '</a>';
		
//Header
ui_print_page_header (__('Manage Netflow Filter'), "images/networkmap/so_cisco_new.png", false, "", true, $buttons);

$delete = (bool) get_parameter ('delete');
$multiple_delete = (bool)get_parameter('multiple_delete', 0);
$id = (int) get_parameter ('id');
$name = (string) get_parameter ('name');

if ($delete) {
	$id_filter = db_get_value('id_name', 'tnetflow_filter', 'id_sg', $id);
	$result = db_process_sql_delete ('tnetflow_filter',
		array ('id_sg' => $id));
		
	$result2 = db_process_sql_delete ('tnetflow_report_content',
		array ('id_filter' => $id_filter));

	if ($result !== false) {
		$result = true;
	} else {
		$result = false;
	}
		
	ui_print_result_message ($result,
		__('Successfully deleted'),
		__('Not deleted. Error deleting data'));
}

if ($multiple_delete) {
	$ids = (array)get_parameter('delete_multiple', array());
	
	db_process_sql_begin();
	
	foreach ($ids as $id) {
		$id_filter = db_get_value('id_name', 'tnetflow_filter', 'id_sg', $id);	
		$result = db_process_sql_delete ('tnetflow_filter',
			array ('id_sg' => $id));
		
		$result2 = db_process_sql_delete ('tnetflow_report_content',
			array ('id_filter' => $id_filter));
	
		if ($result === false) {
			db_process_sql_rollback();
			break;
		}
	}
	
	if ($result !== false) {
		db_process_sql_commit();
	}
	
	if ($result !== false) $result = true;
	else $result = false;
		
	ui_print_result_message ($result,
		__('Successfully deleted'),
		__('Not deleted. Error deleting data'));
}

$own_info = get_user_info ($config['id_user']);
// Get group list that user has access
$groups_user = users_get_groups ($config['id_user'], "AW", $own_info['is_admin'], true);

$groups_id = array();
foreach($groups_user as $key => $groups){
	$groups_id[] = $groups['id_grupo'];
}

$sql = "SELECT * FROM tnetflow_filter WHERE id_group IN (".implode(',',$groups_id).")";
$filters = db_get_all_rows_sql($sql);
if ($filters === false)
	$filters = array ();

$table->width = '70%';
$table->head = array ();
$table->head[0] = __('Name');
$table->head[1] = __('Action') .
		html_print_checkbox('all_delete', 0, false, true, false, 'check_all_checkboxes();');
$table->style = array ();
$table->style[0] = 'font-weight: bold';
$table->align = array ();
$table->align[2] = 'center';
$table->size = array ();
$table->size[0] = '90%';
$table->size[1] = '80px';
$table->data = array ();

$total_filters = db_get_all_rows_filter ('tnetflow_filter', false, 'COUNT(*) AS total');
$total_filters = $total_filters[0]['total'];

//ui_pagination ($total_filters, $url);

foreach ($filters as $filter) {
	$data = array ();
	
	$data[0] = '<a href="index.php?sec=netf&sec2=godmode/netflow/nf_edit_form&id='.$filter['id_sg'].'">'.$filter['id_name'].'</a>';

	$data[1] = "<a onclick='if(confirm(\"" . __('Are you sure?') . "\")) return true; else return false;' 
		href='index.php?sec=netf&sec2=godmode/netflow/nf_edit&delete=1&id=".$filter['id_sg']."&offset=0'>" . 
		html_print_image('images/cross.png', true, array('title' => __('Delete'))) . "</a>" .
		html_print_checkbox_extended ('delete_multiple[]', $filter['id_sg'], false, false, '', 'class="check_delete"', true);
	
	array_push ($table->data, $data);
}

if(isset($data)) {
	echo "<form method='post' action='index.php?sec=netf&sec2=godmode/netflow/nf_edit'>";
	html_print_input_hidden('multiple_delete', 1);
	html_print_table ($table);
	echo "<div style='padding-bottom: 20px; text-align: right; width:" . $table->width . "'>";
	html_print_submit_button(__('Delete'), 'delete_btn', false, 'class="sub delete"');
	echo "</div>";
	echo "</form>";
}
else {
	echo "<div class='nf'>".__('There are no defined filters')."</div>";
}
	
?>

<script type="text/javascript">
function check_all_checkboxes() {
	if ($("input[name=all_delete]").attr('checked')) {
		$(".check_delete").attr('checked', true);
	}
	else {
		$(".check_delete").attr('checked', false);
	}
}
</script>
