<?php

require_once(SCORMCLOUD_BASE.'scormcloudplugin.php');
require_once(SCORMCLOUD_BASE.'db/scormclouddatabase.php');

$inviteId = $_GET['inviteid'];

$invTable = ScormCloudDatabase::get_invitations_table();
$regTable = ScormCloudDatabase::get_registrations_table();
$query = $wpdb->prepare('SELECT inv.*, count(reg.reg_id) as reg_count FROM '.$invTable.' inv
                         LEFT OUTER JOIN '.$regTable.' reg ON inv.invite_id = reg.invite_id
                         WHERE inv.invite_id = %s AND inv.app_id = %s GROUP BY inv.invite_id', array($inviteId, ScormCloudPlugin::get_wp_option('scormcloud_appid')));
$invite = $wpdb->get_row($query, OBJECT);


?>
<div class="scormcloud-admin-page trainingDetail"><a class='backLink'
	href='<?php echo site_url()."/wp-admin/admin.php?page=scormcloud/manage_training"; ?>'><?php _e("Go back to all trainings","scormcloud"); ?></a>

<h2><?php echo __("Training Details for","scormcloud").' "'.$invite->course_title; ?>"</h2>
<div class="invitationStatus"><?php
echo __("This training is currently","scormcloud")." <span class='activeText' key='".$inviteId."'>".($invite->active == 1 ? __("Active","scormcloud") : __("Inactive","scormcloud"))."</span>";
if ($invite->active != 2){
    echo "(<a href='#' key='".$inviteId."' class='activateLink' active='".$invite->active."' >".($invite->active == 1 ? __("click to deactivate","scormcloud") : __("click to activate","scormcloud"))."</a>)";
}
?></div>




<?php if ($invite->post_id != "__direct_invite__" && $invite->post_id != "__catalog_widget__"){ ?>
<div class='meta-box-sortables'>
<div
	class='reportageWrapper postbox <?php echo ($is_valid_account ? "closed" : ""); ?>'>
<div title='<?php _e("Click to toggle","scormcloud"); ?>'
	class='handlediv'><br>
</div>
<h3 class='hndle'><span><?php _e("Invitation Details","scormcloud"); ?></span></h3>
<div class='inside'>

<table class='inviteDetails'>
	<tr>
		<td class='inviteDetails'>

		<table class='inviteForm'>
			<tr>
				<td class="label"><?php _e("Training Header Text:","scormcloud"); ?></td>
				<td><input type="text" name="trainingHeaderTxt"
					value="<?php echo $invite->header; ?>" /></td>
			</tr>
			<tr>
				<td class="label"><?php _e("Training Description:","scormcloud"); ?></td>
				<td><textarea type="text" name="trainingDesc"><?php echo $invite->description; ?></textarea></td>
			</tr>
			<tr>
				<td colspan='2'><input type="checkbox"
				<?php echo ($invite->require_login == 1 ? "checked" : ""); ?>
					name="trainingRequireLogin" /><?php _e("Require that learners be authenticated users.","scormcloud"); ?></td>
			</tr>
			<tr>
				<td colspan='2'><input type="checkbox"
				<?php echo ($invite->show_course_info == 1 ? "checked" : ""); ?>
					name="showCourseInfo" /><?php _e("Show the course title and description.","scormcloud"); ?></td>
			</tr>

		</table>

		<input type="button" class="updateInvitation button"
			name="generateTrainingTag"
			value="<?php _e("Update This Training","scormcloud"); ?>" /> <span
			class='updateMessage'></span></td>
		<td class='inviteDetails'>



		<div class="previewDiv">
		<div class="scormCloudInvitation">
		<h4><?php echo $invite->header; ?></h4>
		<p class="description"><?php echo $invite->description; ?></p>
		<div class="courseInfo">
		<div class="title"></div>
		<div class="desc">This will be the metadata description for your
		course (if it exists). Also, the displayed duration will render if it
		exists in the metadata.</div>
		<div class="duration">Duration: 10 minutes</div>
		</div>
		<p class="inputs">My name is <input disabled name="scormcloudfname"
			placeholder="First Name" type="text"> <input name="scormcloudlname"
			disabled placeholder="Last Name" type="text"> and my email is <input
			name="scormcloudemail" disabled placeholder="Email" type="text"> .</p>
		<input type="button" class="button"
			value="<?php _e("Start Training","scormcloud"); ?>"
			onclick="return false;" name="launch"></div>
		</div>

		</td>
	</tr>
</table>



</div>
</div>
<script type="text/javascript" charset="utf-8">
	var $j = jQuery.noConflict();
    $j(document).ready(function(){
        
        <?php
        echo $invite->require_login == 1 ? "" : "\$j('.scormcloud-admin-page.trainingDetail .previewDiv p.inputs').toggle();";
        echo $invite->show_course_info == 1 ? "" : "\$j('.scormcloud-admin-page.trainingDetail .previewDiv div.courseInfo').toggle();";
        ?>
        
        $j('.scormcloud-admin-page.trainingDetail table.inviteForm input[name="trainingHeaderTxt"]').change(function(){
            $j('.scormcloud-admin-page.trainingDetail .previewDiv h4').text($j(this).val());
        });
        $j('.scormcloud-admin-page.trainingDetail table.inviteForm textarea[name="trainingDesc"]').change(function(){
            $j('.scormcloud-admin-page.trainingDetail p.description').text($j(this).val());
        });
        $j('.scormcloud-admin-page.trainingDetail table.inviteForm input[name="trainingRequireLogin"]').change(function(){
            $j('.scormcloud-admin-page.trainingDetail .previewDiv p.inputs').toggle();
        });
        $j('.scormcloud-admin-page.trainingDetail table.inviteForm input[name="showCourseInfo"]').change(function(){
            $j('.scormcloud-admin-page.trainingDetail .previewDiv div.courseInfo').toggle();
        });
        
        $j(".scormcloud-admin-page.trainingDetail .updateInvitation").click(function(e) {
            
            $j(".scormcloud-admin-page.trainingDetail .inviteDetails span.updateMessage").text('Saving Changes...').fadeIn('fast');
            
            var header = $j('.scormcloud-admin-page.trainingDetail table.inviteForm input[name="trainingHeaderTxt"]').attr('value');
            var description = $j('.scormcloud-admin-page.trainingDetail table.inviteForm textarea[name="trainingDesc"]').attr('value');
            var requirelogin = $j('.scormcloud-admin-page.trainingDetail table.inviteForm input[name="trainingRequireLogin"]:checked').length;
            var showcourseinfo = $j('.scormcloud-admin-page.trainingDetail table.inviteForm input[name="showCourseInfo"]:checked').length;
            
            $j.ajax({
			type: "POST",
			url: "<?php echo site_url() . '/wp-content/plugins/scormcloud/ajax.php'; ?>",
			data: 	"action=updatePostInvite" +
                    "&inviteid=<?php echo $inviteId; ?>" +
					"&header=" + header +
                    "&description=" + description +
                    "&requirelogin=" + requirelogin +
                    "&showcourseinfo=" + showcourseinfo,
			success: function(html){
			    $j(".scormcloud-admin-page.trainingDetail .inviteDetails span.updateMessage").text('Updates Saved').delay(3000).fadeOut('slow');
			}
            });
            
            
        });
       
       
    });

    
        
		
</script> <?php }
if ($is_valid_account){
    ?>

<div class='meta-box-sortables'>
<div class='reportageWrapper postbox '>
<div class='handlediv'><br>
</div>
<h3 class='hndle'><span><?php _e("Training Summary","scormcloud"); ?></span><a
	href='javascript:void(0);' key='<?php echo $invite->invite_id; ?>'
	class='viewReportageLink reportageLink'><?php _e("View Full Results Report","scormcloud"); ?></a></h3>
<div class='inside'><?php

echo "<script type='text/javascript' src='http://cloud.scorm.com/Reportage/scripts/reportage.combined.js'></script>";
echo "<link rel='stylesheet' href='http://cloud.scorm.com/Reportage/css/reportage.combined.css' type='text/css' media='screen' />";


//Check for some defaults to set the form up
$rptService = $cloud_service->getReportingService();
$rptAuth = $rptService->GetReportageAuth('FREENAV',true);

//  AppId Summary Report
$dateRangeStart = '2009-01-01';
$dateRangeEnd = date("Y-m-d");
$dateOptions = new DateRangeSettings(null,$dateRangeStart,$dateRangeEnd,null);

$tagSettings = new TagSettings();
$tagSettings->addTag('registration',$inviteId);

$sumWidgetSettings = new WidgetSettings($dateOptions,$tagSettings);
$sumWidgetSettings->setShowTitle(true);
$sumWidgetSettings->setScriptBased(true);
$sumWidgetSettings->setEmbedded(true);
$sumWidgetSettings->setVertical(false);
$sumWidgetSettings->setDivname('TotalSummary');

$dateRelavance = $rptService->GetReportageDate();

$summaryUrl = $rptService->GetWidgetUrl($rptAuth,'allSummary',$sumWidgetSettings);
echo "<span class='dateRelevance'>".__("Data current as of","scormcloud")." <span class='localizeRecentDate' utcdate='".date("d M Y H:i:s", strtotime($dateRelavance))."'></span></span>";
echo '<table class="reportageTable"><tr class="summary"><td colspan="2">';
echo '<div id="TotalSummary">'.__("Loading Training Summary...","scormcloud").'</div>';
echo '<br></td></tr></table>'

?> <script type="text/javascript">
	jQuery(document).ready(function(){
        loadScript("<?php echo $summaryUrl; ?>");
	});
	
</script></div>
</div>



<?php
$rServiceUrl = $rptService->GetReportageServiceUrl();
$reportageUrl = $rServiceUrl.'Reportage/reportage.php?appId=' . $cloud_service->getAppId() . "&viewall=learners&registrationTags=$inviteId|_all";
$reportageViewAllUrl = $rptService->GetReportUrl($rptAuth, $reportageUrl);
?>

<h3><?php _e("Training History","scormcloud"); ?> <a
	class='reportageLink' href='<?php echo $reportageViewAllUrl; ?>'><?php _e("View All Learners in Reportage","scormcloud"); ?></a></h3>

<?php

$query = $wpdb->prepare('SELECT * FROM '.$regTable.' WHERE invite_id = %s ORDER BY update_date DESC', array($inviteId));
$inviteRegs = $wpdb->get_results($query, OBJECT);

$regService = $cloud_service->getRegistrationService();
$regsXMLStr = $regService->GetRegistrationListResults($invite->course_id,null,0);

$regsXML = simplexml_load_string($regsXMLStr);
$regList = $regsXML->registrationlist;

$returnHTML = "";

$returnHTML .= '<table class="widefat" cellspacing="0" id="InvitationListTable" >';
$returnHTML .= '<thead>';
$returnHTML .= '<tr class="thead"><th class="manage-column">'.__("User","scormcloud").'</th>
    <th class="manage-column">'.__("Completion","scormcloud").'</th>
    <th class="manage-column">'.__("Success","scormcloud").'</th>
    <th class="manage-column">'.__("Score","scormcloud").'</th>
    <th class="manage-column">'.__("Time","scormcloud").'</th>
    <th class="manage-column"></th></tr></thead>';

foreach ($inviteRegs as $inviteReg){
    $regResult = $regList->xpath('//registration[@id="'.$inviteReg->reg_id.'"]');
    if (count($regResult)>0){
      $regReport = $regResult[0]->registrationreport;
    
      $returnHTML .= "<tr key='".$inviteReg->reg_id."'>";
      if ($userId = $inviteReg->user_id){
        $wpUser = get_userdata($userId);
          $returnHTML .= "<td>".$wpUser->display_name."</td>";
      } else {
        $returnHTML .= "<td>".$inviteReg->user_email."</td>";
      }


      $returnHTML .= "<td class='".$regReport->complete."'>".__($regReport->complete)."</td>";
      $returnHTML .= "<td class='".$regReport->success."'>".__($regReport->success)."</td>";
      $score = $regReport->score;
      $returnHTML .= "<td>".($score == "unknown" ? "-" : $score."%")."</td>";
      $seconds = $regReport->totaltime;
      $returnHTML .= "<td>".floor($seconds / 60)."min ".($seconds % 60)."sec</td>";
      $returnHTML .= "<td><a href='javascript:void(0);' class='viewRegDetails' onclick='Scormcloud_loadRegReport(\"$inviteId\",\"".$inviteReg->reg_id."\"); return false;' key='".$inviteReg->invite_id."'>".__("View Details","scormcloud")."</tr>";
    }
}

$returnHTML .= '</table>';

echo $returnHTML;
?> <script language="javascript">



jQuery(".viewReportageLink").click(function(){
	var invId = jQuery(this).attr('key');;
     
    jQuery.ajax({
        type: "POST",
        url: "<?php echo site_url() . '/wp-content/plugins/scormcloud/ajax.php'; ?>",
        data: 	"action=getInviteReportUrl" +
                "&inviteid=" + invId,
        success: function(url){
            //alert(url);
            window.open(url);
            
        }
    });

    return false;
});
    


jQuery('.activateLink').click(function(){
    var invId = jQuery(this).attr('key');
    var wasActive = (jQuery(this).attr('active') == 1);
    //var linkObj = jQuery(this);
    
    jQuery.ajax({
        type: "POST",
        url: "<?php echo site_url() . '/wp-content/plugins/scormcloud/ajax.php'; ?>",
        data: 	"action=setactive" +
                "&inviteid=" + invId +
                "&active=" + (wasActive ? '0' : '1'),
        success: function(data){
            //alert(data);
            
        }
    });
    if (wasActive){
        jQuery('.activateLink[key="'+ invId + '"]').text('click to activate');
        jQuery('.activeText[key="'+ invId + '"]').text('Inactive');
		jQuery(this).attr('active',0);
    } else {
        jQuery('.activeText[key="'+ invId + '"]').text('Active');
        jQuery('.activateLink[key="'+ invId + '"]').text('click to deactivate');
        jQuery(this).attr('active',1);
    }
    
    return false;
});

function Scormcloud_loadRegReport(invId,regId){
                    
    jQuery.ajax({
        type: "POST",
        url: "<?php echo site_url() . '/wp-content/plugins/scormcloud/ajax.php'; ?>",
        data: 	"action=getRegReportUrl" +
                "&inviteid=" + invId +
                "&regid=" + regId,
        success: function(url){
            //alert(url);
            window.open(url);
            
        }
    });
    return true;
}



</script> <?php
} else{
    echo "<div>
            <h2>" . __("Please configure your SCORM Cloud settings to see training results.","scormcloud") . "</h2>
        </div>";
    echo '<div class="settingsPageLink"><a href="'.site_url().'/wp-admin/admin.php?page=scormcloudsettings"
				title="'.__("Click here to configure your SCORM Cloud plugin.","scormcloud").'">' . __("Click Here to go to the settings page.","scormcloud") . '</a></div>';

}

?></div>