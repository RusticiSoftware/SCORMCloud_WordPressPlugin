<?php
global $wpdb;

if (defined('ABSPATH')) {
    require_once ABSPATH . 'wp-load.php';
} else {
    require_once '../../../wp-load.php';
}
require_once ABSPATH . 'wp-admin/includes/admin.php';

require_once SCORMCLOUD_BASE . 'scormcloudplugin.php';
$scorm_service = ScormCloudPlugin::get_cloud_service();

$id = $_GET['id'];
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'new';

$course_service = $scorm_service->getCourseService();

/*** check for https ***/
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
/*** return the full address ***/
$basepath = $protocol . '://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'scormcloud')) . 'scormcloud/';
$import_callback = $basepath . '/importcallback.php';

?>

<script>
/* Script written by Adam Khoury @ DevelopPHP.com */
/* Video Tutorial: http://www.youtube.com/watch?v=EraNFJiY0Eg */
function _(el){
	return document.getElementById(el);
}
function uploadFile(){
	var file = _("file1").files[0];
	// alert(file.name+" | "+file.size+" | "+file.type);
	var formdata = new FormData();
	formdata.append("file1", file);
	formdata.append("courseid", "<?=$id?>");
	var ajax = new XMLHttpRequest();
	ajax.upload.addEventListener("progress", progressHandler, false);
	ajax.addEventListener("load", completeHandler, false);
	ajax.addEventListener("error", errorHandler, false);
	ajax.addEventListener("abort", abortHandler, false);
	ajax.open("POST", "file_upload_parser.php");
	ajax.send(formdata);
}
function progressHandler(event){
	_("loaded_n_total").innerHTML = "Uploaded "+event.loaded+" bytes of "+event.total;
	var percent = (event.loaded / event.total) * 100;
	_("status").innerHTML = Math.round(percent)+"% uploaded... Importing...";
}
function completeHandler(event){
	_("status").innerHTML = event.target.responseText;
}
function errorHandler(event){
	_("status").innerHTML = "Upload Failed";
}
function abortHandler(event){
	_("status").innerHTML = "Upload Aborted";
}
</script>

<form id="upload_form" enctype="multipart/form-data" method="post">
  <input type="file" name="file1" id="file1"><br>
  <input type="button" value="Upload File" onclick="uploadFile()">
  <input type="hidden" value="<?=$id?>" name="courseId" id="courseId">
  <span class="importMessage" style="display:none;"><?=__("Importing Package......", "scormcloud")?></span>
  <h3 id="status"></h3>
  <p id="loaded_n_total"></p>
</form>
