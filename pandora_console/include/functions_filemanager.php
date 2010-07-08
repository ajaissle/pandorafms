<?php

// Pandora FMS - http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2009 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list

// This program is free software; you can redistribute it and/or
// modify it under the terms of the  GNU Lesser General Public License
// as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

/**
 * @package Include
 * @subpackage Filemanager
 */

/**#@+
 * Constants
 */
define ('MIME_UNKNOWN', 0);
define ('MIME_DIR', 1);
define ('MIME_IMAGE', 2);
define ('MIME_ZIP', 3);
define ('MIME_TEXT', 4);
/**#@-*/

if (!function_exists ('mime_content_type')) {
	/**
	 * Gets the MIME type of a file.
	 *
	 * Help function in case mime_magic is not loaded on PHP.
	 *
	 * @param string Filename to get MIME type.
	 *
	 * @return The MIME type of the file.
	 */
	function mime_content_type ($filename) {
		$mime_types = array (
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',
			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',
			'gz' => 'application/x-gzip',
			'gz' => 'application/x-bzip2',
			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',
			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',
			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
		);

		$ext = strtolower (array_pop (explode ('.', $filename)));
		if (array_key_exists ($ext, $mime_types)) {
			return $mime_types[$ext];
		} elseif (function_exists ('finfo_open')) {
			$finfo = finfo_open (FILEINFO_MIME);
			$mimetype = finfo_file ($finfo, $filename);
			finfo_close ($finfo);
			return $mimetype;
		} else {
			return 'application/octet-stream';
		}
	}
}

$upload_file_or_zip = (bool) get_parameter('upload_file_or_zip');

if ($upload_file_or_zip) {
	$zip_or_file = get_parameter('zip_or_file');
	if ($zip_or_file == 'file') {
		$upload_file = true;
		$upload_zip = false;
	}
	else {
		$upload_file = false;
		$upload_zip = true;
	}
}
else {
	$upload_file = (bool) get_parameter ('upload_file');
	$upload_zip = (bool) get_parameter ('upload_zip');
}

// Upload file
if ($upload_file) {
	// Load global vars
	global $config;
	
	$config['filemanager'] = array();
	$config['filemanager']['correct_upload_file'] = 0;
	
	check_login ();
	
	if (! give_acl ($config['id_user'], 0, "PM")) {
		audit_db ($config['id_user'], $_SERVER['REMOTE_ADDR'], "ACL Violation", "Trying to access File manager");
		require ("general/noaccess.php");
		return;
	}
	
	if (isset ($_FILES['file']) && $_FILES['file']['name'] != "") {
		$filename = $_FILES['file']['name'];
		$filesize = $_FILES['file']['size'];
		$real_directory = (string) get_parameter('real_directory');
		$directory = (string) get_parameter ('directory');
		
		$hash = get_parameter('hash', '');
		$testHash = md5($real_directory . $directory . $config['dbpass']);
		
		if ($hash != $testHash) {
			echo "<h3 class=error>".__('Security error.')."</h3>";
		}
		else {
			// Copy file to directory and change name
			if ($directory == '') {
				$nombre_archivo = $real_directory .'/'. $filename;
			}
			else {
				$nombre_archivo = $config['homedir'].'/'.$directory.'/'.$filename;
			}
			if (! @copy ($_FILES['file']['tmp_name'], $nombre_archivo )) {
				echo "<h3 class=error>".__('attach_error')."</h3>";
			} else {
				$config['filemanager']['correct_upload_file'] = 1;
				
				// Delete temporal file
				unlink ($_FILES['file']['tmp_name']);
			}
		}		
	}
}

