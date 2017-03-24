<?php

if (defined('ABSPATH'))
require_once(ABSPATH . 'wp-load.php');
else
require_once('../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');

require_once(SCORMCLOUD_BASE.'scormcloudplugin.php');
require_once(SCORMCLOUD_BASE.'db/scormclouddatabase.php');

global $wpdb;

$ScormService = ScormCloudPlugin::get_cloud_service();
try {
    $isValidAccount = $ScormService->isValidAccount();
} catch (Exception $e) {
    $isValidAccount = false;
}

if (isset($_GET['inviteid'])){
    include('trainingdetails.php');
} else {

    $regsRemaining = ScormCloudPlugin::remaining_registrations();


    ?>
<div class="scormcloud-admin-page trainings">

<h2><?php _e("SCORM Cloud Training","scormcloud"); ?></h2>
    <?php
    if ($isValidAccount){
        if ($regsRemaining < 1){
            echo "<div>
            <h3>".__("The maximum number of registrations for this account has been reached.  Visit the <a href='https://cloud.scorm.com'>SCORM Cloud site</a> to upgrade your account.","scormcloud")."</h3>
        </div>";
        } else {

            ?>


<div class='meta-box-sortables'>
<div class='reportageWrapper postbox '>
<div title='<?php _e("Click to toggle","scormcloud"); ?>'
	class='handlediv'><br>
</div>
<h3 class='hndle'><span><?php _e("Quick Create Training","scormcloud"); ?></span></h3>
<div class='inside'>


<div id="NewRegistrationForm">
<h4><?php _e("Select a course:","scormcloud"); ?></h4>
<select class="courseSelector">

<?php
$coursesFilter = (ScormCloudPlugin::is_network_managed() && get_site_option('scormcloud_sharecourses') !== '1') ? $GLOBALS['blog_id']."-.*" : null ;
$ScormService = ScormCloudPlugin::get_cloud_service();
echo "<option value='-1'></option>";
$courseService = $ScormService->getCourseService();
$allResults = $courseService->GetCourseList($coursesFilter);
foreach($allResults as $course){
    echo "<option value='".$course->getCourseId()."'>".$course->getTitle()."</option>";
}

?>
</select>

<h4><?php _e("Select learner(s):","scormcloud"); ?></h4>
<table class="learnerSelection">
	<tr>
		<td>
		<div><input type='radio' name='learnerPopulationType' value='allUsers' /><?php _e("All Users","scormcloud"); ?></div>

		</td>
		<td>
		<div><input type='radio' name='learnerPopulationType'
			value='selectUsers' /><?php _e("Select User(s)","scormcloud"); ?></div>
		<ul class="userHolder selectionHolder"></ul>
		<?php
		$userArgs = array('show_option_none' => ' ',
                                    'orderby'          => 'display_name',
                                    'order'            => 'ASC',
                                    'multi'            => 0,
                                    'show'             => 'display_name',
                                    'echo'             => 1,
                                    'selected'         => -1,
                                    'class'            =>  'userSelector');
		wp_dropdown_users($userArgs);

		?></td>
		<td class='lastCol'>
		<div><input type='radio' name='learnerPopulationType'
			value='selectRoles' /><?php _e("Select Role(s)","scormcloud"); ?></div>
		<ul class="roleHolder selectionHolder"></ul>
		<select class="roleSelector">
			<option selected value='-1'></option>
			<?php
			$roleArgs=array();
			wp_dropdown_roles();

			?>
		</select></td>

	</tr>
</table>

<p><input name="sendemail" type="checkbox" />Send invitation email to selected user(s)</p>

<p><input id="btnAddRegistration" type="button"
	value="<?php _e("Create Training","scormcloud"); ?>" /><span
	class='createTrainingMessage'>message</span></p>
</div>


</div>
</div>
<script language="javascript">

function scormcloud_showCreateTrainingMessage(msg,msgclass,hide){
    
    jQuery('#NewRegistrationForm span.createTrainingMessage').addClass(msgclass).text(msg).show();
    if (hide){
        jQuery('#NewRegistrationForm span.createTrainingMessage').delay(5000).fadeOut('slow').queue(
            function(){
                jQuery(this).removeClass(msgclass);
                jQuery(this).dequeue();
            });
    }
    
}

jQuery('select.userSelector').change(function(){
    jQuery('input[name="learnerPopulationType"][value="selectUsers"]').click();
    var selectedUser = jQuery('.userSelector option:selected');
    if (selectedUser.val() !=  '-1'){
        jQuery('ul.userHolder').append('<li userid="' + selectedUser.attr('value') + '"><a userid="' + selectedUser.attr('value') + '">X</a>' + selectedUser.text() + '</li>');
        selectedUser.remove();
        jQuery('ul.userHolder li a[userid="' + selectedUser.attr('value') + '"]').click(function(){
            jQuery('ul.userHolder li[userid="' + selectedUser.attr('value') + '"]').remove();
            jQuery('.userSelector').append('<option value="' + selectedUser.attr('value') + '">' + selectedUser.text() + '</option>');
        });
    }
        
});

jQuery('select.roleSelector').change(function(){
    jQuery('input[name="learnerPopulationType"][value="selectRoles"]').click();
    var selectedRole = jQuery('.roleSelector option:selected');
    if (selectedRole.val() !=  '-1'){
        jQuery('ul.roleHolder').append('<li rolename="' + selectedRole.attr('value') + '"><a rolename="' + selectedRole.attr('value') + '">X</a>' + selectedRole.text() + '</li>');
        selectedRole.remove();
        jQuery('ul.roleHolder li a[rolename="' + selectedRole.attr('value') + '"]').click(function(){
            jQuery('ul.roleHolder li[rolename="' + selectedRole.attr('value') + '"]').remove();
            jQuery('.roleSelector').append('<option value="' + selectedRole.attr('value') + '">' + selectedRole.text() + '</option>');
        });
    }
        
});


jQuery("#btnAddRegistration").click(function(){
	var courseId;
    var courseTitle;
    var allUsers;
    var userRoles;
	var userIds;
	jQuery(".courseSelector option:selected").each(function () {
		courseId = jQuery(this).attr("value");
        courseTitle = jQuery(this).text();
		});
	if (courseId == '-1'){
        scormcloud_showCreateTrainingMessage("<?php _e("Please select a course.","scormcloud"); ?>","errorMsg", true);
        return;
    }
    
    var userPopulation = jQuery('input[name="learnerPopulationType"]:checked').val();
    if (userPopulation == undefined){
        scormcloud_showCreateTrainingMessage("<?php _e("Please select learners.","scormcloud"); ?>","errorMsg", true);
        return;
    }
    
    var dataString = "action=addregistration" +
                "&courseid=" + courseId +
                "&coursetitle=" + courseTitle;
    
    if (userPopulation == 'allUsers'){
        dataString += "&allusers=1";
    } else if (userPopulation == 'selectUsers'){
        dataString += "&userids=" + jQuery('ul.userHolder li').map(function(){return jQuery(this).attr('userid')}).get().join(',');
    } else if (userPopulation == 'selectRoles'){
        dataString += "&roles=" + jQuery('ul.roleHolder li').map(function(){return jQuery(this).attr('rolename')}).get().join(',');
    }

    if (jQuery('input[name="sendemail"]').is(':checked')) {
    	dataString += '&sendemail=on';
    }
    
    //alert(dataString);
    
    scormcloud_showCreateTrainingMessage("<?php _e("Creating Trainings...","scormcloud"); ?>","", false);
	
    jQuery.ajax({
        type: "POST",
        url: "<?php echo site_url() . '/wp-content/plugins/scormcloud/ajax.php'; ?>",
        data: 	dataString,
        success: function(rspStr){
            if (rspStr == 'success'){
                window.location = window.location;    
            } else {
                scormcloud_showCreateTrainingMessage(rspStr,"errorMsg", false);
            }
        }
    });
    
});
   
</script> <?php
        }
    } else {
        echo "<div>
            <h3>".__("Please configure your SCORM Cloud settings to add registrations.","scormcloud")."</h3>
        </div>";
        echo '<div class="settingsPageLink"><a href="'.site_url().'/wp-admin/admin.php?page=scormcloudsettings"
				title="'.__("Click here to configure your SCORM Cloud plugin.","scormcloud").'">'.__("Click Here to go to the settings page.","scormcloud").'</a></div>';

    }
    ?>


<h3><?php _e("SCORM Cloud Training History","scormcloud"); ?></h3>

    <?php

    $invTable = ScormCloudDatabase::get_invitations_table();
    $regTable = ScormCloudDatabase::get_registrations_table();
    $query = $wpdb->prepare('SELECT inv.*, count(reg.reg_id) as reg_count FROM '.$invTable.' inv
						 LEFT OUTER JOIN '.$regTable.' reg ON inv.invite_id = reg.invite_id
						 WHERE inv.blog_id = %s AND inv.app_id = %s GROUP BY inv.invite_id ORDER BY inv.create_date DESC', array($GLOBALS['blog_id'], ScormCloudPlugin::get_wp_option('scormcloud_appid')));
    $invites = $wpdb->get_results($query, OBJECT);

    echo '<table class="widefat" cellspacing="0" id="InvitationListTable" >';
    echo '<thead>';
    echo '<tr class="thead"><th class="manage-column">'.__("Course Title","scormcloud").'</th>
        <th class="manage-column">'.__("Post Title","scormcloud").'</th>
        <th class="manage-column">'.__("Create/Publish Date","scormcloud").'</th>
        <th class="manage-column">'.__("Learners","scormcloud").'</th>
        <th class="manage-column">&nbsp;</th>
        <th class="manage-column">&nbsp;</th></tr>';
    echo '</thead>';
    foreach ($invites as $invite)
    {

        echo "<tr class='regRow' key='".$invite->invite_id."'>";
        echo "<td class='title'><a title='".__("Click to view details of this invitation.","scormcloud")."' href='".site_url()."/wp-admin/admin.php?page=scormcloud/manage_training&inviteid=".$invite->invite_id."'>".__($invite->course_title)."</a></td>";

        if ($invite->post_id == "__direct_invite__"){
            echo "<td>".__("User Invitation","scormcloud")."</td>";
            echo "<td><span class='localizeDate' format= 'mmmm d, yyyy h:MM TT' utcdate='".date("d M Y H:i:s", strtotime($invite->create_date))."'>".$invite->create_date."</span></td>";
        } elseif ($invite->post_id == "__catalog_widget__"){
            echo "<td>".__("Catalog Widget","scormcloud")."</td>";
            echo "<td><span class='localizeDate' format= 'mmmm d, yyyy h:MM TT' utcdate='".date("d M Y H:i:s", strtotime($invite->create_date))."'>".$invite->create_date."</span></td>";
        } else {
            $postInfo = get_post($invite->post_id);
            if ($postInfo != null){
                echo "<td><a title='".__('Click to edit this post.',"scormcloud")."' href='".site_url()."/wp-admin/post.php?action=edit&post=".$invite->post_id."'>".$postInfo->post_title."</a></td>";
                echo "<td><span class='localizeDate' format= 'mmmm d, yyyy h:MM TT' utcdate='".date("d M Y H:i:s", strtotime($postInfo->post_date))."'>".$postInfo->post_date."</span></td>";
            } else {
                echo "<td>".__("","scormcloud")."</td>";
                echo "<td><span class='localizeDate' format= 'mmmm d, yyyy h:MM TT' utcdate='".date("d M Y H:i:s", strtotime($invite->create_date))."'>".$invite->create_date."</span></td>";
                 
            }
        }
        if ($invite->reg_count > 0){
            if ($isValidAccount){
                echo "<td><a href='#' key='".$invite->invite_id."' class='viewRegsLink' >".$invite->reg_count." ".(($invite->reg_count != 1 ? __("Learners","scormcloud") : __("Learner","scormcloud")).__("(view)","scormcloud"))."</a></td>";
                echo "<td><a href='#' key='".$invite->invite_id."' class='viewReportageLink' >".__("View Results Report","scormcloud")."</a></td>";
            } else {
                echo "<td colspan='2'>".$invite->reg_count." ".($invite->reg_count != 1 ? __("Learners","scormcloud") : __("Learner","scormcloud"))."</td>";

            }
        } else {
            echo "<td colspan='2'>0 ".__("Learners","scormcloud")."</td>";
        }
        echo "<td><span class='activeText' key='".$invite->invite_id."'>".($invite->active == 1 ? __("Active","scormcloud") : __("Inactive","scormcloud"))."</span>";
        if ($invite->active != 2){
            echo "(<a href='#' key='".$invite->invite_id."' class='activateLink' active='".$invite->active."' >".($invite->active == 1 ? __("click to deactivate","scormcloud") : __("click to activate","scormcloud"))."</a>)";
        }
        echo "</td>";


        //$user_info = get_userdata($reg->user_id);
        //echo $user_info->display_name;
        echo '</tr>';
    }
    echo '</table>';
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
    


jQuery('.viewRegsLink').toggle(function(){
    var invId = jQuery(this).attr('key');
    //var linkObj = jQuery(this);
    if (jQuery('tr.regList[key="'+ invId + '"]').length < 1){
        jQuery.ajax({
            type: "POST",
            url: "<?php echo site_url() . '/wp-content/plugins/scormcloud/ajax.php'; ?>",
            data: 	"action=getRegistrations" +
                    "&inviteid=" + invId,
            success: function(data){
                var newRow = "<tr class='regList' key='"+invId+"' ><td class='regList' colspan='6'>"+data+"</td></tr>";
                jQuery('tr.regRow[key="'+ invId + '"]').addClass('active').after(newRow);
                
            }
        });
    } else {
        jQuery('tr.regRow[key="'+ invId + '"]').addClass('active');
        jQuery('tr.regList[key="'+ invId + '"]').fadeIn();
    }
    return false;
},
function(){
    var invId = jQuery(this).attr('key');
    jQuery('tr.regRow[key="'+ invId + '"]').removeClass('active');
    jQuery('tr.regList[key="'+ invId + '"]').fadeOut();
    
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



</script></div>

    <?php  } ?>