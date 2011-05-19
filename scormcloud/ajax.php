<?php
if ( defined('ABSPATH') )
	require_once(ABSPATH . 'wp-load.php');
else
	require_once('../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');

require_once('scormcloud.wp.php');
require_once('scormcloud.deprecatedFunctions.php');

$ScormService = scormcloud_getScormEngineService();
$action = $_POST['action'];

switch($action)
{	
	case 'addregistration':
        
        $inviteId = uniqid();
        $appId = get_option('scormcloud_appid');
        $postId = "__direct_invite__";
        $courseId = $_POST['courseid'];
        $courseTitle = $_POST['coursetitle'];
        
        $strUserIds = isset($_POST['userids']) ? $_POST['userids'] : null;
        $allUsers = isset($_POST['allusers']) ? $_POST['allusers'] : null;
        $roles = isset($_POST['roles']) ? $_POST['roles'] : null;
        
        $header = "";
        $description = "";
        
        $require_login = 1;
        $show_course_info = 0;
        
        $wpdb->query($wpdb->prepare( "
		    INSERT INTO ".scormcloud_getDBPrefix()."scormcloudinvitations 
				(invite_id, blog_id, app_id, post_id, course_id, course_title, header, description, require_login, show_course_info)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %d, %d)", 
			        $inviteId, $GLOBALS['blog_id'], $appId, $postId, $courseId, $courseTitle, $header, $description, $require_login, $show_course_info));
        
        //create the cloud registration(s)
        $users = array();
        
        if (isset($allUsers)){
            $users = get_users();
            
        } elseif (isset($strUserIds)){
                $userIds = explode(",",$strUserIds);
				$wp_user_search = new WP_User_Query(array('include' => $userIds));
                $users = $wp_user_search->get_results();
                
        } elseif (isset($roles)) {
            $roleArray = explode(",",$roles);
			foreach ($roleArray as $thisRole){
                $wp_user_search = new WP_User_Query(array('role' => $thisRole));
                $users = array_merge($users,$wp_user_search->get_results());
            }
			$users = array_unique($users,SORT_REGULAR);
        }
        
        $responseString = 'success';
        foreach($users as $user){
			$userData = get_userdata($user->ID);
            if (!($user_first_name = $userData->user_firstname) || strlen($user_first_name) < 1){
                $user_first_name = $userData->display_name;
            }
            if (!($user_last_name = $userData->user_lastname) || strlen($user_last_name) < 1){
                $user_last_name = $userData->display_name;
            }
            
            
            $regid = $inviteId."-".uniqid();
            $regService = $ScormService->getRegistrationService();
            $response = $regService->CreateRegistration($regid, $courseId, $userData->user_email, $user_first_name, $user_last_name, $userData->user_email);
            
            $xml = simplexml_load_string($response);
            if (isset($xml->success)){   
                $wpdb->query($wpdb->prepare( "
                    INSERT INTO ".scormcloud_getDBPrefix()."scormcloudinvitationregs 
                        (invite_id, reg_id, user_id, user_email)
                        VALUES (%s, %s, %d, %s)", 
                            $inviteId, $regid, $userData->ID, $userData->user_email));
                
            } else if ($xml->err['code'] == '4') {
                $responseString = 'There was a problem creating a new training. The maximum number of registrations for this account has been reached.';
            } else {
                $responseString = 'There was a problem creating a new training. '.$xml->err['msg'];
            }
        
        }
        
        echo $responseString;
	
		break;
    case 'addPostInvite':
	
        $inviteId = uniqid();
        $appId = get_option('scormcloud_appid');
        $courseId = $_POST['courseid'];
        $courseTitle = $_POST['coursetitle'];
        $header = $_POST['header'];
        $description = $_POST['description'];
        $require_login = $_POST['requirelogin'];
        $show_course_info = $_POST['showcourseinfo'];
        
        $wpdb->query($wpdb->prepare( "
		    INSERT INTO ".scormcloud_getDBPrefix()."scormcloudinvitations 
				(invite_id, blog_id, app_id, course_id, course_title, header, description, require_login, show_course_info)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %d, %d)", 
			        $inviteId, $GLOBALS['blog_id'], $appId, $courseId, $courseTitle, $header, $description, (int)$require_login, (int)$show_course_info));
        
        
        echo $inviteId;
	
	
        break;
    
    case 'updatePostInvite':
	
        $inviteId = $_POST['inviteid'];
        $header = $_POST['header'];
        $description = $_POST['description'];
        $require_login = $_POST['requirelogin'];
        $show_course_info = $_POST['showcourseinfo'];
        
        $querystr = "UPDATE ".scormcloud_getDBPrefix()."scormcloudinvitations SET
            header = '$header',
            description = '$description',
            require_login = ".(int)$require_login.",
            show_course_info = ".(int)$show_course_info."
            WHERE invite_id = '$inviteId'";
        $wpdb->query($querystr);
        
        break;
    
    case "addAnonRegGetLaunchUrl":
        $user_first_name = $_POST['fname'];
        $user_last_name = $_POST['lname'];
        $user_email = $_POST['email'];
        $inviteId = $_POST['inviteid'];
        $returnUrl = $_POST['returnurl'];
        
        $querystr = "SELECT * FROM ".scormcloud_getDBPrefix()."scormcloudinvitations WHERE invite_id = '$inviteId'";
        $invites = $wpdb->get_results($querystr, OBJECT);
        $invite = $invites[0];
        
        $appId = $invite->app_id;
        $courseId = $invite->course_id;
        
        $courseTags = '';
        if ($invite->post_id != "__direct_invite__" && $invite->post_id != '__catalog_widget__'){
            $postCategories = get_the_category($invite->post_id);
            if (is_array($postCategories)){
                foreach($postCategories as $category) { 
                    $courseTags .= ",".$category->cat_name;
                }
            }
            $postTags = get_the_tags($invite->post_id);
            if (is_array($postTags)){
                foreach($postTags as $tag) { 
                    $courseTags .= ",".$tag->name;
                }
            }
        }
            
        
        
        $regTags = $GLOBALS['blog_id'].','.$inviteId.$courseTags;
        
        if (strlen($courseTags) > 0){
            $courseTags = substr($courseTags,1);
        }
        
        $learnerTags = "anonymous";
        
        $regService = $ScormService->getRegistrationService();
        
        $querystr = "SELECT reg_id FROM ".scormcloud_getDBPrefix()."scormcloudinvitationregs WHERE invite_id = '$inviteId' AND user_email = '$user_email'";
        $inviteRegs = $wpdb->get_results($querystr, OBJECT);
        if (count($inviteRegs) > 0) {
            $inviteReg = $inviteRegs[0];
            $regid = (string)$inviteReg->reg_id;
        } else {
            $regid = $inviteId."-".uniqid();
                
            //create the cloud registration
            $regService->CreateRegistration($regid, $courseId, $user_email, $user_first_name, $user_last_name,$user_email);
            
            $wpdb->query($wpdb->prepare( "
                INSERT INTO ".scormcloud_getDBPrefix()."scormcloudinvitationregs 
                    (invite_id, reg_id, user_email)
                    VALUES (%s, %s, %s)", 
                        $inviteId, $regid, $user_email));
        
        }
        
        
        
        if (function_exists("bp_activity_add")){
            global $bp;
            
            if ($invite->post_id != "__direct_invite__" && $invite->post_id != '__catalog_widget__'){
                $thisPost = get_post($invite->post_id);
                $post_permalink = get_permalink( $thisPost->ID );
                $actionStr = sprintf('%s launched course "%s" from post %s', $user_first_name.' '.$user_last_name, $invite->course_title, '<a href="' . $post_permalink . '">' . $thisPost->post_title . '</a>');
            } else {
                $actionStr = sprintf($user_first_name.' '.$user_last_name, $from_user_link, $invite->course_title);
            }
            //error_log('logged in user: '.$bp->loggedin_user->id);
            
            $activityArgs = array(
                'action' => $actionStr, // The activity action - e.g. "Jon Doe posted an update"
                'content' => '', // Optional: The content of the activity item e.g. "BuddyPress is awesome guys!"
        
                'component' => 'scormcloud', // The name/ID of the component e.g. groups, profile, mycomponent
                'type' => 'training_launch', // The activity type e.g. activity_update, profile_updated
                'primary_link' => '', // Optional: The primary URL for this item in RSS feeds (defaults to activity permalink)
        
                'user_id' => false, // Optional: The user to record the activity for, can be false if this activity is not for a user.
                'item_id' => false, // Optional: The ID of the specific item being recorded, e.g. a blog_id
                'secondary_item_id' => false, // Optional: A second ID used to further filter e.g. a comment_id
                'recorded_time' => gmdate( "Y-m-d H:i:s" ), // The GMT time that this activity was recorded
                'hide_sitewide' => false // Should this be hidden on the sitewide activity stream?
            );
            $bpActivityId = bp_activity_add($activityArgs);
            error_log('Logging action: '.$actionStr.' Activity ID: '.$bpActivityId);
        } 
        $cssUrl = (scormcloud_isScormCloudNetworkManaged()) ? get_site_option('scormcloud_player_cssurl') :  get_option('scormcloud_player_cssurl');  
            
        echo $regService->GetLaunchUrl($regid,$returnUrl,$cssUrl,null,$courseTags,$learnerTags,$regTags);
        
        
        break;
       
    case "addUserRegGetLaunchUrl":
        $inviteId = $_POST['inviteid'];
        $returnUrl = $_POST['returnurl'];
        
        $regid = $inviteId."-".uniqid();
        
        global $current_user;
        global $wpdb;
        get_currentuserinfo();
        
        $user_email = $current_user->user_email;
        if (!($user_first_name = $current_user->user_firstname) || strlen($user_first_name) < 1){
            $user_first_name = $current_user->display_name;
        }
        if (!($user_last_name = $current_user->user_lastname) || strlen($user_last_name) < 1){
            $user_last_name = $current_user->display_name;
        }
        
        $querystr = "SELECT * FROM ".scormcloud_getDBPrefix()."scormcloudinvitations WHERE invite_id = '$inviteId'";
        $invites = $wpdb->get_results($querystr, OBJECT);
        $invite = $invites[0];
        
        $courseId = $invite->course_id;
        
        $courseTags = '';
        if ($invite->post_id != "__direct_invite__" && $invite->post_id != '__catalog_widget__'){
            $postCategories = get_the_category($invite->post_id);
            if (is_array($postCategories)){
                foreach($postCategories as $category) { 
                    $courseTags .= ",".$category->cat_name;
                }
            }
            $postTags = get_the_tags($invite->post_id);
            if (is_array($postTags)){
                foreach($postTags as $tag) { 
                    $courseTags .= ",".$tag->name;
                }
            }
            
            
        }
        
        $regTags = $GLOBALS['blog_id'].','.$inviteId.$courseTags;
        
        if (strlen($courseTags) > 0){
            $courseTags = substr($courseTags,1);
        }
        
        $learnerTags = isset($current_user->roles[0]) ? $current_user->roles[0] : "anonymous";
        
        //echo $regid.','. $courseId.','.  $user_email.','.  $user_first_name.','.  $user_last_name;
        //create the cloud registration
        $regService = $ScormService->getRegistrationService();
        $regService->CreateRegistration($regid, $courseId, $user_email, $user_first_name, $user_last_name,$user_email);
        
        $wpdb->query($wpdb->prepare( "
		    INSERT INTO ".scormcloud_getDBPrefix()."scormcloudinvitationregs 
				(invite_id, reg_id, user_id, user_email)
				VALUES (%s, %s, %d, %s)", 
			        $inviteId, $regid, $current_user->ID, $user_email));
        
        if (function_exists("bp_activity_add")){
            global $bp;
            
            $from_user_link = bp_core_get_userlink( $bp->loggedin_user->id );
            if ($invite->post_id != "__direct_invite__" && $invite->post_id != '__catalog_widget__'){
                $thisPost = get_post($invite->post_id);
                $post_permalink = get_permalink( $thisPost->ID );
                $actionStr = sprintf('%s launched course "%s" from post %s', $from_user_link, $invite->course_title, '<a href="' . $post_permalink . '">' . $thisPost->post_title . '</a>');
            } else {
            $actionStr = sprintf('%s launched course "%s"', $from_user_link, $invite->course_title);
            }
            //error_log('logged in user: '.$bp->loggedin_user->id);
            
            $activityArgs = array(
                'action' => $actionStr, // The activity action - e.g. "Jon Doe posted an update"
                'content' => '', // Optional: The content of the activity item e.g. "BuddyPress is awesome guys!"
        
                'component' => 'scormcloud', // The name/ID of the component e.g. groups, profile, mycomponent
                'type' => 'training_launch', // The activity type e.g. activity_update, profile_updated
                'primary_link' => '', // Optional: The primary URL for this item in RSS feeds (defaults to activity permalink)
        
                'user_id' => $bp->loggedin_user->id, // Optional: The user to record the activity for, can be false if this activity is not for a user.
                'item_id' => false, // Optional: The ID of the specific item being recorded, e.g. a blog_id
                'secondary_item_id' => false, // Optional: A second ID used to further filter e.g. a comment_id
                'recorded_time' => gmdate( "Y-m-d H:i:s" ), // The GMT time that this activity was recorded
                'hide_sitewide' => false // Should this be hidden on the sitewide activity stream?
            );
            $bpActivityId = bp_activity_add($activityArgs);
            error_log('Logging action: '.$actionStr.' Activity ID: '.$bpActivityId);
        } 
            
        $cssUrl = (scormcloud_isScormCloudNetworkManaged()) ? get_site_option('scormcloud_player_cssurl') :  get_option('scormcloud_player_cssurl');
        
        echo $regService->GetLaunchUrl($regid,$returnUrl,$cssUrl,null,$courseTags,$learnerTags,$regTags);
        
        
        break;
    
    case "getLaunchUrl":
        
        global $current_user;
        global $wpdb;
        get_currentuserinfo();
        
        $regid = $_POST['regid'];
        $returnUrl = $_POST['returnurl'];
        $widgetName = isset($_POST['widgetname']) ? $_POST['widgetname'] : null;
        
        $querystr = "SELECT invite_id FROM ".scormcloud_getDBPrefix()."scormcloudinvitationregs WHERE reg_id = '$regid'";
        $inviteRegs = $wpdb->get_results($querystr, OBJECT);
        $inviteReg = $inviteRegs[0];
        
        $regTags = $GLOBALS['blog_id'].','.(string)$inviteReg->invite_id;
        
		$learnerTags = isset($current_user->roles[0]) ? $current_user->roles[0] : "anonymous";
        
        $regService = $ScormService->getRegistrationService();
        
        if (function_exists("bp_activity_add")){
            global $bp;
            $querystr = "SELECT * FROM ".scormcloud_getDBPrefix()."scormcloudinvitations WHERE invite_id = '".$inviteReg->invite_id."'";
            $invites = $wpdb->get_results($querystr, OBJECT);
            $invite = $invites[0];
            
            
            $from_user_link = bp_core_get_userlink( $bp->loggedin_user->id );
            
            if (isset($widgetName)){
                $actionStr = sprintf('%s launched course "%s" from the %s widget', $from_user_link, $invite->course_title, $widgetName);
            } elseif ($invite->post_id != "__direct_invite__" && $invite->post_id != '__catalog_widget__'){
                $thisPost = get_post($invite->post_id);
                $post_permalink = get_permalink( $thisPost->ID );
                $actionStr = sprintf('%s launched course "%s" from post %s', $from_user_link, $invite->course_title, '<a href="' . $post_permalink . '">' . $thisPost->post_title . '</a>');
            } else {
                $actionStr = sprintf('%s launched course "%s"', $from_user_link, $invite->course_title);
            }
            //error_log('logged in user: '.$bp->loggedin_user->id);
            
            $activityArgs = array(
                'action' => $actionStr, // The activity action - e.g. "Jon Doe posted an update"
                'content' => '', // Optional: The content of the activity item e.g. "BuddyPress is awesome guys!"
        
                'component' => 'scormcloud', // The name/ID of the component e.g. groups, profile, mycomponent
                'type' => 'training_launch', // The activity type e.g. activity_update, profile_updated
                'primary_link' => '', // Optional: The primary URL for this item in RSS feeds (defaults to activity permalink)
        
                'user_id' => $bp->loggedin_user->id, // Optional: The user to record the activity for, can be false if this activity is not for a user.
                'item_id' => false, // Optional: The ID of the specific item being recorded, e.g. a blog_id
                'secondary_item_id' => false, // Optional: A second ID used to further filter e.g. a comment_id
                'recorded_time' => gmdate( "Y-m-d H:i:s" ), // The GMT time that this activity was recorded
                'hide_sitewide' => false // Should this be hidden on the sitewide activity stream?
            );
            $bpActivityId = bp_activity_add($activityArgs);
            error_log('Logging action: '.$actionStr.' Activity ID: '.$bpActivityId);
        }
        
        $cssUrl = (scormcloud_isScormCloudNetworkManaged()) ? get_site_option('scormcloud_player_cssurl') :  get_option('scormcloud_player_cssurl');
        
        echo $regService->GetLaunchUrl($regid,$returnUrl,$cssUrl,null,null,$learnerTags,$regTags);
        //echo 'regtags:'.$regTags;
        
        break;
    
    case "getPropertiesEditorUrl":
        $courseId = $_POST['courseid'];
        $courseService = $ScormService->getCourseService();
        $cssurl = get_option( 'siteurl' )."/wp-content/plugins/scormcloud/css/scormcloud.ppeditor.css";
        
        echo $courseService->GetPropertyEditorUrl($courseId,$cssurl,Null);
        
        break;
    
    case "getPreviewUrl":
        $courseId = $_POST['courseid'];
        $returnUrl = $_POST['returnurl'];
        
        $cssUrl = (scormcloud_isScormCloudNetworkManaged()) ? get_site_option('scormcloud_player_cssurl') :  get_option('scormcloud_player_cssurl');
        
        $courseService = $ScormService->getCourseService();
        echo $courseService->GetPreviewUrl($courseId,$returnUrl, $cssUrl);
        
        break;
    
    case "deletecourse":
        $courseId = $_POST['courseid'];
        
        //$querystr = "UPDATE ".scormcloud_getDBPrefix()."scormcloudinvitations SET active = 2 WHERE course_id = '$courseId'";
        
        $querystr = "DELETE r FROM wp_scormcloudinvitations AS i LEFT JOIN wp_scormcloudinvitationregs AS r ON i.invite_id=r.invite_id
            WHERE course_id = '$courseId'";
        $wpdb->query($querystr);
        $querystr = "DELETE FROM ".scormcloud_getDBPrefix()."scormcloudinvitations WHERE course_id = '$courseId'";
        
        $wpdb->query($querystr);
        
        $courseService = $ScormService->getCourseService();
        echo $courseService->DeleteCourse($courseId);
        
        break;
    
    case "getCourseReportUrl":
        $courseId = $_POST['courseid'];
        $rptService = $ScormService->getReportingService();
        $rptAuth = $rptService->GetReportageAuth('FREENAV',true);
        echo $rptService->LaunchCourseReport($rptAuth,$courseId);
        
        break;
    
    case "getRegReportUrl";
        $inviteId = $_POST['inviteid'];
        $regId = $_POST['regid'];
        
        $querystr = "SELECT inv.course_id, reg.user_email FROM ".scormcloud_getDBPrefix()."scormcloudinvitations inv
            JOIN ".scormcloud_getDBPrefix()."scormcloudinvitationregs reg ON inv.invite_id = reg.invite_id
            WHERE reg.invite_id = '$inviteId' AND reg.reg_id= '$regId'";
        $invites = $wpdb->get_results($querystr, OBJECT);
        $invite = $invites[0];
        
        $courseId = $invite->course_id;
        $userId = urlencode($invite->user_email);
        
        $rptService = $ScormService->getReportingService();
        $rptAuth = $rptService->GetReportageAuth('FREENAV',true);
        
        $rServiceUrl = $rptService->GetReportageServiceUrl();
        $reportageUrl = $rServiceUrl.'Reportage/reportage.php?appId='.$ScormService->getAppId()."&registrationId=$regId";
        $reportageUrl .= "&courseId=$courseId";
        $reportageUrl .= "&learnerId=$userId";
        //echo $reportageUrl;
        echo  $rptService->GetReportUrl($rptAuth, $reportageUrl);
        
        
        break;
    
    case "getInviteReportUrl":
        $inviteId = $_POST['inviteid'];
        
        
        $rptService = $ScormService->getReportingService();
        $rptAuth = $rptService->GetReportageAuth('FREENAV',true);
        
        $rServiceUrl = $rptService->GetReportageServiceUrl();
        $reportageUrl = $rServiceUrl.'Reportage/reportage.php?appId='.$ScormService->getAppId()."&registrationTags=$inviteId|_all";
        //echo $reportageUrl;
        echo  $rptService->GetReportUrl($rptAuth, $reportageUrl);
        
        break;
    
    case "getRegistrations":
        
        $inviteId = $_POST['inviteid'];
        
        //$invites = $wpdb->get_results("SELECT * FROM ".scormcloud_getDBPrefix()."scormcloudinvitations WHERE invite_id = '$inviteId'", OBJECT);
        //$invite = $invites[0];
        
        $querystr = "SELECT reg.*, inv.course_id FROM ".scormcloud_getDBPrefix()."scormcloudinvitationregs reg JOIN ".scormcloud_getDBPrefix()."scormcloudinvitations inv
            ON reg.invite_id = inv.invite_id
            WHERE reg.invite_id = '$inviteId' ORDER BY reg.update_date DESC";
        $inviteRegs = $wpdb->get_results($querystr, OBJECT);
        
        $regService = $ScormService->getRegistrationService();
        $regsXMLStr = $regService->GetRegistrationListResults($inviteId."-.*",$inviteRegs[0]->course_id,0);
        
        $regsXML = simplexml_load_string($regsXMLStr);
        $regList = $regsXML->registrationlist;
        
        $returnHTML = "";
        
        $returnHTML .= '<table class="widefat" cellspacing="0" id="InvitationListTable" >';
        $returnHTML .= '<thead>';
        $returnHTML .= '<tr class="thead"><th class="manage-column">User</th>
            <th class="manage-column">Completion</th>
            <th class="manage-column">Success</th>
            <th class="manage-column">Score</th>
            <th class="manage-column">Time</th>
            <th class="manage-column"></th></tr></thead>';
        $regcount = (count($inviteRegs) < 10) ? count($inviteRegs) : 10;
        for ($iter = 0; $iter < $regcount; $iter++ ){
            $inviteReg = $inviteRegs[$iter];
            
            
            $regResult = $regList->xpath("//registration[@id='".$inviteReg->reg_id."']");
            $regReport = $regResult[0]->registrationreport;
            
            $returnHTML .= "<tr key='".$inviteReg->reg_id."'>";
            if ($userId = $inviteReg->user_id){
                $wpUser = get_userdata($userId);
                $returnHTML .= "<td>".$wpUser->display_name."</td>";    
            } else {
                $returnHTML .= "<td>".$inviteReg->user_email."</td>";    
            }
            
            
            $returnHTML .= "<td class='".$regReport->complete."'>".$regReport->complete."</td>";
            $returnHTML .= "<td class='".$regReport->success."'>".$regReport->success."</td>";
            $score = $regReport->score;
            $returnHTML .= "<td>".($score == "unknown" ? "-" : $score."%")."</td>";
            $seconds = $regReport->totaltime;
            $returnHTML .= "<td>".floor($seconds / 60)."min ".($seconds % 60)."sec</td>";
            $returnHTML .= "<td><a href='javascript:void(0);' class='viewRegDetails' onclick='Scormcloud_loadRegReport(\"".$inviteReg->invite_id."\",\"".$inviteReg->reg_id."\"); return false;' key='".$inviteReg->invite_id."'>View Details</tr>";
            
    
            
        }
        
        $returnHTML .= '</table>';
        
        if (count($inviteRegs) >= 10){
            $returnHTML .= "<div class='viewInviteLink'><a href='".get_option( 'siteurl' )."/wp-admin/admin.php?page=scormcloudtraining&inviteid=".$inviteId."'>Click here to see complete training history.</a></div>";
        }
        
        
        echo $returnHTML;
        
        break;
    
    case "setactive":
        $inviteId = $_POST['inviteid'];
        $active = $_POST['active'];
        
        $querystr = "UPDATE ".scormcloud_getDBPrefix()."scormcloudinvitations SET active = $active WHERE invite_id = '$inviteId'";
        $wpdb->query($querystr);
        
        break;
    
    case "addCatalogRegGetLaunchUrl":
        $courseId = $_POST['courseid'];
        $courseTitle = $_POST['coursetitle'];
        $returnUrl = $_POST['returnurl'];
        
        $inviteId = uniqid();
        $regid = $inviteId."-".uniqid();
        
		$appId = get_option('scormcloud_appid');
        global $current_user;
        global $wpdb;
        get_currentuserinfo();
        
        $user_email = $current_user->user_email;
        if (!($user_first_name = $current_user->user_firstname) || strlen($user_first_name) < 1){
            $user_first_name = $current_user->display_name;
        }
        if (!($user_last_name = $current_user->user_lastname) || strlen($user_last_name) < 1){
            $user_last_name = $current_user->display_name;
        }
        $postId = "__catalog_widget__";
        
        $header = "";
        $description = "";
        
        $require_login = 0;
        $show_course_info = 0;
        
        $wpdb->query($wpdb->prepare( "
		    INSERT INTO ".scormcloud_getDBPrefix()."scormcloudinvitations 
				(invite_id, blog_id, app_id, post_id, course_id, course_title, header, description, require_login, show_course_info)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %d, %d)", 
			        $inviteId, $GLOBALS['blog_id'], $appId, $postId, $courseId, $courseTitle, $header, $description, $require_login, $show_course_info));
        
        
        $courseTags = 'catalog_widget';
        $regTags = $GLOBALS['blog_id'].','.$inviteId.','.$courseTags;
        
        $learnerTags = isset($current_user->roles[0]) ? $current_user->roles[0] : "anonymous";
        
        //create the cloud registration
        $regService = $ScormService->getRegistrationService();
        $regService->CreateRegistration($regid, $courseId, $user_email, $user_first_name, $user_last_name,$user_email);
        
        $wpdb->query($wpdb->prepare( "
		    INSERT INTO ".scormcloud_getDBPrefix()."scormcloudinvitationregs 
				(invite_id, reg_id, user_id, user_email)
				VALUES (%s, %s, %d, %s)", 
			        $inviteId, $regid, $current_user->ID, $user_email));
        
        if (function_exists("bp_activity_add")){
            global $bp;
            
            $from_user_link = bp_core_get_userlink( $bp->loggedin_user->id );
            $actionStr = sprintf('%s launched course "%s" from the Catalog Widget', $from_user_link , $courseTitle);
            
            //error_log('logged in user: '.$bp->loggedin_user->id);
            
            $activityArgs = array(
                'action' => $actionStr, // The activity action - e.g. "Jon Doe posted an update"
                'content' => '', // Optional: The content of the activity item e.g. "BuddyPress is awesome guys!"
        
                'component' => 'scormcloud', // The name/ID of the component e.g. groups, profile, mycomponent
                'type' => 'training_launch', // The activity type e.g. activity_update, profile_updated
                'primary_link' => '', // Optional: The primary URL for this item in RSS feeds (defaults to activity permalink)
        
                'user_id' => $bp->loggedin_user->id, // Optional: The user to record the activity for, can be false if this activity is not for a user.
                'item_id' => false, // Optional: The ID of the specific item being recorded, e.g. a blog_id
                'secondary_item_id' => false, // Optional: A second ID used to further filter e.g. a comment_id
                'recorded_time' => gmdate( "Y-m-d H:i:s" ), // The GMT time that this activity was recorded
                'hide_sitewide' => false // Should this be hidden on the sitewide activity stream?
            );
            $bpActivityId = bp_activity_add($activityArgs);
            error_log('Logging action: '.$actionStr.' Activity ID: '.$bpActivityId);
        }
        
        $cssUrl = (scormcloud_isScormCloudNetworkManaged()) ? get_site_option('scormcloud_player_cssurl') :  get_option('scormcloud_player_cssurl');
        
        echo $regService->GetLaunchUrl($regid,$returnUrl,$cssUrl,null,$courseTags,$learnerTags,$regTags);
        
        
        break;
    
    case "addAnonCatalogRegGetLaunchUrl":
        
        $user_first_name = $_POST['fname'];
        $user_last_name = $_POST['lname'];
        $user_email = $_POST['email'];
        
        $courseId = $_POST['courseid'];
        $courseTitle = $_POST['coursetitle'];
        $returnUrl = $_POST['returnurl'];
        
        
        $postId = "__catalog_widget__";
        
        $header = "";
        $description = "";
        
        $regService = $ScormService->getRegistrationService();
        
        $querystr = "select r.reg_id, r.invite_id from ".scormcloud_getDBPrefix()."scormcloudinvitations i
                JOIN ".scormcloud_getDBPrefix()."scormcloudinvitationregs r on i.invite_id = r.invite_id
                WHERE r.user_email = '$user_email' and i.course_id = '$courseId'";
        $inviteRegs = $wpdb->get_results($querystr, OBJECT);
        if (count($inviteRegs) > 0) {
            $inviteReg = $inviteRegs[0];
            $regid = (string)$inviteReg->reg_id;
            $inviteId = (string)$inviteReg->invite_id;
        } else {
            $inviteId = uniqid();
            $regid = $inviteId."-".uniqid();
        
            $require_login = 0;
            $show_course_info = 0;
            //error_log(scormcloud_getDBPrefix());
            $wpdb->query($wpdb->prepare( "
                INSERT INTO ".scormcloud_getDBPrefix()."scormcloudinvitations 
                    (invite_id, blog_id, app_id, post_id, course_id, course_title, header, description, require_login, show_course_info)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %d, %d)", 
                        $inviteId, $GLOBALS['blog_id'], $appId, $postId, $courseId, $courseTitle, $header, $description, $require_login, $show_course_info));
        
            //create the cloud registration
            $regService->CreateRegistration($regid, $courseId, $user_email, $user_first_name, $user_last_name, $user_email);
            
            $wpdb->query($wpdb->prepare( "
		    INSERT INTO ".scormcloud_getDBPrefix()."scormcloudinvitationregs 
				(invite_id, reg_id, user_email)
				VALUES (%s, %s, %s)", 
			        $inviteId, $regid, $user_email));
        }
        
        $courseTags = 'catalog_widget';
        $regTags = $GLOBALS['blog_id'].','.$inviteId.','.$courseTags;
        
		$learnerTags = isset($current_user->roles[0]) ? $current_user->roles[0] : "anonymous";
        
        if (function_exists("bp_activity_add")){
            global $bp;
            
            $actionStr = sprintf('%s launched course "%s" from the Catalog Widget', $user_first_name.' '.$user_last_name , $courseTitle);
            
            //error_log('logged in user: '.$bp->loggedin_user->id);
            
            $activityArgs = array(
                'action' => $actionStr, // The activity action - e.g. "Jon Doe posted an update"
                'content' => '', // Optional: The content of the activity item e.g. "BuddyPress is awesome guys!"
        
                'component' => 'scormcloud', // The name/ID of the component e.g. groups, profile, mycomponent
                'type' => 'training_launch', // The activity type e.g. activity_update, profile_updated
                'primary_link' => '', // Optional: The primary URL for this item in RSS feeds (defaults to activity permalink)
        
                'user_id' => false, // Optional: The user to record the activity for, can be false if this activity is not for a user.
                'item_id' => false, // Optional: The ID of the specific item being recorded, e.g. a blog_id
                'secondary_item_id' => false, // Optional: A second ID used to further filter e.g. a comment_id
                'recorded_time' => gmdate( "Y-m-d H:i:s" ), // The GMT time that this activity was recorded
                'hide_sitewide' => false // Should this be hidden on the sitewide activity stream?
            );
            $bpActivityId = bp_activity_add($activityArgs);
            error_log('Logging action: '.$actionStr.' Activity ID: '.$bpActivityId);
        }
        
        $cssUrl = (scormcloud_isScormCloudNetworkManaged()) ? get_site_option('scormcloud_player_cssurl') :  get_option('scormcloud_player_cssurl');
        
        echo $regService->GetLaunchUrl($regid,$returnUrl,$cssUrl,null,$courseTags,$learnerTags,$regTags);
        
        
        break;
    
	default:
		break;
}

?>