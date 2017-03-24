<?php
global $wpdb;

if ( defined( 'ABSPATH' ) ) {
	require_once( ABSPATH . 'wp-load.php' );
} else {
	require_once( '../../../wp-load.php' );
}
require_once( ABSPATH . 'wp-admin/includes/admin.php' );

require_once( SCORMCLOUD_BASE . 'scormcloudplugin.php' );;
$scorm_service = ScormCloudPlugin::get_cloud_service();


$id   = $_GET[ 'id' ];
$mode = isset( $_GET[ 'mode' ] ) ? $_GET[ 'mode' ] : 'new';

$upload_service = $scorm_service->getUploadService();

//echo $uploadService->GetUploadLink($CFG->wwwroot.'/mod/scormcloud/importcallback.php?courseid='.$id);
/*** check for https ***/
$protocol = isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ? 'https' : 'http';
/*** return the full address ***/
$basepath       = $protocol . '://' . $_SERVER[ 'HTTP_HOST' ] . substr( $_SERVER[ 'REQUEST_URI' ], 0, strpos( $_SERVER[ 'REQUEST_URI' ], 'scormcloud' ) ) . 'scormcloud/';
$import_callback = $basepath . '/importcallback.php';

echo '<table >';

echo '<tr><td >';
echo '<form id="uploadform" action="' . $upload_service->GetUploadLink( $import_callback . '?courseid=' . $id . '&mode=' . $mode ) . '" method="post" ';
echo 'enctype="multipart/form-data">';
echo '<label for="file">' . __( "Filename:", "scormcloud" ) . '</label>';
echo '<input type="file" name="filedata" id="file" /> ';
echo '<input type="submit" id="submit" name="submit" value="' . __( "Submit", "scormcloud" ) . '" />';
echo '<span class="importMessage hidden">' . __( "Importing Package......", "scormcloud" ) . '</span>';
echo '</form>';
echo '</td></tr>';
echo '</table>';

?>
<script
		type="text/javascript"
		src='https://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js'></script>
<script type="text/javascript">

	jQuery("#uploadform").submit(function () {
		if (jQuery("#uploadform input[name='filedata']").val().length == 0) return false;
		jQuery("input[type=submit]", this).attr("disabled", "disabled");
		jQuery(".importMessage").removeClass('hidden');
	});
</script>
