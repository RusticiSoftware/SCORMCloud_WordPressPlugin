<?php

require_once(SCORMCLOUD_BASE.'scormcloudplugin.php');
require_once(SCORMCLOUD_BASE.'db/scormclouddatabase.php');

class ScormCloudContentHandler
{
    public static function make_blog_entry($content){
        global $wpdb;
        
        preg_match_all('/\[scormcloud.training:.*\]/',$content,$cloudTagArray);
    
        $cloudTags = $cloudTagArray[0];
    
        foreach($cloudTags as $tagString) {
            $ScormService = ScormCloudPlugin::get_cloud_service();
            try {
                $isValidAccount = $ScormService->isValidAccount();
            } catch (Exception $e) {
                $isValidAccount = false;
            }
    
            $inviteId = substr($tagString,21,strlen($tagString) - 22);
    
            $invite = ScormCloudDatabase::get_invitation($inviteId);
            if ($invite == null) {
                $content = str_replace($tagString,'',$content);
            }
    
            $inviteHtml = "<div class='scormCloudInvitation' key='$inviteId'>";
            $inviteHtml .= "<h3>".stripcslashes($invite->header)."</h3>";
            $inviteHtml .= "<p class='description'>".stripcslashes($invite->description)."</p>";
    		
			$courseService = $ScormService->getCourseService();
			
			if (!$courseService->Exists($invite->course_id)){
                $inviteHtml .= "<h3>".__("This training is not currently available.","scormcloud")."</h3>";
            } else {

            	if ($invite->show_course_info == 1){
	                //get course info
	                $inviteHtml .= "<div class='courseInfo'>";
    
	                if ($isValidAccount){
	                    $courseMDXmlStr = $courseService->GetMetadata($invite->course_id,0,0,0);
	                    //$inviteHtml .= $courseMDXmlStr;
	                    $courseMDXml = simplexml_load_string($courseMDXmlStr);//,"SimpleXMLElement",LIBXML_NOCDATA);
	                    $metadata = $courseMDXml->package->metadata;
	                }
	                $inviteHtml .= "<div class='title'>Title: ".$invite->course_title."</div>";
    
	                if ($isValidAccount && $metadata != null){
	                    $inviteHtml .= "<div class='desc'>".$metadata->description."</div>";
    
	                    //assuming seconds coming back for now
	                    $duration = ((int)$metadata->duration)/100;
	                    if ($duration > 0){
	                        $inviteHtml .= "<div class='duration'>Duration: ".floor($duration / 60)."min ".($duration % 60)."sec </div>";
	                    }
	                }
    
    
    
	                $inviteHtml .= "</div>";
	            }
    
	            if (!$isValidAccount || $invite->active != 1){
	                $inviteHtml .= "<h3>".__("This training is not currently active.","scormcloud")."</h3>";
	            } else {
    
	                $regsRemaining = ScormCloudPlugin::remaining_registrations();
    
	                global $current_user;
	                global $wpdb;
	                get_currentuserinfo();
    
	                //if not logged in
	                if(!isset($current_user->user_login) || $current_user->user_login == '')
	                {
	                    if ($regsRemaining > 0){
	                        if ($invite->require_login == 0){
	                            $inviteHtml .= "<p class='inputs'>My name is <input name='scormcloudfname' placeholder='First Name' type='text' key='$inviteId'/>&nbsp;<input name='scormcloudlname' placeholder='Last Name' type='text' key='$inviteId'/>";
	                            $inviteHtml .= " and my email is <input name='scormcloudemail' placeholder='Email' type='text' key='$inviteId'/> .</p>";
	                            $inviteHtml .= "<input name='launch' type='button' key='$inviteId' onclick='ScormCloud.Post.makeAnonRegLaunch(\"$inviteId\");' url='" .get_option( 'siteurl' ) ."/wp-content/plugins/scormcloud/ajax.php' value='Start Training'/>";
	                        } else {
	                            $inviteHtml .= "<h3>".__("Please log in to take this training.","scormcloud")."</h3>";
	                        }
	                    } else {
	                        $inviteHtml .= "<h3>".__("This training is not currently active.","scormcloud")."</h3>";
	                    }
	                } else {
	                    $userId = $current_user->ID;
	                    $query = $wpdb->prepare('SELECT reg_id FROM '.ScormCloudDatabase::get_registrations_table().' WHERE invite_id = %s AND
	                                             user_id = %s ORDER BY update_date DESC', array($inviteId, $userId));
	                    $reg = $wpdb->get_row($query, OBJECT);
	                    if ($reg != null){
	                        $regId = $reg->reg_id;
    
	                        $regService = $ScormService->getRegistrationService();
	                        $regResultsXmlStr = $regService->GetRegistrationResult($regId,0,0);
	                        $resXml = simplexml_load_string($regResultsXmlStr);
    
    
	                        $completion = $resXml->registrationreport->complete;
	                        $success = $resXml->registrationreport->success;
	                        $seconds = $resXml->registrationreport->totaltime;
	                        $score = $resXml->registrationreport->score;
    
	                        $inviteHtml .= "<table class='result_table'><tr>" .
	                        "<td class='head'>".__("Completion","scormcloud")."</td>" .
	                        "<td class='head'>".__("Success","scormcloud")."</td>" .
	                        "<td class='head'>".__("Score","scormcloud")."</td>" .
	                        "<td class='head'>".__("Total Time","scormcloud")."</td>" .
	                        "</tr><tr>" .
	                        "<td class='$completion'>".$completion."</td>" .
	                        "<td class='$success'>".$success."</td>" .
	                        "<td class='".($score == "unknown" ? __("unknown") : "")."'>".($score == "unknown" ? "-" : $score."%")."</td>".
	                        "<td class='time'>$seconds ".__("secs","scormcloud")."</td>" .
	                        "</tr></table>";
    
    
	                        $inviteHtml .= "<input name='launch' type='button' key='$inviteId' onclick='ScormCloud.Post.getLaunchURL(\"$inviteId\",\"$regId\");' url='" .get_option( 'siteurl' ) ."/wp-content/plugins/scormcloud/ajax.php' value='".__('Relaunch Training','scormcloud')."' />";
    
    
	                    }else{
	                        if ($regsRemaining > 0){
	                            $inviteHtml .= "<input name='launch' type='button' key='$inviteId' onclick='ScormCloud.Post.makeUserRegLaunch(\"$inviteId\");' url='" .get_option( 'siteurl' ) ."/wp-content/plugins/scormcloud/ajax.php' value='Start Training'/>";
	                        } else {
	                            $inviteHtml .= "<h3>".__("This training is not currently active.","scormcloud")."</h3>";
	                        }
	                    }
    
    
	                }
				}
            }
            $inviteHtml .= "<div class='inviteMessage'>message</div>";
            //$inviteHtml .= "<div class='serviceCredit'>Delivery via <a href='http://www.scorm.com/scorm-solved/scorm-cloud/'>SCORM Cloud</a></div>";
            $inviteHtml .= "</div>";
            $content = str_replace($tagString,$inviteHtml,$content);
        }

		preg_match_all('/\[scormcloud.reportage:.*]/',$content,$cloudRepArray);
    
        $cloudReportageLinks = $cloudRepArray[0];
    
        foreach($cloudReportageLinks as $tagString) {
			$ScormService = ScormCloudPlugin::get_cloud_service();
			try {
                $isValidAccount = $ScormService->isValidAccount();
            } catch (Exception $e) {
                $isValidAccount = false;
            }
			
			if ($isValidAccount){
				$linkText = substr($tagString,22,strlen($tagString) - 23);
				$rptService = $ScormService->getReportingService();
				$rServiceUrl = $rptService->GetReportageServiceUrl();
				$rptAuth = $rptService->GetReportageAuth('FREENAV',true);
				$reportageUrl = $rServiceUrl.'Reportage/reportage.php?appId='.$ScormService->getAppId()."&registrationTags=".$GLOBALS['blog_id']."|_all";
			    $repHtml = '<a id="ReportageLink" href="'.$rptService->GetReportUrl($rptAuth, $reportageUrl).'" 
							title="'. __("Open the SCORM Reportage Console in a new window.","scormcloud").'">'. $linkText.'</a>';
			
	
				$content = str_replace($tagString,$repHtml,$content);
			}
	
		}
    
        return $content;
    
    
        //return $content;
    }
    
    public static function update_post_invite($postId){
        global $wpdb;
        $post = get_post($postId);
        $content = $post->post_content;
    
        if($parent_id = wp_is_post_revision($postId))
        {
            $postId = $parent_id;
        }
    
    
        preg_match_all('/\[scormcloud.training:.*\]/',$content,$cloudTagArray);
    
        $cloudTags = $cloudTagArray[0];
    
        foreach($cloudTags as $tagString){
            $inviteId = substr($tagString,21,strlen($tagString) - 22);
    
            $wpdb->update(ScormCloudDatabase::get_invitations_table(),
            array('post_id' => $postId), array('invite_id' => $inviteId));
        }
    
    }
    
    // This might go better somewhere else but best I can come up with for now...
    public static function update_learner_info($userId) {
        global $wpdb;
        $ScormService = ScormCloudPlugin::get_cloud_service();
        $regService = $ScormService->getRegistrationService();
        $userData = get_userdata($userId);
        $response = $regService->UpdateLearnerInfo($userData->user_email,$userData->user_firstname,$userData->user_lastname);
        write_log($response);
    }
}