// Create text file 
$create_text_file = (bool) get_parameter ('create_text_file');
if ($create_text_file) {
	// Load global vars
	global $config;
	
	$config['filemanager'] = array();
	$config['filemanager']['correct_upload_file'] = 0;
	
	check_login ();
	
	if (! give_acl ($config['id_user'], 0, "PM")) {
		audit_db ($config['id_user'], $_SERVER['REMOTE_ADDR'], "ACL Violation", "Trying to access File manager");
		require ("general/noaccess.php");
		return;
	}
	
	$filename = get_parameter('name_file');
	
	if ($filename != "") {

		$real_directory = (string) get_parameter('real_directory');
		$directory = (string) get_parameter ('directory');
		
		$hash = get_parameter('hash', '');
		$testHash = md5($real_directory . $directory . $config['dbpass']);
		
		if ($hash != $testHash) {
			echo "<h3 class=error>".__('Security error.')."</h3>";
		}
		else {
			if ($directory == '') {
				$nombre_archivo = $real_directory .'/'. $filename;
			}
			else {
				$nombre_archivo = $config['homedir'].'/'.$directory.'/'.$filename;
			}
			if (! @touch($nombre_archivo)) {
				echo "<h3 class=error>".__('Error create file.')."</h3>";
			}
			else {
				$config['filemanager']['correct_upload_file'] = 1;
			}
		}
	}
	else {
		echo "<h3 class=error>".__('Error create file with empty name.')."</h3>";
	}
}

// Upload file
if ($upload_zip) {
	// Load global vars
	global $config;
	
	$config['filemanager'] = array();
	$config['filemanager']['correct_upload_file'] = 0;
	
	check_login ();
	
	if (! give_acl ($config['id_user'], 0, "PM")) {
		audit_db ($config['id_user'], $_SERVER['REMOTE_ADDR'], "ACL Violation", "Trying to access File manager");
		require ("general/noaccess.php");
		return;
	}
	
	if (isset ($_FILES['file']) && $_FILES['file']['name'] != "") {
		$filename = $_FILES['file']['name'];
		$filesize = $_FILES['file']['size'];
		$real_directory = (string) get_parameter('real_directory');
		$directory = (string) get_parameter ('directory');
		
		$hash = get_parameter('hash', '');
		$testHash = md5($real_directory . $directory . $config['dbpass']);
		
		if ($hash != $testHash) {
			echo "<h3 class=error>".__('Security error.')."</h3>";
		}
		else {
			// Copy file to directory and change name
			if ($directory == '') {
				$nombre_archivo = $real_directory .'/'. $filename;
			}
			else {
				$nombre_archivo = $config['homedir'].'/'.$directory.'/'.$filename;
			}
			if (! @copy ($_FILES['file']['tmp_name'], $nombre_archivo )) {
				echo "<h3 class=error>".__('attach_error')."</h3>";
			}
			else {
				// Delete temporal file
				unlink ($_FILES['file']['tmp_name']);
				
				//Extract the zip file
				$zip = new ZipArchive;
				$pathname = $config['homedir'].'/'.$directory.'/';
				
				if ($zip->open($nombre_archivo) === true) {
					$zip->extractTo($pathname);
					unlink($nombre_archivo);
				}
	
				$config['filemanager']['correct_upload_file'] = 1;
			}
		}
	}
}

// CREATE DIR
$create_dir = (bool) get_parameter ('create_dir');
if ($create_dir) {
	global $config;
	
	$config['filemanager'] = array();
	$config['filemanager']['correct_create_dir'] = 0;
	
	$directory = (string) get_parameter ('directory', "/");
	
	$hash = get_parameter('hash', '');
	$testHash = md5($directory . $config['dbpass']);
	
	if ($hash != $testHash) {
		 echo "<h3 class=error>".__('Security error.')."</h3>";
	}
	else {
		$dirname = (string) get_parameter ('dirname');
		if ($dirname != '') {
			@mkdir ($directory.'/'.$dirname);
			echo '<h3>'.__('Created directory %s', $dirname).'</h3>';
			
			$config['filemanager']['correct_create_dir'] = 1;
		}
		else {
			echo "<h3 class=error>".__('Error create file with empty name.')."</h3>";
		}
	}
}

