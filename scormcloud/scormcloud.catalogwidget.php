<?php
	
	class ScormCloudCatalogWidget extends WP_Widget
	{
		/**
		* Declares the UserRegistrationsWidget class.
		*
		*/
		function ScormCloudCatalogWidget(){
			$widget_ops = array('classname' => 'widget_catalog_widget', 'description' => __( "Widget for displaying SCORM Cloud Catalog to users.","scormcloud") );
			$control_ops = array('width' => 200, 'height' => 300);
			$this->WP_Widget('scormcloudcatalog', __("Scorm Cloud Catalog Widget","scormcloud"), $widget_ops, $control_ops);
		}

		/**
		* Displays the Widget
		*
		*/
		function widget($args, $instance){
			extract($args);
			$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
            $requireLogin = isset( $instance['requirelogin'] ) ? (bool) $instance['requirelogin'] : true;
            
            require_once('scormcloud.wp.php');
            $regsRemaining = scormcloud_regsRemaining();
            
			# Before the widget
			echo $before_widget;

			# The title
			if ( $title )
				echo $before_title . $title . $after_title;

			# Make the widget
				
				echo '<link rel="stylesheet" type="text/css" href="'.get_option( 'siteurl' ) . '/wp-content/plugins/scormcloud/css/scormcloud.widget.css" />';
				
			//echo '<h2>SCORM Cloud Courses</h2>';
			global $current_user;
			global $wpdb;
			get_currentuserinfo();

            require_once('scormcloud.wp.php');
            $coursesFilter = (scormcloud_isScormCloudNetworkManaged() && get_site_option('scormcloud_sharecourses') !== 'on') ? $GLOBALS['blog_id']."-.*" : null ;
            $ScormService = scormcloud_getScormEngineService();
            $courseService = $ScormService->getCourseService();
            $courseObjArray = $courseService->GetCourseList($coursesFilter);

			if($requireLogin && !is_user_logged_in())
			{
                
                echo '<a href="wp-login.php">Log in</a> to see the course catalog.';
                
			}
			else
			{
                
                $regService = $ScormService->getRegistrationService();
                
                //echo count($regs);
                echo '<div class="courselistDiv">';
                
                
                echo "<div class='helpMsg'>".__("Click course title to launch.","scormcloud")." <br/><a class='catalogMoreInfo toggleButton' href='javascript:void(0);' toggleobject='.courselistDiv .catalog.moreInfo' onText='".__("hide info","scormcloud")."' offText='".__("more info","scormcloud")."'>".__("more info","scormcloud")."</a></div>";
                echo "<div class='catalog moreInfo'>";
                if($current_user->user_login != '') {
                    echo "<p>".__("If you have taken a course before, your most recent results will display by clicking 'show details' and your most recent training will launch by clicking the course title.","scormcloud")."</p>";
                } else {
                    echo "<p>".__("To launch a course, you must provide a name and email address.  This will allow your training results to be tracked.","scormcloud")."</p>";
                    echo "<p>".__("By registering or logging in, your results will be associated with your user identity and you will be able to see your training results in this widget.","scormcloud")."</p>";
                }
                echo "</div>";
                foreach($courseObjArray as $course) {
                    
                    $courseId = $course->getCourseId();
                    $courseTitle = $course->getTitle();
                    
                    if(isset($current_user->user_login) && $current_user->user_login != '') {
                        $regs = $wpdb->get_results("SELECT reg.reg_id,inv.course_title,inv.course_id, inv.active, reg.update_date FROM ".scormcloud_getDBPrefix()."scormcloudinvitationregs reg
                                               JOIN ".scormcloud_getDBPrefix()."scormcloudinvitations inv ON reg.invite_id = inv.invite_id
                                               WHERE reg.user_id = '".$current_user->ID."' AND inv.course_id = '".$courseId."'
                                               ORDER BY reg.update_date DESC");
                        
                        if (count($regs) > 0){
                            
                            $reg = $regs[0];
                            $regId = $reg->reg_id;
                            $regResultsXmlStr = $regService->GetRegistrationResult($regId,0,0);
                            $resXml = simplexml_load_string($regResultsXmlStr);
                            
                            $completion = $resXml->registrationreport->complete;
                            $success = $resXml->registrationreport->success;
                            $seconds = $resXml->registrationreport->totaltime;
                            $score = $resXml->registrationreport->score;
                            
                            echo "<div class='usercourseblock'>";
                            
							if ($reg->active == 1){
                                echo "<a class='courseTitle' href='javascript:void(0);' key='$regId' onclick='ScormCloud.Widget.getLaunchURL(\"$regId\",\"Catalog\");' url='".get_option( 'siteurl' )."/wp-content/plugins/scormcloud/ajax.php' title='".__("Click to launch course ","scormcloud")."$courseTitle'>$courseTitle</a>";
                            } else {
                                echo "<span class='courseTitle' title='".__("This course is currently inactive.","scormcloud")."'>$courseTitle</span>";
                            }
                            
                            echo "<a href='javascript:void(0);' class='toggleButton showDetails' toggleobject='.courselistDiv .catalog.courseDetails.$regId' onText='".__("hide details","scormcloud")."' offText='".__("show details","scormcloud")."'>".__("show details","scormcloud")."</a>";
                            
                            echo "<div class='catalog courseDetails $regId' >";
                            if($seconds > 0)
                            {
                                echo "<div class=''>".__("Completion","scormcloud").": <span class='$completion'>".__($completion)."</span></div>";
                                echo "<div class=''>".__("Success","scormcloud").": <span class='$success'>".__($success)."</span></div>";
                                echo "<div class=''>".__("Score","scormcloud").": ".($score == "unknown" ? "-" : $score."%")."</div>";
        
                                echo '<div class="time">'.floor($seconds / 60)."min ".($seconds % 60).__("sec spent in course","scormcloud").'</div>';
                                
        
                            }else{
                                echo '<div class="">'.__("Not Started","scormcloud").'</div>';
                            }
							echo "</div>";
                        } else {
                            
                            echo "<div class='usercourseblock'>";
                            if ($regsRemaining > 0){
                                echo "<a class='courseTitle' href='javascript:void(0);' coursetitle='$courseTitle' key='$courseId' onclick='ScormCloud.Widget.getCatalogLaunchURL(\"$courseId\");' url='".get_option( 'siteurl' )."/wp-content/plugins/scormcloud/ajax.php' title='".__("Click to launch course ","scormcloud")."$courseTitle'>$courseTitle</a>";
                            } else {
                                echo "<span class='courseTitle' title='".__("This course is currently inactive.","scormcloud")."'>$courseTitle</span>";    
                            }
                            
                        }
                        
                    
                    } else {
                        echo "<div class='usercourseblock'>";
                        if ($regsRemaining > 0){
                            echo "<a class='courseTitle anonLaunch' href='javascript:void(0);' key='$courseId' title='".__("Click to launch course","scormcloud")." $courseTitle'>$courseTitle</a>";
                            
                            echo "<div class='anonlaunchdiv' key='$courseId'>".__("First Name","scormcloud").":<br/><input name='scormcloudfname' type='text' key='$courseId'/><br/>";
                            echo __("Last Name","scormcloud").":<br/><input name='scormcloudlname' type='text' key='$courseId'/><br/>";
                            echo __("Email","scormcloud").":<br/><input name='scormcloudemail' type='text' key='$courseId'/>";
                            echo "<input name='launch' type='button' class='catalogLaunchBtn' key='$courseId' coursetitle='$courseTitle' onclick='ScormCloud.Widget.getAnonCatalogLaunchURL(\"$courseId\");' url='" .get_option( 'siteurl' ) ."/wp-content/plugins/scormcloud/ajax.php' value='".__("Start Training","scormcloud")."'/>";
                            echo "<div class='launchMessage'>message</div></div>";
                        } else {
                            echo "<span class='courseTitle' title='".__("This course is currently inactive.","scormcloud")."'>$courseTitle</span>";    
                        }
                        
                    }
                    echo "</div>";
                    
                   
                    
			}
			echo '</div>';
			//echo '<script language="javascript">'.$widgetscript.'</script>';
			echo '<script language="javascript" src="'.get_option( 'siteurl' ) . '/wp-content/plugins/scormcloud/scripts/scormcloud.widget.js" >'.'</script>';
			}
			# After the widget
			echo $after_widget;
		}

		/**
		* Saves the widgets settings.
		*
		*/
		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = strip_tags(stripslashes($new_instance['title']));
            $instance['requirelogin'] = !empty($new_instance['requirelogin']) ? 1 : 0;

			return $instance;
		}

		/**
		* Creates the edit form for the widget.
		*
		*/
		function form($instance){
			//Defaults
			$instance = wp_parse_args( (array) $instance, array('title'=>'') );

			$title = htmlspecialchars($instance['title']);
            $requireLogin = isset( $instance['requirelogin'] ) ? (bool) $instance['requirelogin'] : true;

			# Output the options
			echo '<p style="text-align:left;"><label for="' . $this->get_field_name('title') . '">' . __('Title:','scormcloud') . ' <input style="width: 150px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';
            echo '<p><input type="checkbox" class="checkbox" id="'.$this->get_field_id('requirelogin').'" name="'.$this->get_field_name('requirelogin').'"'.( $requireLogin ? 'checked="checked"' : '' ).' />';
            echo '<label for="'.$this->get_field_id('requirelogin').'"> '.__("Require user login","scormcloud").'</label></p>';
            
		}
        

	}// END class
?>