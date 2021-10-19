<?php
global $wpdb;

if (defined('ABSPATH')) {
    require_once ABSPATH . 'wp-load.php';
} else {
    require_once '../../../../wp-load.php';
}
require_once ABSPATH . 'wp-admin/includes/admin.php';

// define( 'SCORMCLOUD_BASE', '../' );

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

$_SESSION['token'] = bin2hex(random_bytes(35));

?>
<link rel="stylesheet" href="../css/scormcloud.admin.css" />
<script>
/* Script written by Adam Khoury @ DevelopPHP.com */
function _(el){
	return document.getElementById(el);
}
function uploadFile(){
	var file = _("file1").files[0];
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
	parent.location = parent.location.href;
}
function errorHandler(event){
	_("status").innerHTML = "Upload Failed";
}
function abortHandler(event){
	_("status").innerHTML = "Upload Aborted";
}
</script>
<div class="scormcloud-admin-page courses">
<table>
	<tr>
		<td>
			<form id="upload_form" enctype="multipart/form-data" method="post">
				<h5>Choose ZIP/MP3/MP4/PDF File</h5>
				<input type="file" name="file1" id="file1" accept="application/zip, video/mp4, audio/mp3, application/pdf"><br>
				<input type="button" id="submit" value="Upload File" onclick="uploadFile()">
				<input type="hidden" value="<?=$id?>" name="courseId" id="courseId">
				<span class="importMessage" style="display:none;"><?=__("Importing Package......", "scormcloud")?></span>
				<input type="hidden" name="token" value="<?= $_SESSION['token'] ?? '' ?>">
				<h3 id="status"></h3>
				<p id="loaded_n_total"></p>
			</form>
		</td>
		<td>
			<div class="importdetail">
				To import a course package into SCORM Cloud, it needs to be zipped up (.zip) and needs to be either a SCORM, AICC or xAPI course package.
				<br/>
				A SCORM package needs to have a imsmanifest.xml file which describes the course and its content.
				<br/>
				A xAPI package needs to have a tincan.xml file and needs to send xAPI statements to the SCORM Cloud Learning Record Store. Click here to learn more about xAPI.
				<br/>
				An AICC package needs to be zipped up with the 4 AICC descriptor files (AU, CRS, CST, & DES).
				<br/>
				A cmi5 package must include a 'cmi5.xml' file which includes a list of assignable units (AU) which when launched will communicate with the SCORM Cloud Learning Record Store via xAPI.
				<br/>
				To import a PDF, MP4, or MP3 into SCORM Cloud, simply upload the file itself. The file should not be inside of a .zip file. Instead, upload just the .pdf, .mp4, or .mp3 file on its own.
				<br/>
				SCORM Cloud will wrap this file in a cmi5 package to play it. Progress is determined by the percentage of the content viewed (e.g. the number of pages viewed for a PDF).
			</div>
		</td>
	</tr>
</table>
</div>



