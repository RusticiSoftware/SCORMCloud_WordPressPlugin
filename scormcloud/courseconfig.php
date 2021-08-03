<?php

define( 'SCORMCLOUD_BASE', './' );


global $wpdb;
if ( defined('ABSPATH') )
require_once(ABSPATH . 'wp-load.php');
else
require_once('../../../wp-load.php');


function is_network_managed() {
    if ( ! function_exists( 'get_site_option' ) ) {
        return false;
    }
    if ( null !== get_site_option( 'scormcloud_networkmanaged' ) ) {
        return (bool) get_site_option( 'scormcloud_networkmanaged' );
    } else {
        return false;
    }
}

    require_once( 'SCORMCloud_PHPLibrary/ScormEngineService2.php' );
    require_once( 'SCORMCloud_PHPLibrary/ScormEngineUtilities.php' );

    if ( is_network_managed() || $force_network_settings ) {
        $appid      = get_site_option( 'scormcloud_appid' );
        $secretkey  = get_site_option( 'scormcloud_secretkey' );
        $engine_url = get_site_option( 'scormcloud_engine_url' );
        $proxy      = get_site_option( 'proxy' );

    } else {
        $appid      = get_option( 'scormcloud_appid' );
        $secretkey  = get_option( 'scormcloud_secretkey' );
        $engine_url = get_option( 'scormcloud_engine_url' );
        $proxy      = get_option( 'proxy' );
    }

    $origin = ScormEngineUtilities::getCanonicalOriginString( 'Rustici Software', 'WordPress', '1.1.2' );

    // arbitrary number 17 is the length of 'EngineWebServices'.
    if ( strlen( $engine_url ) < 17 ) {
        $engine_url = 'http://cloud.scorm.com/EngineWebServices';
    }

    $ScormService = new ScormEngineService( $engine_url, $appid, $secretkey, $origin, $proxy );

require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/ModelInterface.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/LaunchLinkRequestSchema.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/LearnerSchema.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/CreateRegistrationSchema.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/ReportageAuthTokenSchema.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/ReportageLinkSchema.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/RegistrationListSchema.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/RegistrationSchema.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/RegistrationCompletion.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/RegistrationSuccess.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/CourseReferenceSchema.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/ActivityResultSchema.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/StaticPropertiesSchema.php');

require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/SettingListSchema.php');
require_once(SCORMCLOUD_BASE.'SCORMCloud_PHPLibrary/v2/Model/SettingItem.php');

require_once(SCORMCLOUD_BASE.'/reportagehelper.php');

// require_once('/SCORMCloud_PHPLibrary/v2/Model/ReportageAuthTokenSchema.php');
// require_once('/SCORMCloud_PHPLibrary/v2/Model/ReportageLinkSchema.php');

$courseId = $_GET['courseid'];

//$ScormService = ScormCloudPlugin::get_cloud_service();
try {
    $isValidAccount = $ScormService->isValidAccount();
} catch (Exception $e) {
    $isValidAccount = false;
}

$courseService = $ScormService->getCourseService();

$courseConfig = $courseService->getCourseConfiguration($courseId)->getSettingItems();

$supportedConfigOptions = ['PlayerLaunchType', 'PlayerScoLaunchType', 'PlayerSuspendDataMaxLength', 'PlayerResetRunTimeData'];

$launchTypes = ['FRAMESET','NEW_WINDOW', 'NEW_WINDOW_AFTER_CLICK', 'NEW_WINDOW_WITHOUT_BROWSER_TOOLBAR', 'NEW_WINDOW_AFTER_CLICK_WITHOUT_BROWSER_TOOLBAR'];
$resetRunTimeData = ['NEVER', 'WHEN_EXIT_IS_NOT_SUSPEND', 'ON_EACH_NEW_SEQUENCING_ATTEMPT'];

echo '<div class="scormcloud-adming-page">';

foreach ( $courseConfig as $config) {
    if (in_array($config->getId(), $supportedConfigOptions)) {
        echo '<div class="course-config-option">' . $config->getId() . ' : ';
        if ( $config->getId() == 'PlayerLaunchType' || $config->getId() == 'PlayerScoLaunchType' ) {
            echo '<select id="' . $config->getId() . '">';
            foreach ( $launchTypes as $launchType) {
                echo '<option value="' . $launchType . '"';
                if ($config->getEffectiveValue() == $launchType) {
                    echo ' selected ';
                } 
                echo ' >' . $launchType . '</option>';
            }
            echo '</select>';
        } elseif ($config->getId() == 'PlayerResetRunTimeData') {
            echo '<select id="' . $config->getId() . '">';
            foreach ( $resetRunTimeData as $resetType) {
                echo '<option value="' . $resetType . '"';
                if ($config->getEffectiveValue() == $resetType) {
                    echo ' selected ';
                } 
                echo ' >' . $resetType . '</option>';
            }
            echo '</select>';
        } else {
            echo '<input type="text" id="' . $config->getId() . '" value="' . $config->getEffectiveValue() . '">';
        }
        echo '</div>';
    }
}

echo '<div><button id="saveConfig" class="course-config-save">Save Configuration Options</button><span id="status"></span></div>';
echo '</div>';

?>
<link rel="stylesheet" href="css/scormcloud.admin.css">
<style>
    .body { border: 1px black solid; }
.course-config-option {padding:8px 8px 8px 8px;}
.course-config-save {
    padding:8px 8px 8px 8px;
    margin: 10px 10px 10px 10px;
    }
</style>

<script>

var configsToSet = [];

saveConfig.addEventListener("click", function() {
    console.log('clicked');
    //var formdata = new FormData();
    var postData = {"settings":configsToSet};	
	var ajax = new XMLHttpRequest();
	ajax.addEventListener("load", completeHandler, false);
	ajax.addEventListener("error", errorHandler, false);
	ajax.open("POST", "courseconfigaction.php?courseId=<?= $courseId ?>");
	ajax.send(JSON.stringify(postData));
});
    
function completeHandler(event){
    console.log(event.target.responseText);
	document.getElementById("status").innerHTML = event.target.responseText;
}
function errorHandler(event){
	document.getElementById("status").innerHTML = "Save Failed. Please try again.";
}

PlayerLaunchType.addEventListener("change", function(e) { 
    configsToSet.push({"settingId":PlayerLaunchType.id,"value":PlayerLaunchType.value});
});

PlayerScoLaunchType.addEventListener("change", function(e) {
    configsToSet.push({"settingId":PlayerScoLaunchType.id,"value":PlayerScoLaunchType.value});
});

PlayerSuspendDataMaxLength.addEventListener("change", function(e) {
    console.log(PlayerSuspendDataMaxLength.value);
    configsToSet.push({"settingId":PlayerSuspendDataMaxLength.id,"value":PlayerSuspendDataMaxLength.value});
});

PlayerResetRunTimeData.addEventListener("change", function(e) {
    console.log(PlayerResetRunTimeData.value);
    configsToSet.push({"settingId":PlayerResetRunTimeData.id,"value":PlayerResetRunTimeData.value});
});

</script>