//DELETE FILE OR DIR
$delete_file = (bool) get_parameter ('delete_file');
if ($delete_file) {
	global $config;
	
	$config['filemanager'] = array();
	$config['filemanager']['delete'] = 0;
	
	$filename = (string) get_parameter ('filename');
	
	$hash = get_parameter('hash', '');
	$testHash = md5($filename . $config['dbpass']);
	
	if ($hash != $testHash) {
		 echo "<h3 class=error>".__('Security error.')."</h3>";
	}
	else {
		echo "<h3>".__('Deleting')." ".$filename."</h3>";
		if (is_dir ($filename)) {		
			rmdir ($filename);
			$config['filemanager']['delete'] = 1;
		} else {
			unlink ($filename);
			$config['filemanager']['delete'] = 1;
		}
	}
}

/**
 * Recursive delete directory and empty or not directory.
 * 
 * @param string $dir The dir to deletete
 */
function delete_directory($dir)
{
	if ($handle = opendir($dir))
	{
	    while (false !== ($file = readdir($handle))) {
	        if (($file != ".") && ($file != "..")) {
	
				if (is_dir($dir . $file))
				{
					if (!rmdir($dir . $file))
					{
						delete_directory($dir . $file . '/');
					}
				}
				else
				{
					unlink($dir . $file);
				}
	        }
	    }
		closedir($handle);
		rmdir($dir);
	}
}

/**
 * The main function to show the directories and files.
 * 
 * @param string $real_directory The string of dir as realpath.
 * @param string $relative_directory The string of dir as relative path.
 * @param string $url The url to set in the forms and some links in the explorer.
 * @param string $father The directory father don't navigate bottom this.
 * @param boolean $editor The flag to set the edition of text files.
 */
