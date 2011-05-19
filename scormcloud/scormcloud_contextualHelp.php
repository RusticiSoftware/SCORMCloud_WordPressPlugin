<?php

function scormcloud_contextualHelp($text, $screen_id, $screen){
    
	global $plugin_hooks;
    if (in_array($screen_id,$plugin_hooks) ){

		$helpHtml = "<div class='sc_helpPanel'>";
	    $helpHtml .= "<h2>SCORM Cloud Hints and Tips</h2>";
    
	    if ( isset($_GET['page']) ) {
	        $plugin_page = stripslashes($_GET['page']);
	        $plugin_page = plugin_basename($plugin_page);
	    }   
    
	    switch ($plugin_page){
        
	        case 'scormcloud/scormcloud.php':
	            $helpHtml .= "<p><span class='emph'>Overall Reportage Summary</span> is a results report for all trainings in your wordpress plugin.
	            Note that the results are not reported in real time but are current as of the given date.  You can view the full report in the Reportage site by clicking the 'Scorm Cloud Reportage' link under the page header.";
	            $helpHtml .= "</p>";
	            break;
        
	        case 'scormcloudcourses':
	            $helpHtml .= "<p><span class='emph'>Import a new course</span> provides functionality to upload a course to the SCORM Cloud for use in trainings in wordpress.
	            Files uploaded should be zipped up SCORM or AICC course packages.</p>";
	            $helpHtml .= "<p><span class='emph'>All Courses</span> lists all of the courses available for training in wordpress.  This list comes from the SCORM Cloud, and the courses can also be managed on the SCORM Cloud site.
	            <ul>
	            <li>Click the <span class='emph'>Preview</span> link to launch an untracked preview of the course.  This will launch the full course, but no results will be recorded.</li>
	            <li>Clicking the <span class='emph'>View Course Report</span> link will open the full reportage report for the course in the Reportage Application.</li>
	            <li>Clicking the <span class='emph'>Delete Course</span> link will delete the course from wordpress and from the SCORM Cloud.  Any trainings associated with the course, however,
	            will not be deleted.</li>
	            </ul></p>";
	            break;
        
	        case 'scormcloudtraining':
            
	            if (isset($_GET['inviteid'])){
	                $helpHtml .= "<p>Clicking the <span class='emph'>click to activate/deactivate</span> link will set the training as launchable or not launchable.</p>";
	                $helpHtml .= "<p><span class='emph'>Invitation Details</span> provides a way to modify an existing post/page invitation.  An example of what the invitation will
	                look like in the post is displayed to the right of the edit fields.  This section will not appear for 'Catalog Widget' and 'Quick Create Training' trainings.</p>";
	                $helpHtml .= "<p><span class='emph'>Training Summary</span> is a results report for all learners who have started this training.
	                Note that the results are not reported in real time but are current as of the given date.  You can view the full report in the Reportage site by clicking the 'View Full Results Report' link.";
	                $helpHtml .= "<p><span class='emph>Training History</span> lists all of the learners that have started or have been assigned this training.
	                You can view the full learners report in the Reportage site by clicking the 'View All Learners in Reportage' link.
	                <ul>
	                <li>Click the <span class='emph'>View Details</span> link see a detailed report of a learner's results in Reportage.</li>
	                </ul>
	                </p>";
	            } else {
            
	                $helpHtml .= "<p><span class='emph'>Quick Create Training</span> provides the ability to directly assign a course to existing wordpress users.  These trainings will not appear
	                in a wordpress post or page, but the course will show up in the user's <span class='emph'>SCORM Cloud User Training Widget</span>; so it is important to add that widget to your wordpress blog for the users
	                to be able to launch the assigned courses.  Note that using this functionality to assign courses pre-creates a registration in your SCORM Cloud account for each assigned course and user.</p>";
	                $helpHtml .= "<p><span class='emph'>SCORM Cloud Training History</span> lists all of the trainings that have been created in your wordpress site.
	                <ul>
	                <li>Click the <span class='emph'>Course Title</span> link to go to a training detail page.</li>
	                <li>The <span class='emph'>Post Title</span> shows which page or blog post contains the training launch. Clicking the title will open the edit page for that post.  A non-link 'Catalog Widget' title indicates the training was created and launched
	                from the catalog widget, and the 'User Invitation' title indicates that the training was created in the Quick Create Training section.</li>
	                <li>Clicking the <span class='emph'>Learners</span> link will display a list of the 10 most recent learner results for the course.  To view all of the results, click the linnk at the bottom of the list or
	                click on the course title for that row.</li>
	                <li>Clicking the <span class='emph'>View Results Report</span> link will open the full reportage report for the training in the Reportage Application.</li>
	                <li>Clicking the <span class='emph'>click to activate/deactivate</span> link will set the training as launchable or not launchable.</li>
	                </ul>
	                </p>";
	            }
	            break;
        
	        case 'scormcloudsettings':
	            $helpHtml .= "<p>The <span class='emph'>Cloud Engine URL</span> is set with a default value that does not need to be changed for most users.</p>";
	            $helpHtml .= "<p>The <span class='emph'>App Id</span> and <span class='emph'>Secret Key</span> can both be found by going to your <a href='http://cloud.scorm.com/sc/user/Apps'>Apps Page</a> on the SCORM Cloud site.
	            These values are randomly generated unique strings that tie this wordpress plugin to your SCORM Cloud account.</p>";
	            break;
        
	        default:
	            $helpHtml .= "<p><span class='emph'></span></p>";
	            break;
        
        
	    }
    
	    $helpHtml .= "<hr>";
    
	    $helpHtml .= "<p>The SCORM Cloud plugin requires a SCORM Cloud account that can be created and managed on the <a href='https://cloud.scorm.com/'>SCORM Cloud</a> application site.</p>";
	    $helpHtml .= "<p>The SCORM Cloud is brought to you by <a href='https://www.scorm.com/'>Rustici Software</a>.</p>";
    
    
    
	    $helpHtml .= "</div>";
	    return $helpHtml;
	} else {
		return $text;
	}
}










?>