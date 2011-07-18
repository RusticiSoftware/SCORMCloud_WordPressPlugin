<?php 
	/*
	Plugin Name: SCORM Cloud For WordPress
	Plugin URI: http://scorm.com/wordpress
	Description: Tap the power of SCORM to deliver and track training right from your WordPress-powered site. Just add the SCORM Cloud widget to the sidebar or use the SCORM Cloud button to add a link directly in a post or page.
	Author: Rustici Software
	Version: 1.0.6.6
	Author URI: http://www.scorm.com
	*/
	
	function scormcloud_admin_actions() {
		//add_management_page("SCORM Cloud Settings", "SCORM Cloud Settings", 1, "SCORM Cloud Settings", "scormcloud_settings");  
	
		
        wp_enqueue_script('scormcloud-admin', plugins_url('/scormcloud/scripts/scormcloud.admin.js'), array() );
        wp_enqueue_script('scormcloud-date_format', plugins_url('/scormcloud/scripts/date.format.js'), array() );
        wp_register_style('scormcloud-admin-style', plugins_url('/scormcloud/css/scormcloud.admin.css'));
        wp_enqueue_style('scormcloud-admin-style');
		
		global $plugin_hooks;
		$plugin_hooks[] = add_menu_page('SCORM Cloud Overview', 'SCORM Cloud', 'publish_posts', __FILE__, 'scormcloud_toplevel_page');
		//$plugin_hooks[] = add_submenu_page(__FILE__, 'SCORM Engine - Import New Package', 'Import New Package', 8, 'importpackage', 'scormcloud_import_package');
		$plugin_hooks[] = add_submenu_page(__FILE__, 'SCORM Cloud Courses', 'Courses', 'publish_posts', 'scormcloudcourses', 'scormcloud_manage_courses');
		$plugin_hooks[] = add_submenu_page(__FILE__, 'SCORM Cloud Training', 'Training', 'publish_posts', 'scormcloudtraining', 'scormcloud_manage_training');
        require_once('scormcloud.wp.php');
        if (!scormcloud_isScormCloudNetworkManaged() || (scormcloud_isScormCloudNetworkManaged() && is_super_admin())){
            $plugin_hooks[] = add_submenu_page(__FILE__, 'SCORM Cloud Settings', 'Settings', 'publish_posts', 'scormcloudsettings', 'scormcloud_settings');
        }
        require_once('scormcloud_contextualHelp.php');
        add_action( 'contextual_help', 'scormcloud_contextualHelp',10,3 );
	}
    
	load_plugin_textdomain( 'scormcloud', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
    
	add_action('admin_menu', 'scormcloud_admin_actions');
	register_activation_hook(__FILE__,'scormcloud_install');
	
    if( !is_admin()){
        wp_deregister_script('jquery');
        wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"), false, '');
        wp_enqueue_script('jquery');
    }

    
    function scormcloud_install () {
	   	include('scormcloud_install.php');
	}
	
    //Add button to editor
    add_action( 'init', 'scormcloud_editpost_addbuttons');
    function scormcloud_editpost_addbuttons() {
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
            return;
        }
        if ( get_user_option('rich_editing') == 'true') {
            //add_filter( 'tiny_mce_version', 'tiny_mce_version', 0 );
            add_filter( 'mce_external_plugins', 'scormcloud_embed_plugin', 0 );
            add_filter( 'mce_buttons', 'scormcloud_editpost_button', 0);
        }
        
        add_action( 'edit_form_advanced', 'add_quicktags' );
        add_action( 'edit_page_form', 'add_quicktags' );

    }
    
    function scormcloud_embed_plugin( $plugins ) {
        $plugins['scormCloudEmbed'] = plugins_url('/scormcloud/tinymce3/editor_plugin.js');
        return $plugins;
    }

    function scormcloud_editpost_button( $buttons ) {
        array_push( $buttons, 'separator', 'scormCloudEmbed' );
        return $buttons;
    }
    
    add_action( 'admin_head', 'set_js_vars');
    wp_enqueue_script( 'scormclouddialog', plugins_url('/scormcloud/scripts/scormcloud.dialog.js'), array() );
    function set_js_vars()
	{
?>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
	if (typeof ScormCloud !== 'undefined' && typeof ScormCloud.Dialog !== 'undefined') {
        var dialogUrl = "<?php echo plugins_url('/scormcloud/embedTraining.php'); ?>";
        
		ScormCloud.Dialog.embedTrainingUrl = dialogUrl;
	}
// ]]>	
</script>
<?php
	}

