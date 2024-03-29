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

function loadAllCourses($courseService) {
	$courseResponse = $courseService->getCourses(null, null, 'updated', null, null, null, null, null, 'false', 'true');
	$more = $courseResponse->getMore();
	$courseArray = $courseResponse->getCourses();

	if ($more != '') {
		$moreCourses = handleMoreCourses($more, $courseService);
		foreach($moreCourses as $course) {
			array_push($courseArray, $course);
		}
	}

	return $courseArray;
}

function handleMoreCourses($more, $courseService) {
	if ($more != '') {
		// there are more results to load them up recursively if needed
		$moreResponse = $courseService->getCourses(null, null, 'updated', null, null, null, null, $more, 'false', 'true');
		$moreCourses = $moreResponse->getCourses();
		$moreMore = $moreResponse->getMore();
		if ($moreMore != '') {
			$evenMoreCourses = handleMoreCourses($moreMore, $courseService);
			foreach($evenMoreCourses as $course) {
				array_push($moreCourses, $course);
			}
		}
		return $moreCourses;
	}
}



echo '<div class="scormcloud-admin-page courses">';

if ($isValidAccount){

    echo '<h2>'.__("Import a new course.","scormcloud").'</h2>';
    $packageid = $GLOBALS['blog_id'].'-'.uniqid();
    ?>
<div id="UploadFrame">
<iframe width="100%" height="110px"
	style="border: 0;"
	src="<?= plugin_dir_url( __FILE__ ) ?>uploadpif.php?id=<?php echo $packageid; ?>"
	id="ifmImport"></iframe></div>

    <?php
    $coursesFilter = null;
	if (ScormCloudPlugin::is_network_managed() && get_site_option('scormcloud_sharecourses') !== '1'){
        $coursesFilter = $GLOBALS['blog_id']."-.*" ;
    }
	
    $courseService = $ScormService->getCourseService();
    $allCourses = loadAllCourses($courseService);
    
    $courseCount = count($allCourses);
    if ($courseCount > 0){
        ?>
<div>
<h2><?php _e("All Courses","scormcloud"); ?> [ <?=$courseCount?> courses ]</h2>
</div>
<table class="widefat" cellspacing="0" id="CourseListTable">
	<thead>
		<tr class="thead">
			<th class="manage-column"><?php _e("Course Title","scormcloud"); ?></th>
			<th colspan="5" class="manage-column">
		
		</tr>
	</thead>
	<?php
	foreach($allCourses as $course)
	{
	    echo "<tr key='".$course->getId()."' class='courseRow'><td class='title'>";
	    echo $course->getTitle();
        if ($course->getCourseLearningStandard() !== 'CMI5'){
        echo "<a key='".$course->getId()."' class='previewLink' onclick='scormcloud_LaunchCoursePreview(\"".$course->getId()."\",\"".site_url() . "/wp-content/plugins/scormcloud/ajax.php\",window.location);' href='javascript:void(0);'>".__("Preview","scormcloud")."</a>";
        } else {
            echo "<span class='previewLink'>cmi5 content cannot be previewed currently</span>";
        }
	    echo '</td><td>';
	    $regCount = $course->getRegistrationCount();
	    echo "$regCount ".($regCount != 1 ? __("Learners","scormcloud") : __("Learner","scormcloud"));
	    echo '</td><td>';
	    echo "<a key='".$course->getId()."' class='reportLink' onclick='scormcloud_LaunchCourseReport(\"".$course->getId()."\",\"".site_url() . "/wp-content/plugins/scormcloud/ajax.php\");' href='javascript:void(0);'>".__("View Course Report","scormcloud")."</a>";
	    echo '</td>';
	    echo "<td><a href='#' key='".$course->getId()."' class='viewPkgPropsLink' >".__("Edit Course Properties","scormcloud")."</a></td>";
	    echo '<td>';
	    if (strpos($course->getId(),$GLOBALS['blog_id']."-") === 0){
	        echo "<a key='".$course->getId()."' class='deleteLink' onclick='scormcloud_deleteCourse(\"".$course->getId()."\",\"".site_url() . "/wp-content/plugins/scormcloud/ajax.php\");'  >".__("Delete Course","scormcloud")."</a>";
	    }
	    echo '</td></tr>';
	    echo "<tr class='propseditor' key='".$course->getId()."' ><td class='regList' colspan='5'><iframe src=''></iframe></td></tr>";
	}
	?>
</table>
<script language="javascript">
jQuery('.viewPkgPropsLink').click(function(){
    var courseid = jQuery(this).attr('key');
    if (jQuery('tr.propseditor[key="'+ courseid + '"] iframe').attr('src') == ''){
        jQuery('tr.propseditor[key="'+ courseid + '"] iframe').attr('src','<?php echo site_url() . "/wp-content/plugins/scormcloud/courseconfig.php?courseid=" ?>' + courseid);
    }
    
    jQuery('tr.courseRow[key="'+ courseid + '"]').addClass('active');
    jQuery('tr.propseditor[key="'+ courseid + '"]').fadeIn();

}
);

</script>

	<?php
    }

} else {
    echo "<div>
            <h2>".__("Please configure your SCORM Cloud settings to view your courses.","scormcloud")."</h2>
        </div>";
    echo '<div class="settingsPageLink"><a href="'.site_url().'/wp-admin/admin.php?page=scormcloud/admin/settings"
				title="'.__("Click here to configure your SCORM Cloud plugin.","scormcloud").'">'.__("Click Here to go to the settings page.","scormcloud").'</a></div>';
}

?>



</div>
