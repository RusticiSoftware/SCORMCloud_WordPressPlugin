<?php

if ( defined('ABSPATH') )
require_once(ABSPATH . 'wp-load.php');
else
require_once('../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/admin.php');

global $wpdb;

echo '<div class="scormcloud-admin-page startpage">';

require_once(SCORMCLOUD_BASE.'scormcloudplugin.php');
$ScormService = ScormCloudPlugin::get_cloud_service();

try {
    $isValidAccount = $ScormService->isValidAccount();
} catch (Exception $e) {
    $isValidAccount = false;
}

if ($isValidAccount){
    //Reportage Includes
    echo '<script type="text/javascript" ';
    echo "src=\"https://cloud.scorm.com/Reportage/scripts/reportage.combined.js\"></script>\n";
    echo '<link rel="stylesheet" ';
    echo "href=\"https://cloud.scorm.com/Reportage/css/reportage.combined.css\" type=\"text/css\" media=\"screen\" />\n";
    echo '<div class="mod-scormcloud">';

    //Check for some defaults to set the form up
    $rptService = $ScormService->getReportingService();
    $rptAuth = $rptService->GetReportageAuth('FREENAV',true);
    $rServiceUrl = $rptService->GetReportageServiceUrl();

}


//Report banner SCORM Cloud branded?
echo '<div class="header">
            <h1>'. __("SCORM Cloud for Wordpress","scormcloud").'</h1>
			<a id="CloudConsoleLink" href="https://cloud.scorm.com" 
				target="_blank" title="'. __("Open the SCORM Cloud Site in a new window.","scormcloud").'">'. __("SCORM Cloud Account Management","scormcloud").'</a>';
if ($isValidAccount){
    $reportageUrl = $rServiceUrl.'Reportage/reportage.php?appId='.$ScormService->getAppId()."&registrationTags=".$GLOBALS['blog_id']."|_all";
    echo '&nbsp;&nbsp;|&nbsp;&nbsp;
            <a id="ReportageLink" href="'.$rptService->GetReportUrl($rptAuth, $reportageUrl).'" 
				target="_blank" title="'. __("Open the SCORM Reportage Console in a new window.","scormcloud").'">'. __("SCORM Cloud Reportage","scormcloud").'</a>';
}
echo "</div>";

if (!$isValidAccount){

    if (is_super_admin())
    {
        $settings_url = site_url().'/wp-admin/network/admin.php?page=scormcloud/network-admin/settings';
    }
    else
    {
        $settings_url = site_url().'/wp-admin/admin.php?page=scormcloud/admin/settings';
    }
    echo '<div class="settingsPageLink"><a href="'.$settings_url.'"
				title="'. __("Click here to configure your SCORM Cloud plugin.","scormcloud").'">'. __("Click Here to go to the settings page to configure the SCORM Cloud wordpress Plugin.","scormcloud").'</a></div>';

}
/*
 echo "<div class='meta-box-sortables'>";
 echo "<div class='reportageWrapper postbox ".($isValidAccount ? "closed" : "")."'>";
 echo "<div class='handlediv'><br></div><h3 class='hndle'><span>". __("About SCORM Cloud","scormcloud")."</span></h3>";
 echo "<div class='inside'>";

 echo '<div class="aboutScorm">
 <object style="float:right;" width="480" height="385"><param name="movie" value="http://www.youtube.com/v/nP657pV6OWU&hl=en_US&fs=1&"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/nP657pV6OWU&hl=en_US&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="385"></embed></object>
 This will be an explantion of SCORM Cloud.  Maybe with a video of how it works... maybe like this...
 <div style="clear:both;"></div>
 </div>';

 echo "</div></div>";
 */

if ($isValidAccount){
    //  AppId Summary Report
    if(!isset($dateRangeStart))
    {
        $dateRangeStart = '2009-01-01';
    }
    if(!isset($dateRangeEnd))
    {
        $dateRangeEnd = date("Y-m-d");
    }

    $dateOptions = new DateRangeSettings(null,$dateRangeStart,$dateRangeEnd,null);

    $tagSettings = new TagSettings();
    $tagSettings->addTag('registration',$GLOBALS['blog_id']);

    $sumWidgetSettings = new WidgetSettings($dateOptions,$tagSettings);
    $sumWidgetSettings->setShowTitle(true);
    $sumWidgetSettings->setScriptBased(true);
    $sumWidgetSettings->setEmbedded(true);
    $sumWidgetSettings->setVertical(false);
    $sumWidgetSettings->setDivname('TotalSummary');

    $coursesWidgetSettings = new WidgetSettings($dateOptions,$tagSettings);
    $coursesWidgetSettings->setShowTitle(true);
    $coursesWidgetSettings->setScriptBased(true);
    $coursesWidgetSettings->setEmbedded(true);
    $coursesWidgetSettings->setExpand(true);
    $coursesWidgetSettings->setDivname('CourseListDiv');

    $learnersWidgetSettings = new WidgetSettings($dateOptions,$tagSettings);
    $learnersWidgetSettings->setShowTitle(true);
    $learnersWidgetSettings->setScriptBased(true);
    $learnersWidgetSettings->setEmbedded(true);
    $learnersWidgetSettings->setExpand(true);
    $learnersWidgetSettings->setDivname('LearnersListDiv');

    $summaryUrl = $rptService->GetWidgetUrl($rptAuth,'allSummary',$sumWidgetSettings);
    $coursesUrl = $rptService->GetWidgetUrl($rptAuth,'courseRegistration',$coursesWidgetSettings);
    $learnersUrl = $rptService->GetWidgetUrl($rptAuth,'learnerRegistration',$learnersWidgetSettings);

    $dateRelavance = $rptService->GetReportageDate();


    echo "<div class='meta-box-sortables'>";
    echo "<div class='reportageWrapper postbox'>";
    echo "<div title='". __("Click to toggle","scormcloud"). "' class='handlediv'><br></div><h3 class='hndle'>". __("Overall Reportage Summary","scormcloud");
    echo "</h3>";
    echo "<div class='inside'>";
    echo "<span class='dateRelevance'>". __("Data current as of ","scormcloud")."<span class='localizeRecentDate' utcdate='".date("d M Y H:i:s", strtotime($dateRelavance))."'></span></span>";
    echo '<table class="reportageTable"><tr class="summary"><td colspan="2">';
    echo '<div id="TotalSummary">'. __("Loading Summary...","scormcloud").'</div>';
    echo '<br></td></tr>';
    echo '<tr class="details">';
    // Courses Detail Widget
    echo '<td class="wp_details"><div id="CourseListDiv" class="wp_details_div">'. __("Loading All Courses...","scormcloud").'</div>';
    echo '</td>';
    //Learners Detail Widget
    echo '<td class="wp_details"><div id="LearnersListDiv" class="wp_details_div">'. __("Loading All Learners...","scormcloud").'</div>';
    echo '</td></tr></table>';
    //Load 'em Up...
    echo '<script type="text/javascript">';
    echo 'jQuery(document).ready(function(){';
    echo '	loadScript("'.$summaryUrl.'");';
    echo '	loadScript("'.$coursesUrl.'");';
    echo '	loadScript("'.$learnersUrl.'");';

    echo '});';

    echo '</script>';
    echo '</div></div></div>';//reportage wrapper
}




echo '</div>';//overall page wrapper

?>

