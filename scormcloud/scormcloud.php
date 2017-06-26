<?php
/*
 Plugin Name: SCORM Cloud For WordPress
 Plugin URI: http://scorm.com/wordpress
 Description: Tap the power of SCORM to deliver and track training right from your WordPress-powered site. Just add the SCORM Cloud widget to the sidebar or use the SCORM Cloud button to add a link directly in a post or page.
 Author: Rustici Software
 Version: 1.2.3
 Author URI: http://www.scorm.com
 */

define( 'SCORMCLOUD_BASE', WP_PLUGIN_DIR . '/' . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) );

require_once( 'scormcloudplugin.php' );
require_once( 'scormcloudui.php' );
require_once( 'SCORMCloud_PHPLibrary/DebugLogger.php' );

load_plugin_textdomain( 'scormcloud', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

register_activation_hook( __FILE__, array( 'ScormCloudPlugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ScormCloudPlugin', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'ScormCloudPlugin', 'uninstall' ) );

add_action( 'plugins_loaded', array( 'ScormCloudPlugin', 'update_check' ) );
add_action( 'init', array( 'ScormCloudPlugin', 'initialize' ) );
add_action( 'init', array( 'ScormCloudUi', 'initialize' ) );
add_action( 'widgets_init', array( 'ScormCloudUi', 'initialize_widgets' ) );

add_filter( 'script_loader_src', array( 'ScormCloudPlugin', 'agnostic_loader' ), 20, 2 );
add_filter( 'style_loader_src', array( 'ScormCloudPlugin', 'agnostic_loader' ), 20, 2 );
