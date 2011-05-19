<?php

require_once('scormcloud.wp.php');



    if(isset($_POST['scormcloud_hidden']) && $_POST['scormcloud_hidden'] == 'Y') {
		//Form data sent
		$dbhost = isset($_POST['scormcloud_dbhost']) ? $_POST['scormcloud_dbhost'] : null;
        $dbname = isset($_POST['scormcloud_dbname']) ? $_POST['scormcloud_dbname'] : null;
        $dbuser = isset($_POST['scormcloud_dbuser']) ? $_POST['scormcloud_dbuser'] : null;
        $dbpwd = isset($_POST['scormcloud_dbpwd']) ? $_POST['scormcloud_dbpwd'] : null;
        $appid = $_POST['scormcloud_appid'];
        $engine_url = $_POST['scormcloud_engine_url'];
        $secretkey = $_POST['scormcloud_secretkey'];
        $player_cssurl = $_POST['scormcloud_player_cssurl'];
        
        if (scormcloud_isScormCloudNetworkManaged()){
            $sharecourses = $_POST['scormcloud_sharecourses'];
            
            update_site_option('scormcloud_dbhost', $dbhost);
            update_site_option('scormcloud_dbname', $dbname);
            update_site_option('scormcloud_dbuser', $dbuser);
            update_site_option('scormcloud_dbpwd', $dbpwd);
            update_site_option('scormcloud_appid', $appid);
            update_site_option('scormcloud_engine_url', $engine_url);
            update_site_option('scormcloud_secretkey', $secretkey);
            update_site_option('scormcloud_sharecourses', $sharecourses);
            update_site_option('scormcloud_player_cssurl', $player_cssurl);
            
            
        } else {
            update_option('scormcloud_dbhost', $dbhost);
            update_option('scormcloud_dbname', $dbname);
            update_option('scormcloud_dbuser', $dbuser);
            update_option('scormcloud_dbpwd', $dbpwd);
            update_option('scormcloud_appid', $appid);
            update_option('scormcloud_engine_url', $engine_url);
            update_option('scormcloud_secretkey', $secretkey);
            update_option('scormcloud_player_cssurl', $player_cssurl);
            
        }
        
        require_once('scormcloud.wp.php');
        $ScormService = scormcloud_getScormEngineService();
        
        if (!$ScormService->isValidUrl()){
            echo "<div class='updated'><p class='failed'><strong>". __("Invalid Service Url.  Check your URL and try again or clear out the bad URL and the default URL will be used.","scormcloud")."</strong></p></div>";
        } else {
			$valid = $ScormService->isValidAccount();
			if ($valid === "107"){
				echo "<div class='updated'><p class='failed'><strong>". __("It appears that your server UTC time is out of sync with that of the SCORM Cloud. Try updating the time on your server using ntpdate or something equivalent.  Authenticated calls to the server require a timestamp within 15 minutes of current UTC time.","scormcloud")."</strong></p></div>";
			} elseif ($valid !== true){
            	echo "<div class='updated'><p class='failed'><strong>". __("Invalid Credentials.  Check your App Id and Secret Key and try again.","scormcloud")."</strong></p></div>";
        	} else {
            	echo "<div class='updated'><p><strong>". __("Options saved.","scormcloud")."</strong></p></div>";
			}
		}
        
		
	} else {
        require_once('scormcloud.wp.php');
        $ScormService = scormcloud_getScormEngineService();
        
        if (!$ScormService->isValidUrl()){
            echo "<div class='updated'><p class='failed'><strong>". __("Invalid Service Url.  Check your URL click 'Update Options' or clear out the bad URL and the default URL will be used.","scormcloud")."</strong></p></div>";
        } elseif (!$ScormService->isValidAccount()){
            echo "<div class='updated'><p><strong>". __("Please fill in your SCORM Cloud credentials and click 'Update Options'.","scormcloud")."</strong></p></div>";
        } 
        
		//Normal page display
        if (scormcloud_isScormCloudNetworkManaged()){
            $dbhost = get_site_option('scormcloud_dbhost');  
            $dbname = get_site_option('scormcloud_dbname');  
            $dbuser = get_site_option('scormcloud_dbuser');  
            $dbpwd = get_site_option('scormcloud_dbpwd');  
            $appid = get_site_option('scormcloud_appid');  
            $secretkey = get_site_option('scormcloud_secretkey'); 
            $engine_url = (strlen(get_site_option('scormcloud_engine_url')) > 0) ? get_site_option('scormcloud_engine_url') : "http://cloud.scorm.com/EngineWebServices";
            $player_cssurl = (strlen(get_site_option('scormcloud_player_cssurl')) > 0) ? get_site_option('scormcloud_player_cssurl') : 'http://cloud.scorm.com/sc/css/cloudPlayer/cloudstyles.css';
            $sharecourses = get_site_option('scormcloud_sharecourses');
            
        } else {
            $dbhost = get_option('scormcloud_dbhost');  
            $dbname = get_option('scormcloud_dbname');  
            $dbuser = get_option('scormcloud_dbuser');  
            $dbpwd = get_option('scormcloud_dbpwd');  
            $appid = get_option('scormcloud_appid');  
            $secretkey = get_option('scormcloud_secretkey'); 
            $engine_url = (strlen(get_option('scormcloud_engine_url')) > 0) ? get_option('scormcloud_engine_url') : "http://cloud.scorm.com/EngineWebServices";
            $player_cssurl = (strlen(get_option('scormcloud_player_cssurl')) > 0) ? get_option('scormcloud_player_cssurl') : 'http://cloud.scorm.com/sc/css/cloudPlayer/cloudstyles.css';
        }
	}
