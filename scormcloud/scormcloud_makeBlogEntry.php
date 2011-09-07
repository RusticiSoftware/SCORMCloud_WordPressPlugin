<?php

function scormcloud_makeBlogEntry($content){
    global $wpdb;    
    
    
    
    preg_match_all('/\[scormcloud.training:.*\]/',$content,$cloudTagArray);
    
    $cloudTags = $cloudTagArray[0];
    
    foreach($cloudTags as $tagString){
        require_once('scormcloud.wp.php');
        $ScormService = scormcloud_getScormEngineService();
        $isValidAccount = $ScormService->isValidAccount();
        
        $inviteId = substr($tagString,21,strlen($tagString) - 22);
        
        $invite = scormcloud_getInvitation($inviteId);
        if ($invite == null) {
            $content = str_replace($tagString,'',$content);
        }
        
        $inviteHtml = "<div class='scormCloudInvitation' key='$inviteId'>";
        $inviteHtml .= "<h3>".stripcslashes($invite->header)."</h3>";
        $inviteHtml .= "<p class='description'>".stripcslashes($invite->description)."</p>";
        
        if ($invite->show_course_info == 1){
            //get course info
            $inviteHtml .= "<div class='courseInfo'>";
            
            if ($isValidAccount){
                $courseService = $ScormService->getCourseService();
                
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
            
            $regsRemaining = scormcloud_regsRemaining();
            
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
                $query = $wpdb->prepare('SELECT reg_id FROM '.scormcloud_getTableName('scormcloudinvitationregs').' WHERE invite_id = %s AND
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
        $inviteHtml .= "<div class='inviteMessage'>message</div>";
        //$inviteHtml .= "<div class='serviceCredit'>Delivery via <a href='http://www.scorm.com/scorm-solved/scorm-cloud/'>SCORM Cloud</a></div>";
        $inviteHtml .= "</div>";
        $content = str_replace($tagString,$inviteHtml,$content);    
    }
    
    return $content;
    

    //return $content;
}

function scormcloud_UpdatePostInvite($postId){
    global $wpdb;
    require_once('scormcloud.wp.php');
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
        
        $wpdb->update(scormcloud_getTableName('scormcloudinvitations'),
                      array('post_id' => $postId, 'invite_id' => $inviteId));
    }
    
}

?>
