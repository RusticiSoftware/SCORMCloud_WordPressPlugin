<?php
global $wpdb;
if ( defined('ABSPATH') )
require_once(ABSPATH . 'wp-load.php');
else
require_once('../../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/admin.php');
require_once(SCORMCLOUD_BASE.'scormcloudplugin.php');
$ScormService = ScormCloudPlugin::get_cloud_service();
try {
    $isValidAccount = $ScormService->isValidAccount();
} catch (Exception $e) {
    $isValidAccount = false;
}

$uploadDirectoryName = SCORMCLOUD_BASE."/uploads/";

//Check if the directory already exists.
if(!is_dir($uploadDirectoryName)){
    //Directory does not exist, so lets create it.
    mkdir($uploadDirectoryName, 0755);
}

$token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);

if (!$token || $token !== $_SESSION['token']) {
    // show an error message
    echo '<p class="error">Error: invalid form submission</p>';
    // return 405 http status code
    header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
    exit;
}

$fileName = $_FILES["file1"]["name"]; // The file name
$fileTmpLoc = $_FILES["file1"]["tmp_name"]; // File in the PHP tmp folder
$fileType = $_FILES["file1"]["type"]; // The type of file it is
$fileSize = $_FILES["file1"]["size"]; // File size in bytes
$fileErrorMsg = $_FILES["file1"]["error"]; // 0 for false... and 1 for true
$courseId = $_POST["courseid"];
$mode     = $_POST[ 'mode' ];
if ( $mode == null ) {
	$mode = 'new';
}

if (!$fileTmpLoc) { // if file not chosen
    echo "ERROR: Please browse for a file before clicking the upload button.";
    exit();
}

if(move_uploaded_file($fileTmpLoc, $uploadDirectoryName.$fileName)){
    if ($isValidAccount){
        $courseService = $ScormService->getCourseService();
        $course_path = $uploadDirectoryName.$fileName;
        $course_file = new SplFileObject($course_path);
        $import_token = $courseService->createUploadAndImportCourseJob($courseId, 'false', null, $fileType, null, $course_file)->getResult();
        echo '<span class="importMessage">' . __( "Processing Import....", "scormcloud" ) . '</span>';
        do {
            $importJobResultSchema = $courseService->getImportJobStatus($import_token);
            sleep(1); //pause for a second
            if ($importJobResultSchema->getStatus() === "COMPLETE") {
                echo "Import is complete. Refresh the page to see your new course.";
            }
            if ($importJobResultSchema->getStatus() === "ERROR") {
                echo "There was an error importing the content.";
            }
        } while ($importJobResultSchema->getStatus() === "RUNNING");
    }
} else {
    echo '<span class="importMessage">' . __( "There was an error uploading your package. Please try again.", "scormcloud" ) . '</span>';
}

?>