?>
<div class="scormcloud-admin-page settings">

<div class="wrap">
    
	<?php    echo "<h2>" . __( 'Rustici Software SCORM Cloud Settings','scormcloud' ) . "</h2>"; ?>
	<p><em><?php _e("The following settings require the credentials provided when you signed up for your SCORM Cloud account.  They can also be found by going to your Apps page in the SCORM Cloud site.","scormcloud" ); ?>
    <br/><?php _e("If you need an account on SCORM Cloud, ","scormcloud"); ?><a href="https://cloud.scorm.com" target="_blank" title="<?php _e("Open the SCORM Cloud Site in a new window.","scormcloud"); ?>"><?php _e("click here to go there now","scormcloud"); ?></a>.</em></p>
    
	<form name="scormcloud_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="scormcloud_hidden" value="Y">
		<p><?php _e("Cloud Engine URL: ","scormcloud" ); ?><input type="text" name="scormcloud_engine_url" value="<?php echo $engine_url; ?>" size="50"></p>
		
		<p><?php _e("App ID: ","scormcloud" ); ?><input type="text" name="scormcloud_appid" value="<?php echo $appid; ?>" size="20">
		<br/><em><?php _e(" ex: MYAYX1KB3G","scormcloud" ); ?></em></p>
		<p><?php _e("Secret Key: ","scormcloud" ); ?><input type="text" name="scormcloud_secretkey" value="<?php echo $secretkey; ?>" size="50">
		<br/><em><?php _e(" ex: fJ4Q2HwEZbhRLeftHLv0SjzIVpJLXRiyfyOiWBo0","scormcloud" ); ?></em></p>
        
        <br/>
        <p><?php _e("SCORM Player Stylesheet Url: ","scormcloud" ); ?><input type="text" name="scormcloud_player_cssurl" value="<?php echo $player_cssurl; ?>" size="50">
		<br/><em><?php _e(" ex: http://cloud.scorm.com/sc/css/cloudPlayer/cloudstyles.css","scormcloud" ); ?></em></p>
    <?php 
    if (scormcloud_isScormCloudNetworkManaged()){ ?>
        <p><input type="checkbox" name="scormcloud_sharecourses" <?php echo ($sharecourses ? "checked" : ""); ?> /><?php _e(" Share courses among all sites.","scormcloud" ); ?></p>
    <?php } ?>
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e("Update Settings","scormcloud" ) ?>" />
		</p>
	</form>
</div>

<!--<iframe class='signupFrame' src='https://cloud.scorm.com/sc/embedded/EmbeddedSignUpForm?cssUrl=<?php //echo plugins_url('/scormcloud/css/scormcloud.admin.css'); ?>'/>-->

</div>