function file_explorer($real_directory, $relative_directory, $url, $father = '', $editor = false) {
	global $config;
	
	?>
	<script type="text/javascript">
	function show_form_create_folder() {
		$("#main_buttons").css("display", "none");
		$("#create_folder").css("display", "");
	}

	function show_upload_file() {
		$("#main_buttons").css("display", "none");
		$("#upload_file").css("display", "");
	}

	function show_create_text_file() {
		$("#main_buttons").css("display", "none");
		$("#create_text_file").css("display", "");
	}

	function show_main_buttons_folder() {
		$("#main_buttons").css("display", "");
		$("#create_folder").css("display", "none");
		$("#upload_file").css("display", "none");
		$("#create_text_file").css("display", "none");
	}
	</script>
	<?php
	
	// List files
	if (! is_dir ($real_directory)) {
		echo __('Directory %s doesn\'t exist!', $relative_directory);
		return;
	}
	
	$files = list_file_manager_dir ($real_directory);
	
	$table->width = '90%';
	$table->class = 'listing';
	
	$table->colspan = array ();
	$table->data = array ();
	$table->head = array ();
	$table->size = array ();
	$table->align[4] = 'center';
	
	$table->size[0] = '24px';
	
	$table->head[0] = '';
	$table->head[1] = __('Name');
	$table->head[2] = __('Last modification');
	$table->head[3] = __('Size');
	$table->head[4] = __('Actions');
	
	$prev_dir = explode ("/", $relative_directory);
	$prev_dir_str = "";
	for ($i = 0; $i < (count ($prev_dir) - 1); $i++) {
		$prev_dir_str .= $prev_dir[$i];
		if ($i < (count ($prev_dir) - 2))
			$prev_dir_str .= "/";
	}
	
	if (($prev_dir_str != '') && ($father != $relative_directory)) {
		$table->data[0][0] = print_image ('images/go_previous.png', true);
		$table->data[0][1] = '<a href="' . $url . '&directory='.$prev_dir_str.'&hash=' . md5($prev_dir_str.$config['dbpass']) . '">';
		$table->data[0][1] .= __('Parent directory');
		$table->data[0][1] .='</a>';
		
		$table->colspan[0][1] = 5;
	}
	
	if (is_writable ($real_directory)) {
		$table->data[1][0] = '';
		$table->data[1][1] = '<div id="main_buttons">';
		$table->data[1][1] .= print_button(__('Create folder'), 'folder', false, 'show_form_create_folder();', "class='sub'", true);
		$table->data[1][1] .= print_button(__('Upload file/s'), 'up_files', false, 'show_upload_file();', "class='sub'", true);
		$table->data[1][1] .= print_button(__('Create text file'), 'create_file', false, 'show_create_text_file();', "class='sub'", true);
		$table->data[1][1] .= '</div>';
		
		$table->data[1][1] .= '<div id="create_folder" style="display: none;">';
		$table->data[1][1] .= print_button(__('Close'), 'close', false, 'show_main_buttons_folder();', "class='sub' style='float: left;'", true);
		$table->data[1][1] .= '<form method="post" action="' . $url . '">';
		$table->data[1][1] .= print_input_text ('dirname', '', '', 15, 255, true);
		$table->data[1][1] .= print_submit_button (__('Create'), 'crt', false, 'class="sub next"', true);
		$table->data[1][1] .= print_input_hidden ('directory', $relative_directory, true);
		$table->data[1][1] .= print_input_hidden ('create_dir', 1, true);
		$table->data[1][1] .= print_input_hidden('hash', md5($relative_directory . $config['dbpass']), true);
		$table->data[1][1] .= '</form>';
		$table->data[1][1] .= '</div>';
		
		$table->data[1][1] .= '<div id="upload_file" style="display: none;">';
		$table->data[1][1] .= print_button(__('Close'), 'close', false, 'show_main_buttons_folder();', "class='sub' style='float: left;'", true);
		$table->data[1][1] .= '<form method="post" action="' . $url . '" enctype="multipart/form-data">';
		$table->data[1][1] .= print_help_tip (__("The zip upload in this dir, easy to upload multiple files."), true);
		$table->data[1][1] .= print_input_file ('file', true, false);
		$table->data[1][1] .= print_radio_button('zip_or_file', 'zip', '', false, true) . __('Multiple files zipped');
		$table->data[1][1] .= print_radio_button('zip_or_file', 'file', '', true, true) .  __('One');
		$table->data[1][1] .= '&nbsp;&nbsp;&nbsp;';
		$table->data[1][1] .= print_submit_button (__('Go'), 'go', false, 'class="sub next"', true);
		$table->data[1][1] .= print_input_hidden ('real_directory', $real_directory, true);
		$table->data[1][1] .= print_input_hidden ('directory', $relative_directory, true);
		$table->data[1][1] .= print_input_hidden('hash', md5($real_directory . $relative_directory . $config['dbpass']), true);
		$table->data[1][1] .= print_input_hidden ('upload_file_or_zip', 1, true);
		$table->data[1][1] .= '</form>';	
		$table->data[1][1] .= '</div>';
		
		$table->data[1][1] .= '<div id="create_text_file" style="display: none;">';
		$table->data[1][1] .= print_button(__('Close'), 'close', false, 'show_main_buttons_folder();', "class='sub' style='float: left;'", true);
		$table->data[1][1] .= '<form method="post" action="' . $url . '">';
		$table->data[1][1] .= print_input_text('name_file', '', '', 30, 50, true);
		$table->data[1][1] .= print_submit_button (__('Create'), 'create', false, 'class="sub"', true);
		$table->data[1][1] .= print_input_hidden ('real_directory', $real_directory, true);
		$table->data[1][1] .= print_input_hidden ('directory', $relative_directory, true);
		$table->data[1][1] .= print_input_hidden('hash', md5($real_directory . $relative_directory . $config['dbpass']), true);
		$table->data[1][1] .= print_input_hidden ('create_text_file', 1, true);
		$table->data[1][1] .= '</form>';	
		$table->data[1][1] .= '</div>';
		
		$table->colspan[1][1] =5;
	}
	
	foreach ($files as $fileinfo) {
		$data = array ();
		
		switch ($fileinfo['mime']) {
			case MIME_DIR:
				$data[0] = print_image ('images/mimetypes/directory.png', true);
				break;
			case MIME_IMAGE:
				$data[0] = print_image ('images/mimetypes/image.png', true);
				break;
			case MIME_ZIP:
				$data[0] = print_image ('images/mimetypes/zip.png', true);
				break;
			case MIME_TEXT:
				$data[0] = print_image ('images/mimetypes/text.png', true);
				break;
			default:
				$data[0] = print_image ('images/mimetypes/unknown.png', true);
				break;
		}
		
		if ($fileinfo['is_dir']) {
			$data[1] = '<a href="' . $url . '&directory='.$relative_directory.'/'.$fileinfo['name'].'&hash=' . md5($relative_directory.'/'.$fileinfo['name'].$config['dbpass']) . '">'.$fileinfo['name'].'</a>';
		} else {
			$data[1] = '<a href="'.$fileinfo['url'].'">'.$fileinfo['name'].'</a>';
		}
		$data[2] = print_timestamp ($fileinfo['last_modified'], true,
			array ('prominent' => true));
		if ($fileinfo['is_dir']) {
			$data[3] = '';
		} else {
			$data[3] = format_filesize ($fileinfo['size']);
		}
		
		//Actions buttons
		//Delete button
		$data[4] = '';
		if (is_writable ($fileinfo['realpath'])  &&
			(! is_dir ($fileinfo['realpath']) || count (scandir ($fileinfo['realpath'])) < 3)) {
			$data[4] = '<form method="post" action="' . $url . '" style="float: left;">';
			$data[4] .= '<input type="image" src="images/cross.png" onClick="if (!confirm(\' '.__('Are you sure?').'\')) return false;">';
			$data[4] .= print_input_hidden ('filename', $fileinfo['realpath'], true);
			$data[4] .= print_input_hidden('hash', md5($fileinfo['realpath'] . $config['dbpass']), true);
			$data[4] .= print_input_hidden ('delete_file', 1, true);
			$data[4] .= '</form>';
			
			if ($editor) {
				if ($fileinfo['mime'] == MIME_TEXT) {
					$data[4] .= "<a href='$url&edit_file=1&location_file=" . $fileinfo['realpath'] . "&hash=" . md5($fileinfo['realpath'] . $config['dbpass']) . "' style='float: left;'><img src='images/edit.png' style='margin-top: 2px;' /></a>";
				}
			}
		}
	
		array_push ($table->data, $data);
	}
	
	print_table ($table);
}

