<?php
global $wpdb;
if ( defined('ABSPATH') )
require_once(ABSPATH . 'wp-load.php');
else
require_once('../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/admin.php');


require_once(SCORMCLOUD_BASE.'scormcloudplugin.php');
$ScormService = ScormCloudPlugin::get_cloud_service();
try {
    $isValidAccount = $ScormService->isValidAccount();
} catch (Exception $e) {
    $isValidAccount = false;
}

echo '<div class="scormcloud-admin-page courses">';

if ($isValidAccount){

    echo '<h2>'.__("Import a new course.","scormcloud").'</h2>';
    //echo $GLOBALS['blog_id'];
    $packageid = $GLOBALS['blog_id'].'-'.uniqid();
    ?>
<div id="UploadFrame"><iframe width="100%" height="50px"
	style="border: 0;"
	src="<?php echo site_url(); ?>/wp-content/plugins/scormcloud/uploadpif.php?id=<?php echo $packageid; ?>"
	id="ifmImport"></iframe></div>

    <?php
    $coursesFilter = null;
	if (ScormCloudPlugin::is_network_managed() && get_site_option('scormcloud_sharecourses') !== '1'){
        $coursesFilter = $GLOBALS['blog_id']."-.*" ;
    }
	
    $courseService = $ScormService->getCourseService();
    $courseObjArray = $courseService->GetCourseList($coursesFilter);

    $courseCount = count($courseObjArray);
	
	$courseObjArray = array_reverse($courseObjArray);
	
    if ($courseCount > 0){
        ?>
<div>
<h2><?php _e("All Courses","scormcloud"); ?></h2>
</div>
<table class="widefat" cellspacing="0" id="CourseListTable">
	<thead>
		<tr class="thead">
			<th class="manage-column"><?php _e("Course Title","scormcloud"); ?></th>
			<th colspan="5" class="manage-column">
		
		</tr>
	</thead>
	<?php
	foreach($courseObjArray as $course)
	{
	    echo "<tr key='".$course->getCourseId()."' class='courseRow'><td class='title'>";
	    echo $course->getTitle()."<a key='".$course->getCourseId()."' class='previewLink' onclick='scormcloud_LaunchCoursePreview(\"".$course->getCourseId()."\",\"".site_url() . "/wp-content/plugins/scormcloud/ajax.php\",window.location);' href='javascript:void(0);'>".__("Preview","scormcloud")."</a>";
	    echo '</td><td>';
	    $regCount = $course->getNumberOfRegistrations();
	    echo "$regCount ".($regCount != 1 ? __("Learners","scormcloud") : __("Learner","scormcloud"));
	    echo '</td><td>';
	    echo "<a key='".$course->getCourseId()."' class='reportLink' onclick='scormcloud_LaunchCourseReport(\"".$course->getCourseId()."\",\"".site_url() . "/wp-content/plugins/scormcloud/ajax.php\");' href='javascript:void(0);'>".__("View Course Report","scormcloud")."</a>";
	    echo '</td>';
	    echo "<td><a href='#' key='".$course->getCourseId()."' class='viewPkgPropsLink' >".__("Edit Course Properties","scormcloud")."</a></td>";
	    echo '<td>';
	    if (strpos($course->getCourseId(),$GLOBALS['blog_id']."-") === 0){
	        echo "<a key='".$course->getCourseId()."' class='deleteLink' onclick='scormcloud_deleteCourse(\"".$course->getCourseId()."\",\"".site_url() . "/wp-content/plugins/scormcloud/ajax.php\");'  >".__("Delete Course","scormcloud")."</a>";
	    }
	    echo '</td></tr>';
	    echo "<tr class='propseditor' key='".$course->getCourseId()."' ><td class='regList' colspan='5'><iframe src=''></iframe></td></tr>";
	}
	?>
</table>
<script language="javascript">
jQuery('.viewPkgPropsLink').toggle(function(){
    var courseid = jQuery(this).attr('key');
    if (jQuery('tr.propseditor[key="'+ courseid + '"] iframe').attr('src') == ''){
        jQuery.ajax({
            type: "POST",
            url: "<?php echo site_url() . '/wp-content/plugins/scormcloud/ajax.php'; ?>",
            data: 	"action=getPropertiesEditorUrl" +
                    "&courseid=" + courseid,
            success: function(data){
                jQuery('tr.propseditor[key="'+ courseid + '"] iframe').attr('src',data);
            }
        });
    }
    
    jQuery('tr.courseRow[key="'+ courseid + '"]').addClass('active');
    jQuery('tr.propseditor[key="'+ courseid + '"]').fadeIn();

},
function(){
    var courseid = jQuery(this).attr('key');
    jQuery('tr.courseRow[key="'+ courseid + '"]').removeClass('active');
    jQuery('tr.propseditor[key="'+ courseid + '"]').fadeOut();
    
});

</script>

	<?php
    }

} else {
    echo "<div>
            <h2>".__("Please configure your SCORM Cloud settings to view your courses.","scormcloud")."</h2>
        </div>";
    echo '<div class="settingsPageLink"><a href="'.site_url().'/wp-admin/admin.php?page=scormcloudsettings"
				title="'.__("Click here to configure your SCORM Cloud plugin.","scormcloud").'">'.__("Click Here to go to the settings page.","scormcloud").'</a></div>';
}

?>



</div>
