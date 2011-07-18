<?php

/* Finding the path to the wp-admin folder */
$iswin = preg_match('/:\\\/', dirname(__file__));
$slash = ($iswin) ? "\\" : "/";

$wp_path = preg_split('/(?=((\\\|\/)wp-content)).*/', dirname(__file__));
$wp_path = (isset($wp_path[0]) && $wp_path[0] != "") ? $wp_path[0] : $_SERVER["DOCUMENT_ROOT"];

/** Load WordPress Administration Bootstrap */
require_once($wp_path . $slash . 'wp-load.php');
require_once($wp_path . $slash . 'wp-admin' . $slash . 'admin.php');

global $wpdb;



require_once('scormcloud.wp.php');
$ScormService = scormcloud_getScormEngineService();
$isValidAccount = $ScormService->isValidAccount();

if ($isValidAccount){
?>
<div id="embedTrainingDialog">
<h1><?php _e("Add training to your post","scormcloud"); ?></h1>


<span class="labelheader"><?php _e("First select a course","scormcloud"); ?>: </span>
<select class="courseSelector">
    
<?php
	
    echo "<option value=''></option>";
    $coursesFilter = (scormcloud_isScormCloudNetworkManaged() && get_site_option('scormcloud_sharecourses') !== 'on') ? $GLOBALS['blog_id']."-.*" : null ;
    $courseService = $ScormService->getCourseService();
    $allResults = $courseService->GetCourseList($coursesFilter);
    foreach($allResults as $course){
        echo "<option value='".$course->getCourseId()."'>".$course->getTitle()."</option>";
    }
	
?>
</select>

<br/>
<br/>

<div class='selectOptionsDiv'>
    <span class="labelheader"><?php _e("Next select some options","scormcloud"); ?>: </span>
    <table>
        <tr>
            <td class="label"><?php _e("Training Header Text","scormcloud"); ?>:</td>
            <td><input type="text" name="trainingHeaderTxt" value="<?php _e("Launch Training Now","scormcloud"); ?>"/></td>
        </tr>
        <tr>
            <td class="label"><?php _e("Training Description","scormcloud"); ?>:</td>
            <td><textarea type="text" name="trainingDesc" ><?php _e("Click 'Start Training' to take your training.","scormcloud"); ?></textarea></td>
        </tr>
        <tr>
            <td colspan='2'><input type="checkbox" checked name="trainingRequireLogin"/><?php _e("Require that learners be authenticated users.","scormcloud"); ?></td>
        </tr>
        <tr>
            <td colspan='2'><input type="checkbox" checked name="showCourseInfo"/><?php _e("Show the course title and description.","scormcloud"); ?></td>
        </tr>
        
    </table>
    


<input type="button" class="generateTrainingTag button" name="generateTrainingTag" value="<?php _e("Embed This Training","scormcloud"); ?>" />

<a href="javascript:void(0);" class="toggleButton previewPostEmbed" toggleobject='#embedTrainingDialog .previewDiv' onText='<?php _e("hide preview","scormcloud"); ?>' offText='<?php _e("show preview","scormcloud"); ?>'><?php _e("show preview","scormcloud"); ?></a>
<div class="previewDiv">
    <div class="scormCloudInvitation">
        <h3><?php _e("Launch Training Now","scormcloud"); ?></h3>
        <p class="description"><?php _e("Click 'Start Training' to take your training.","scormcloud"); ?></p>
        <div class="courseInfo">
            <div class="title"></div>
            <div class="desc"><?php _e("This will be the metadata description for your course (if it exists).  Also, the displayed duration will render if it exists in the metadata.","scormcloud"); ?></div>
            <div class="duration"><?php _e("Duration: 10 minutes","scormcloud"); ?></div>
        </div>
        <p class="inputs">
            <?php _e("My name is","scormcloud"); ?> <input disabled name="scormcloudfname" placeholder="First Name" type="text" >
            <input name="scormcloudlname" disabled placeholder="Last Name" type="text" >
                <?php _e("and my email is","scormcloud"); ?> <input name="scormcloudemail" disabled placeholder="Email" type="text"> .</p>
        <input type="button" class="button" value="<?php _e("Start Training","scormcloud"); ?>" onclick="return false;" name="launch">
        
    </div>
</div>

</div>
<script type="text/javascript" charset="utf-8">
	var $j = jQuery.noConflict();
    $j(document).ready(function(){
        
        $j('#TB_ajaxContent').removeAttr('style');
        $j('#TB_window').css('overflow-y','auto');
        
        $j('#embedTrainingDialog .toggleButton').toggle(function(){
            $j($j(this).attr('toggleobject')).fadeIn('slow');
            $j(this).text($j(this).attr('onText'));
        },function(){
            $j($j(this).attr('toggleobject')).fadeOut('slow');
            $j(this).text($j(this).attr('offText'));
        });
        
        $j('#embedTrainingDialog .courseSelector').change(function(){
            var selObj = $j('#embedTrainingDialog .courseSelector option:selected');
            if (selObj.val() != ''){
                //var newheader = 'Training: ' + selObj.text();
                //$j('#embedTrainingDialog input[name="trainingHeaderTxt"]').val(newheader);
                //$j('#embedTrainingDialog .previewDiv h3').text(newheader);
                $j('#embedTrainingDialog .previewDiv div.courseInfo div.title').text('<?php _e("Title","scormcloud"); ?>: ' + selObj.text());
                $j('#embedTrainingDialog .selectOptionsDiv').fadeIn('slow');
                tb_position();
            }
        });
        
        $j('#embedTrainingDialog input[name="trainingHeaderTxt"]').change(function(){
            $j('#embedTrainingDialog .previewDiv h3').text($j(this).val());
        });
        $j('#embedTrainingDialog textarea[name="trainingDesc"]').change(function(){
            $j('#embedTrainingDialog p.description').text($j(this).val());
        });
        $j('#embedTrainingDialog input[name="trainingRequireLogin"]').change(function(){
            $j('#embedTrainingDialog .previewDiv p.inputs').toggle();
        });
        $j('#embedTrainingDialog input[name="showCourseInfo"]').change(function(){
            $j('#embedTrainingDialog .previewDiv div.courseInfo').toggle();
        });
        
        $j("#embedTrainingDialog .generateTrainingTag").click(function(e) {
            
            
            var courseId = $j('#embedTrainingDialog .courseSelector option:selected').attr('value');
            var courseTitle = $j('#embedTrainingDialog .courseSelector option:selected').text();
            var header = $j('#embedTrainingDialog input[name="trainingHeaderTxt"]').attr('value');
            var description = $j('#embedTrainingDialog textarea[name="trainingDesc"]').attr('value');
            var requirelogin = $j('#embedTrainingDialog input[name="trainingRequireLogin"]:checked').length;
            var showcourseinfo = $j('#embedTrainingDialog input[name="showCourseInfo"]:checked').length;
            
            $j.ajax({
			type: "POST",
			url: "<?php echo get_option( 'siteurl' ) . '/wp-content/plugins/scormcloud/ajax.php'; ?>",
			data: 	"action=addPostInvite" + 
					"&courseid=" + courseId +
                    "&coursetitle=" + courseTitle +
                    "&header=" + header +
                    "&description=" + description +
                    "&requirelogin=" + requirelogin +
                    "&showcourseinfo=" + showcourseinfo,
			success: function(html){
			    ScormCloud.Dialog.insertTag("[scormcloud.training:"+ html + "]");
			}
            });
            
            
        });
       
       
    });

    
        
		
</script>
</div>
<?php
} else {
     echo "<div>
            <h2>".__("Please configure your SCORM Cloud settings to add training to your posts.","scormcloud")."</h2>
        </div>";
    echo '<div class="settingsPageLink"><a href="'.get_option( 'siteurl' ).'/wp-admin/admin.php?page=scormcloudsettings" 
				title="'.__("Click here to configure your SCORM Cloud plugin.","scormcloud").'">'.__("Click Here to go to the settings page.","scormcloud").'</a></div>';
    
    
    
}
?>