/**
 * 
 * @param string $real_directory The string of dir as realpath.
 * @param string $relative_directory The string of dir as relative path.
 * @param string $url The url to set in the forms and some links in the explorer.
 */
function box_upload_file_complex($real_directory, $relative_directory, $url = '') {
	global $config;
	
	$table->width = '100%';
	
	$table->data = array ();
	
	if (! is_file_manager_writable_dir ($real_directory)) {
		echo "<h3 class='error'>".__('Current directory is not writable by HTTP Server')."</h3>";
		echo '<p>';
		echo __('Please check that current directory has write rights for HTTP server');
		echo '</p>';
	} else {
		$table->data[1][0] = __('Upload') . print_help_tip (__("The zip upload in this dir, easy to upload multiple files."), true);
		$table->data[1][1] = print_input_file ('file', true, false);
		$table->data[1][2] = print_radio_button('zip_or_file', 'zip', __('Multiple files zipped'), false, true);
		$table->data[1][3] = print_radio_button('zip_or_file', 'file', __('One'), true, true);
		$table->data[1][4] = print_submit_button (__('Go'), 'go', false,
			'class="sub next"', true);
		$table->data[1][4] .= print_input_hidden ('real_directory', $real_directory, true);
		$table->data[1][4] .= print_input_hidden ('directory', $relative_directory, true);
		$table->data[1][4] .= print_input_hidden('hash', md5($real_directory . $relative_directory . $config['dbpass']), true);
		$table->data[1][4] .= print_input_hidden ('upload_file_or_zip', 1, true);
	}
	
	echo '<form method="post" action="' . $url . '" enctype="multipart/form-data">';
	print_table ($table);
	echo '</form>';	
}

/**
 * Print the box of fields for upload file.
 * 
 * @param string $real_directory The string of dir as realpath.
 * @param string $relative_directory The string of dir as relative path.
 * @param string $url The url to set in the forms and some links in the explorer.
 */