?>
<?php
    
    // Add a button to the quicktag view
	function add_quicktags()
	{
		$buttonshtml = '<input type="button" class="ed_button" onclick="ScormCloud.Dialog.embed.apply(ScormCloud.Dialog);return false;" title="Insert a SCORM Cloud training link." value="SCORM Cloud Training" />';
?>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
	(function(){
		
		if (typeof jQuery === 'undefined') {
			return;
		}
		
		jQuery(document).ready(function(){
			// Add the buttons to the HTML view
			jQuery("#ed_toolbar").append('<?php echo $buttonshtml; ?>');
		});
	}());
// ]]>
</script>
<?php	
	}
    
    
	function scormcloud_init_method() {
	    wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');            
	}    

	add_action('init', 'scormcloud_init_method');
	
	// mt_toplevel_page() displays the page content for the custom Test Toplevel menu
	function scormcloud_toplevel_page() {
		include('scormcloud_startpage.php');
	}
	
	function scormcloud_settings(){
		include('scormcloud_settings.php');
	}
	function scormcloud_import_package(){
		include('scormcloud_import_package.php');
	}	
	function scormcloud_manage_courses(){
		include('scormcloud_manage_courses.php');
	}
	function scormcloud_manage_training(){
		include('scormcloud_manage_training.php');
	}
	
	function tb_enqueue() {
	    wp_enqueue_script(‘jquery’);
	    wp_enqueue_script(‘thickbox’);
	}
	
	
    /**
    * Register scormcloud widget.
    *
    * Calls 'widgets_init' action after the Hello World widget has been registered.
    */
    function ScormCloudRegistrationsWidgetInit() {
        require_once('scormcloud.widget.php');
        register_widget('ScormCloudRegistrationsWidget');
    }	
    add_action('widgets_init', 'ScormCloudRegistrationsWidgetInit');
    
    function ScormCloudCatalogWidgetInit() {
        require_once('scormcloud.catalogwidget.php');
        register_widget('ScormCloudCatalogWidget');
    }	
    add_action('widgets_init', 'ScormCloudCatalogWidgetInit');
    

    
    function scormcloud_addTrainingHTML( $content ) {
        require_once('scormcloud_makeBlogEntry.php');
        return scormcloud_makeBlogEntry($content);
    }
    wp_enqueue_script('scormcloud-post', plugins_url('/scormcloud/scripts/scormcloud.post.js'), array() );
    wp_register_style('scormcloud-post-style', plugins_url('/scormcloud/css/scormcloud.post.css'));
    wp_enqueue_style('scormcloud-post-style');
    add_filter('the_content','scormcloud_addTrainingHTML');

    function sc_UpdatePostInvite( $postId ) {
        require_once('scormcloud_makeBlogEntry.php');
        scormcloud_UpdatePostInvite($postId);
    }
    add_action('save_post','sc_UpdatePostInvite');
    

	function scormcloud_updateLearnerInfo( $userId){
		global $wpdb;
	    require_once('scormcloud.wp.php');
		require_once('SCORMAPI/DebugLogger.php');
		$ScormService = scormcloud_getScormEngineService();
		$regService = $ScormService->getRegistrationService();
		$userData = get_userdata($userId);
		$response = $regService->UpdateLearnerInfo($userData->user_email,$userData->user_firstname,$userData->user_lastname);
		write_log($response);
	}
	add_action('profile_update', 'scormcloud_updateLearnerInfo');
    
?>