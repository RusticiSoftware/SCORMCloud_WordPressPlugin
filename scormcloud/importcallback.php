<?php
/*
 ==============================================================================

 Copyright (c) 2009 Rustici Software

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 See the GNU General Public License for more details.

 ==============================================================================
 */
global $wpdb;
if ( defined( 'ABSPATH' ) ) {
	require_once( ABSPATH . 'wp-load.php' );
} else {
	require_once( '../../../wp-load.php' );
}
require_once( ABSPATH . 'wp-admin/includes/admin.php' );

require_once( SCORMCLOUD_BASE . 'scormcloudplugin.php' );
$ScormService = ScormCloudPlugin::get_cloud_service();

$scormcloudid = $_GET[ 'courseid' ];
$mode         = $_GET[ 'mode' ];
if ( $mode == null ) {
	$mode = 'new';
}

$location = $_GET[ 'location' ];
$success  = $_GET[ 'success' ];

wp_enqueue_style( "global" );
wp_enqueue_style( "wp-admin" );
wp_register_style( 'scormcloud-admin-style', plugins_url( '/css/scormcloud.admin.css' ) );
wp_enqueue_style( 'scormcloud-admin-style' );
wp_print_styles();

$courseService = $ScormService->getCourseService();


if ( $success == 'true' ) {
	echo '<span class="importMessage">' . __( "Processing Import....", "scormcloud" ) . '</span>';

	if ( $mode == 'update' ) {
		//version the uploaded course
		$result = $courseService->VersionUploadedCourse( $scormcloudid, $location, null );
		$importResult = new ImportResult( $result );

	} else {

		//import the uploaded course
		$result = $courseService->ImportUploadedCourse( $scormcloudid, $location, null );


	}

	echo '<script>window.parent.location=window.parent.location;</script>';

} else {
	echo '<span class="importMessage">' . __( "There was an error uploading your package. Please try again.", "scormcloud" ) . '</span>';


}

?>