function box_upload_file_explorer($real_directory, $relative_directory, $url = '') {
	global $config;
	
	$table->width = '50%';
	
	$table->data = array ();
	
	if (! is_file_manager_writable_dir ($real_directory)) {
		echo "<h3 class='error'>".__('Current directory is not writable by HTTP Server')."</h3>";
		echo '<p>';
		echo __('Please check that current directory has write rights for HTTP server');
		echo '</p>';
	} else {
		$table->data[1][0] = __('Upload file');
		$table->data[1][1] = print_input_file ('file', true, false);
		$table->data[1][2] = print_submit_button (__('Go'), 'go', false,
			'class="sub next"', true);
		$table->data[1][2] .= print_input_hidden ('real_directory', $real_directory, true);
		$table->data[1][2] .= print_input_hidden ('directory', $relative_directory, true);
		$table->data[1][2] .= print_input_hidden('hash', md5($real_directory . $relative_directory . $config['dbpass']), true);
		$table->data[1][2] .= print_input_hidden ('upload_file', 1, true);
	}
	
	echo '<form method="post" action="' . $url . '" enctype="multipart/form-data">';
	print_table ($table);
	echo '</form>';
}

/**
 * Print the box of fields for upload file zip.
 * 
 * @param unknown_type $real_directory
 * @param unknown_type $relative_directory
 * @param string $url The url to set in the forms and some links in the explorer.
 */
function box_upload_zip_explorer($real_directory, $relative_directory, $url = '') {
	global $config;
	
	$table->width = '60%';
	
	$table->data = array ();
	
	if (! is_file_manager_writable_dir ($real_directory)) {
		echo "<h3 class='error'>".__('Current directory is not writable by HTTP Server')."</h3>";
		echo '<p>';
		echo __('Please check that current directory has write rights for HTTP server');
		echo '</p>';
	} else {
		$table->data[1][0] = __('Upload zip file: ') . print_help_tip (__("The zip upload in this dir, easy to upload multiple files."), true);
		$table->data[1][1] = print_input_file ('file', true, false);
		$table->data[1][2] = print_submit_button (__('Go'), 'go', false,
			'class="sub next"', true);
		$table->data[1][2] .= print_input_hidden ('real_directory', $real_directory, true);
		$table->data[1][2] .= print_input_hidden ('directory', $relative_directory, true);
		$table->data[1][2] .= print_input_hidden('hash', md5($real_directory . $relative_directory . $config['dbpass']), true);
		$table->data[1][2] .= print_input_hidden ('upload_zip', 1, true);
	}
	
	echo '<form method="post" action="' . $url . '" enctype="multipart/form-data">';
	print_table ($table);
	echo '</form>';
}

/**
 * Print the box of fields for create the text file.
 * 
 * @param unknown_type $real_directory
 * @param unknown_type $relative_directory
 * @param string $url The url to set in the forms and some links in the explorer.
 */
function box_create_text_explorer($real_directory, $relative_directory, $url = '') {
	global $config;
	
	$table->width = '60%';
	
	$table->data = array ();
	
	if (! is_file_manager_writable_dir ($real_directory)) {
		echo "<h3 class='error'>".__('Current directory is not writable by HTTP Server')."</h3>";
		echo '<p>';
		echo __('Please check that current directory has write rights for HTTP server');
		echo '</p>';
	} else {
		$table->data[1][0] = __('Create text file: ');
		$table->data[1][1] = print_input_text('name_file', '', '', 30, 50, true);
		$table->data[1][2] = print_submit_button (__('Create'), 'create', false,
			'class="sub"', true);
		$table->data[1][2] .= print_input_hidden ('real_directory', $real_directory, true);
		$table->data[1][2] .= print_input_hidden ('directory', $relative_directory, true);
		$table->data[1][2] .= print_input_hidden('hash', md5($real_directory . $relative_directory . $config['dbpass']), true);
		$table->data[1][2] .= print_input_hidden ('create_text_file', 1, true);
	}
	
	echo '<form method="post" action="' . $url . '">';
	print_table ($table);
	echo '</form>';
}

