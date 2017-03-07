<?php

require_once(SCORMCLOUD_BASE.'scormcloudplugin.php');

$force_network_settings = true;

if(isset($_POST['scormcloud_hidden']) && $_POST['scormcloud_hidden'] == 'Y') {
    $appid = $_POST['scormcloud_appid'];
    $engine_url = $_POST['scormcloud_engine_url'];
    $secretkey = $_POST['scormcloud_secretkey'];
    $network_managed = isset($_POST['scormcloud_networkmanaged']);
    $sharecourses = isset($_POST['scormcloud_sharecourses']);

    update_site_option('scormcloud_appid', $appid);
    update_site_option('scormcloud_engine_url', $engine_url);
    update_site_option('scormcloud_secretkey', $secretkey);
    update_site_option('scormcloud_networkmanaged', $network_managed);
    update_site_option('scormcloud_sharecourses', $sharecourses);
    
    $ScormService = ScormCloudPlugin::get_cloud_service($force_network_settings);

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
            echo "<div class='updated'><p><strong>". __("Settings saved.","scormcloud")."</strong></p></div>";
        }
    }


} else {
    $ScormService = ScormCloudPlugin::get_cloud_service($force_network_settings);

    try {
        if (!$ScormService->isValidUrl()) {
            echo "<div class='updated'><p class='failed'><strong>". __("Invalid Service Url.  Check your URL and click 'Update Settings' or clear out the bad URL and the default URL will be used.","scormcloud")."</strong></p></div>";
        } elseif (!$ScormService->isValidAccount()) {
            echo "<div class='updated'><p><strong>". __("Please fill in your SCORM Cloud credentials and click 'Update Settings'.","scormcloud")."</strong></p></div>";
        }
    } catch (Exception $e) {
        echo '<div class="updated"><p class="failed"><strong>'. __('Your SCORM Cloud credentials could not be verified. Please check that the Service URL, App ID, and Secret Key options are correct.', 'scormcloud').'</strong></p></div>';
    }
    
    $appid = get_site_option('scormcloud_appid');
    $secretkey = get_site_option('scormcloud_secretkey');
    $engine_url = (strlen(get_site_option('scormcloud_engine_url')) > 0) ? get_site_option('scormcloud_engine_url') : "http://cloud.scorm.com/EngineWebServices";
    $sharecourses = get_site_option('scormcloud_sharecourses');
    $network_managed = get_site_option('scormcloud_networkmanaged');
    
}
?>
<div class="scormcloud-admin-page settings">

    <h2> <?php echo esc_attr__( 'Rustici Software SCORM Cloud Settings', 'scormcloud' ) ; ?> </h2>
<p><em><?php _e('To configure the SCORM Cloud for WordPress plugin, you need the AppID and Secret Key credentials for your account. They can be found by going to your Apps page on the SCORM Cloud site.', 'scormcloud'); ?> 
<?php _e('If you need an account on SCORM Cloud,', 'scormcloud'); ?>
	<a
	href="https://cloud.scorm.com" target="_blank"
	title="<?php _e("Open the SCORM Cloud Site in a new window.","scormcloud"); ?>"><?php _e("sign up now","scormcloud"); ?></a>.
</em></p>

<form name="scormcloud_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<input type="hidden" name="scormcloud_hidden" value="Y">

<h3><?php _e('SCORM Cloud Account Credentials'); ?></h3>
<p><?php _e("App ID: ","scormcloud" ); ?><input type="text"	name="scormcloud_appid" value="<?php echo $appid; ?>" size="20"></p>
<p><?php _e("Secret Key: ","scormcloud" ); ?><input type="text"	name="scormcloud_secretkey" value="<?php echo $secretkey; ?>" size="50"></p>

<h3><?php _e('Network Settings'); ?></h3>
<p><input type="checkbox" name="scormcloud_networkmanaged" <?php echo ($network_managed ? "checked" : "") ?> />
    <?php _e('Use same SCORM Cloud account across all sites.', 'scormcloud'); ?></p>
<p><input type="checkbox" name="scormcloud_sharecourses"<?php echo ($sharecourses ? "checked" : ""); ?> />
    <?php _e(" Share courses among all sites.","scormcloud" ); ?></p>
	
<h3><?php _e('Advanced Settings'); ?></h3>
<p><?php _e("Cloud Engine URL: ","scormcloud" ); ?><input type="text"
	name="scormcloud_engine_url" value="<?php echo $engine_url; ?>"
	size="50"></p>

<p class="submit"><input type="submit" name="Submit" value="<?php _e("Update Settings","scormcloud" ) ?>" /></p>
</form>
</div>

</div>
