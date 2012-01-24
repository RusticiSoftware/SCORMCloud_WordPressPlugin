<?php

require_once(SCORMCLOUD_BASE.'scormcloudplugin.php');
require_once(SCORMCLOUD_BASE.'ui/scormcloudcatalogwidget.php');
require_once(SCORMCLOUD_BASE.'ui/scormcloudregistrationswidget.php');
require_once(SCORMCLOUD_BASE.'admin/scormcloudadminui.php');

class ScormCloudUi
{
    public static function initialize()
    {
        self::enqueue_includes();
        
        self::embed_plugin();
        
        ScormCloudAdminUi::initialize();
    }
    
    public static function initialize_widgets()
    {
        register_widget('ScormCloudRegistrationsWidget');
        register_widget('ScormCloudCatalogWidget');
    }
    
    public static function enqueue_includes()
    {
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        
        wp_enqueue_script( 'scormclouddialog', plugins_url('/scormcloud/scripts/scormcloud.dialog.js'), array());
        wp_enqueue_script('scormcloud-post', plugins_url('/scormcloud/scripts/scormcloud.post.js'), array());
        wp_register_style('scormcloud-post-style', plugins_url('/scormcloud/css/scormcloud.post.css'));
        wp_enqueue_style('scormcloud-post-style');
    }
    
    public static function embed_plugin()
    {
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }
        
        if (get_user_option('rich_editing') == 'true') {
            add_filter('mce_external_plugins', array(__CLASS__, 'embed_editor_plugin'), 0);
            add_filter('mce_buttons', array(__CLASS__, 'embed_editor_button'), 0);
        }
    
        add_action('edit_form_advanced', array(__CLASS__, 'add_quicktags'));
        add_action('edit_page_form', array(__CLASS__, 'add_quicktags'));
    }
    
    public static function embed_editor_plugin($plugins)
    {
        $plugins['scormCloudEmbed'] = plugins_url('/scormcloud/tinymce3/editor_plugin.js');
        return $plugins;
    }

    public static function embed_editor_button($buttons) {
        array_push($buttons, 'separator', 'scormCloudEmbed');
        return $buttons;
    }

    public static function add_quicktags()
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
    
    
}