/**
 * Get the available directories of the file manager.
 *
 * @return array An array with all the directories where the file manager can
 * operate.
 */
function get_file_manager_available_directories () {
	global $config;
	
	$dirs = array ();
	$dirs['images'] = "images";
	$dirs['attachment'] = "attachment";
	$dirs['languages'] = "include/languages";
	
	foreach ($dirs as $dirname) {
		$dirpath = realpath ($config['homedir'].'/'.$dirname);
		$dir = opendir ($dirpath);
		while ($file = @readdir ($dir)) {
			/* Ignore hidden files */
			if ($file[0] == '.')
				continue;
			$filepath = $dirpath.'/'.$file;
			if (is_dir ($filepath)) {
				$dirs[$dirname.'/'.$file] = $dirname.'/'.$file;
			}
		}
	}
	
	return $dirs;
}

/**
 * Check if a dirname is available for the file manager.
 *
 * @param string Dirname to check.
 * 
 * @return array An array with all the directories where the file manager can
 * operate.
 */
function is_file_manager_available_directory ($dirname) {
	$dirs = get_file_manager_available_directories ();
	
	return isset ($dirs[$dirname]);
}

/**
 * Check if a directory is writable.
 *
 * @param string Directory path to check.
 * @param bool If set, it will try to make the directory writeable if it's not.
 *
 * @param bool Wheter the directory is writeable or not.
 */
function is_file_manager_writable_dir ($dirpath, $force = false) {
	if (is_file_manager_available_directory (basename ($dirpath)))
		return is_writable ($dirpath);
	if (is_file_manager_writable_dir (realpath ($dirpath.'/..')))
		return true;
	else if (! $force)
			return is_writable ($dirpath);
	
	return (is_writable ($dirpath) || @chmod ($dirpath, 0755));
}

/**
 * Check if a directory is writable.
 *
 * @param string Directory path to check.
 * @param bool If set, it will try to make the directory writeable if it's not.
 *
 * @param bool Wheter the directory is writeable or not.
 */
function get_file_manager_file_info ($filepath) {
	global $config;
	
	$realpath = realpath ($filepath);
	
	$info = array ('mime' => MIME_UNKNOWN,
		'mime_extend' => mime_content_type ($filepath),
		'link' => 0,
		'is_dir' => false,
		'name' => basename ($realpath),
		'url' => str_replace('//', '/', $config['homeurl'].str_ireplace ($config['homedir'], '', $realpath)),
		'realpath' => $realpath,
		'size' => filesize ($realpath),
		'last_modified' => filemtime ($realpath)
	);
	
	$zip_mimes = array ('application/zip',
		'application/x-rar-compressed',
		'application/x-gzip',
		'application/x-bzip2');
	if (is_dir ($filepath)) {
		$info['mime'] = MIME_DIR;
		$info['is_dir'] = true;
		$info['size'] = 0;
	} else if (strpos ($info['mime_extend'], 'image') === 0) {
		$info['mime'] = MIME_IMAGE;
	} else if (in_array ($info['mime_extend'], $zip_mimes)) {
		$info['mime'] = MIME_ZIP;
	} else if (strpos ($info['mime_extend'], 'text') === 0) {
		$info['mime'] = MIME_TEXT;
	}
	
	return $info;
}

/**
 * Check if a directory is writable.
 *
 * @param string Directory path to check.
 * @param bool If set, it will try to make the directory writeable if it's not.
 *
 * @param bool Wheter the directory is writeable or not.
 */
function list_file_manager_dir ($dirpath) {
	$files = array ();
	$dirs = array ();
	$dir = opendir ($dirpath);
	while ($file = @readdir ($dir)) {
		/* Ignore hidden files */
		if ($file[0] == '.')
			continue;
		$info = get_file_manager_file_info ($dirpath.'/'.$file);
		if ($info['is_dir']) {
			$dirs[$file] = $info;
		} else {
			$files[$file] = $info;
		}
	}
	ksort ($files);
	ksort ($dirs);
	closedir ($dir);
	
	return array_merge ($dirs, $files);
}
?>
