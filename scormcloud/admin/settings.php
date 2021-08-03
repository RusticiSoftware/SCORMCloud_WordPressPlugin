<?php

require_once(SCORMCLOUD_BASE.'scormcloudplugin.php');

if(isset($_POST['scormcloud_hidden']) && $_POST['scormcloud_hidden'] == 'Y') {
    $appid = $_POST['scormcloud_appid'];
    $engine_url = $_POST['scormcloud_engine_url'];
    $secretkey = $_POST['scormcloud_secretkey'];
    $player_cssurl = $_POST['scormcloud_player_cssurl'];
    $proxy = $_POST['proxy'];
	$network_managed = ScormCloudPlugin::is_network_managed();

    if (!$network_managed) {
        update_option('scormcloud_appid', $appid);
        update_option('scormcloud_engine_url', $engine_url);
        update_option('scormcloud_secretkey', $secretkey);
    }
    
    update_option('scormcloud_player_cssurl', $player_cssurl);
    update_option('proxy', $proxy);

    $ScormService = ScormCloudPlugin::get_cloud_service();
    if (!$ScormService->isValidUrl()){
        echo "<div class='updated'><p class='failed'><strong>". __("Invalid Service Url.  Check your URL and try again or clear out the bad URL and the default URL will be used.","scormcloud")."</strong></p></div>";
    } else {
        try {
            $valid = $ScormService->isValidAccount();
        } catch (Exception $e) {
            $valid = false;
        }
        if ($valid === "107") {
            echo "<div class='updated'><p class='failed'><strong>". __("It appears that your server UTC time is out of sync with that of the SCORM Cloud. Try updating the time on your server using ntpdate or something equivalent.  Authenticated calls to the server require a timestamp within 15 minutes of current UTC time.","scormcloud")."</strong></p></div>";
        } elseif ($valid !== true) {
            echo "<div class='updated'><p class='failed'><strong>". __("Invalid Credentials.  Check your App Id and Secret Key and try again.","scormcloud")."</strong></p></div>";
        } else {
            echo "<div class='updated'><p><strong>". __("Options saved.","scormcloud")."</strong></p></div>";
        }
    }


} else {
    $ScormService = ScormCloudPlugin::get_cloud_service();

    try {
        if (!$ScormService->isValidUrl()) {
            echo "<div class='updated'><p class='failed'><strong>". __("Invalid Service Url.  Check your URL click 'Update Options' or clear out the bad URL and the default URL will be used.","scormcloud")."</strong></p></div>";
        } elseif (!$ScormService->isValidAccount()) {
            echo "<div class='updated'><p><strong>". __("Please fill in your SCORM Cloud credentials and click 'Update Options'.","scormcloud")."</strong></p></div>";
        }
    } catch (Exception $e) {
        echo '<div class="updated"><p class="failed"><strong>'. __('Your SCORM Cloud credentials could not be verified. Please check that the Service URL, App ID, and Secret Key options are correct.', 'scormcloud').'</strong></p></div>';
    }

    $appid = get_option('scormcloud_appid');
    $secretkey = get_option('scormcloud_secretkey');
    $engine_url = (strlen(get_option('scormcloud_engine_url')) > 0) ? get_option('scormcloud_engine_url') : "http://cloud.scorm.com/EngineWebServices";
    $player_cssurl = (strlen(get_option('scormcloud_player_cssurl')) > 0) ? get_option('scormcloud_player_cssurl') : 'http://cloud.scorm.com/sc/css/cloudPlayer/cloudstyles.css';
    $proxy = get_option('proxy');
    $network_managed = ScormCloudPlugin::is_network_managed();
}
?>
<div
	class="scormcloud-admin-page settings">

 <h2> <?php    echo esc_attr__( 'Rustici Software SCORM Cloud Settings','scormcloud' ) ; ?> </h2>
<p><em><?php _e('To configure the SCORM Cloud for WordPress plugin, you need the AppID and Secret Key credentials for your account. They can be found by going to your Apps page on the SCORM Cloud site.', 'scormcloud'); ?> 
<?php _e('If you need an account on SCORM Cloud,', 'scormcloud'); ?>
	<a
	href="https://cloud.scorm.com" target="_blank"
	title="<?php _e("Open the SCORM Cloud Site in a new window.","scormcloud"); ?>"><?php _e("sign up now","scormcloud"); ?></a>.
</em></p>

<form name="scormcloud_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<input type="hidden" name="scormcloud_hidden" value="Y">

<?php if (!$network_managed) { ?>
<h3><?php _e('SCORM Cloud Account Credentials'); ?></h3>
<p><?php _e("App ID: ","scormcloud" ); ?><input type="text"	name="scormcloud_appid" value="<?php echo $appid; ?>" size="20"></p>
<p><?php _e("Secret Key: ","scormcloud" ); ?><input type="text"	name="scormcloud_secretkey" value="<?php echo $secretkey; ?>" size="50"></p>
	
<h3><?php _e('Advanced Settings'); ?></h3>
<p><?php _e("Cloud Engine URL: ","scormcloud" ); ?><input type="text"
	name="scormcloud_engine_url" value="<?php echo $engine_url; ?>"
	size="50"></p>
<?php } else { ?>
<?php $sharecourses = get_site_option('scormcloud_sharecourses'); ?>
<h3>Network Managed</h3>
<p>
<?php _e('The network administrator has set the SCORM Cloud plugin to its network managed state. This means that all sites within the network will use the same SCORM Cloud account. In order for individual WordPress sites to use separate SCORM Cloud accounts, realms, or application IDs, the network administrator must disable network managed mode.', 'scormcloud'); ?>
</p>
<?php if ($sharecourses) { ?>
<p>
	<?php _e('Additionally, the network administrator has enabled the "Share Courses" option, which means that all sites within the network will have access to the same course library.', 'scormcloud'); ?>
</p>
<?php } ?>
<?php if (is_super_admin()) {?>
<p>
<a href="<?php echo(site_url().'/wp-admin/network/admin.php?page=scormcloud/network-admin/settings'); ?>"><?php _e('Network Admin Settings'); ?></a>
</p>
<?php } ?>
<h3><?php _e('Advanced Settings'); ?></h3>
<?php } ?>

<p><?php _e("SCORM Player Stylesheet Url: ","scormcloud" ); ?><input
	type="text" name="scormcloud_player_cssurl"
	value="<?php echo $player_cssurl; ?>" size="50"></p>

<p><?php _e("Proxy: ","proxy"); ?><input
    type="text" name="proxy"
    value="<?php echo $proxy; ?>" size="50">
</p>
<p class="submit"><input type="submit" name="Submit" value="<?php _e("Update Settings","scormcloud" ) ?>" /></p>
</form>
</div>

<!--<iframe class='signupFrame' src='https://cloud.scorm.com/sc/embedded/EmbeddedSignUpForm?cssUrl=<?php //echo plugins_url('/scormcloud/css/scormcloud.admin.css'); ?>'/>-->

</div>
