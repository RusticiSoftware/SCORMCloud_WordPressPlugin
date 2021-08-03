<?php

require_once( SCORMCLOUD_BASE . 'scormcloudplugin.php' );
require_once( SCORMCLOUD_BASE . 'db/scormclouddatabase.php' );

class ScormCloudCatalogWidget extends WP_Widget {
	/**
	 * Declares the UserRegistrationsWidget class.
	 */
	function __construct() {
		$widget_ops  = array(
			'classname'   => 'widget_catalog_widget',
			'description' => __( 'Widget for displaying SCORM Cloud Catalog to users.', 'scormcloud' ),
		);
		$control_ops = [
			'width' => 200,
			'height' => 300,
		];

		parent::__construct( 'scormcloudcatalog', __( 'Scorm Cloud Catalog Widget', 'scormcloud' ), $widget_ops, $control_ops );
	}

	/**
	 * Displays the Widget
	 *
	 * @param array $args widget args.
	 * @param array $instance widget instance.
	 */
	function widget( $args, $instance ) {
		$title        = apply_filters( 'widget_title', empty( $instance['title'] ) ? '&nbsp;' : $instance['title'] );
		$require_login = isset( $instance['requirelogin'] ) ? (bool) $instance['requirelogin'] : true;

		$remaining_registrations = ScormCloudPlugin::remaining_registrations();

		// Before the widget.
		echo wp_kses_post( $args['before_widget'] );

		// The title.
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		// Make the widget.
		wp_enqueue_style( 'scormcloud',  site_url() . '/wp-content/plugins/scormcloud/css/scormcloud.widget.css' );

		global $current_user;
		global $wpdb;
		wp_get_current_user();

		$courses_filter  = ( ScormCloudPlugin::is_network_managed() && (int) get_site_option( 'scormcloud_sharecourses' ) !== 1 ) ? $GLOBALS['blog_id'] . '-.*' : null;
		$cloud_service   = ScormCloudPlugin::get_cloud_service();
		$course_service  = $cloud_service->getCourseService();
		$course_list = $course_service->GetCourseList( $courses_filter );

		if ( $require_login && ! is_user_logged_in() ) {

			echo '<a href="wp-login.php">Log in</a> to see the course catalog.';

		} else {

			$registration_service = $cloud_service->getRegistrationService();

			echo '<div class="courselistDiv">';
			echo "<div class='helpMsg'>" . esc_textarea( __( 'Click course title to launch.', 'scormcloud' ) ) . " <br/><a class='catalogMoreInfo toggleButton' href='javascript:void(0);' toggleobject='.courselistDiv .catalog.moreInfo' onText='" . esc_attr( __( 'hide info', 'scormcloud' ) ) . "' offText='" . esc_attr( __( 'more info', 'scormcloud' ) ) . "'>" . esc_textarea( __( 'more info', 'scormcloud' ) ) . '</a></div>';
			echo "<div class='catalog moreInfo'>";
			if ( '' !== $current_user->user_login ) {
				echo '<p>' . esc_textarea( __( 'If you have taken a course before, your most recent results will display by clicking \'show details\' and your most recent training will launch by clicking the course title.', 'scormcloud' ) ) . '</p>';
			} else {
				echo '<p>' . esc_textarea( __( 'To launch a course, you must provide a name and email address.  This will allow your training results to be tracked.', 'scormcloud' ) ) . '</p>';
				echo '<p>' . esc_textarea( __( 'By registering or logging in, your results will be associated with your user identity and you will be able to see your training results in this widget.', 'scormcloud' ) ) . '</p>';
			}
			echo '</div>';
			foreach ( $course_list as $course ) {

				$course_id    = $course->getCourseId();
				$course_title = $course->getTitle();

				if ( isset( $current_user->user_login ) && '' !== $current_user->user_login ) {
					$invitations_table = ScormCloudDatabase::get_invitations_table();
					$registrations_table = ScormCloudDatabase::get_registrations_table();
					$reg      = $wpdb->get_row( $wpdb->prepare( 'SELECT reg.reg_id, inv.course_title, inv.course_id, inv.active, reg.update_date 
																FROM ' . esc_sql( $registrations_table ) . ' reg
                                                                JOIN ' . esc_sql( $invitations_table ) . ' inv 
                                                                    ON reg.invite_id = inv.invite_id
                                                                WHERE reg.user_id = %s AND inv.course_id = %s
                                                                ORDER BY reg.update_date DESC',
					array( $current_user->ID, $course_id ), OBJECT ));// db call ok; no-cache ok.

					if ( null !== $reg ) {
						$reg_id            = $reg->reg_id;
						$registration_result = $registration_service->GetRegistrationResult( $reg_id, 0, 0 );
						$res_xml           = simplexml_load_string( $registration_result );

						$completion = (string) $res_xml->registrationreport->complete;
						$success    = (string) $res_xml->registrationreport->success;
						$seconds    = (string) $res_xml->registrationreport->totaltime;
						$score      = (string) $res_xml->registrationreport->score;

						echo "<div class='usercourseblock'>";

						if ( 1 === (int) $reg->active ) {
							echo "<a class='courseTitle' href='javascript:void(0);' key='" . esc_attr( $reg_id ) . "' onclick='ScormCloud.Widget.getLaunchURL(\"" . esc_js( $reg_id ) . "\",\"Catalog\");' url='" . esc_url_raw( site_url() ) . "/wp-content/plugins/scormcloud/ajax.php' title='" . esc_textarea( __( 'Click to launch course ', 'scormcloud' ) ) . esc_textarea( $course_title ) . "'>" . esc_textarea( $course_title ) . '</a>';
						} else {
							echo "<span class='courseTitle' title='" . esc_attr__( 'This course is currently inactive.', 'scormcloud' ) . "'>" . esc_attr( $course_title ) . '</span>';
						}

						echo "<br/><a href='javascript:void(0);' class='toggleButton showDetails' toggleobject='.courselistDiv .catalog.courseDetails." . esc_attr( $reg_id ) . "' onText='" . esc_attr__( 'hide details', 'scormcloud' ) . "' offText='" . esc_attr__( 'show details', 'scormcloud' ) . "'>" . esc_attr__( 'show details', 'scormcloud' ) . '</a>';

						echo "<div class='catalog courseDetails " . esc_attr( $reg_id ) . "' >";
						if ( $seconds > 0 ) {
							echo "<div class=''>" . esc_attr__( 'Completion', 'scormcloud' ) . ": <span class='" . esc_attr( $completion ) . '>' . esc_attr( $completion ) . '</span></div>';
							echo "<div class=''>" . esc_attr__( 'Success', 'scormcloud' ) . ": <span class='" . esc_attr( $success ) . "'>" . esc_attr( $success ) . '</span></div>';
							echo "<div class=''>" . esc_textarea( __( 'Score', 'scormcloud' ) . ': ' . ( 'unknown' === $score ? '-' : $score . '%' ) ) . '</div>';

							echo '<div class="time">' . esc_textarea( floor( $seconds / 60 ) . 'min ' . ( $seconds % 60 ) . __( 'sec spent in course', 'scormcloud' ) ) . '</div>';


						} else {
							echo '<div class="">' . esc_attr__( 'Not Started', 'scormcloud' ) . '</div>';
						}
						echo '</div>';
					} else {
						echo "<div class='usercourseblock'>";
						if ( $remaining_registrations > 0 ) {
							echo "<a class='courseTitle' href='javascript:void(0);' coursetitle='" . esc_attr( $course_title ) . "' key='" . esc_attr( $course_id ) . "' onclick='ScormCloud.Widget.getCatalogLaunchURL(\"" . esc_attr( $course_id ) . "\");' url='" . esc_url_raw( site_url() ) . "/wp-content/plugins/scormcloud/ajax.php' title='" . esc_attr__( 'Click to launch course ', 'scormcloud' ) . esc_attr( $course_title ) . "'>" . esc_attr( $course_title ) . '</a>';
						} else {
							echo "<span class='courseTitle' title='" . esc_attr__( 'This course is currently inactive.', 'scormcloud' ) . "'>" . esc_attr( $course_title ) . '</span>';
						}
					}// End if().
				} else {
					echo "<div class='usercourseblock'>";
					if ( $remaining_registrations > 0 ) {
						echo "<a class='courseTitle anonLaunch' href='javascript:void(0);' key='" . esc_attr( $course_id ) . "' title='" . esc_attr__( 'Click to launch course', 'scormcloud' ) . esc_attr( $course_title ) . "'>" . esc_attr( $course_title ) . '</a>';

						echo "<div class='anonlaunchdiv' key='" . esc_attr( $course_id ) . "'>" . esc_attr__( 'First Name', 'scormcloud' ) . ":<br/><input name='scormcloudfname' type='text' key='" . esc_attr( $course_id ) . "'/><br/>";
						echo esc_attr__( 'Last Name', 'scormcloud' ) . ":<br/><input name='scormcloudlname' type='text' key='" . esc_attr( $course_id ) . "'/><br/>";
						echo esc_attr__( 'Email', 'scormcloud' ) . ":<br/><input name='scormcloudemail' type='text' key='" . esc_attr( $course_id ) . "'/>";
						echo "<input name='launch' type='button' class='catalogLaunchBtn' key='" . esc_attr( $course_id ) . "' coursetitle='" . esc_attr( $course_title ) . "' onclick='ScormCloud.Widget.getAnonCatalogLaunchURL(\"" . esc_attr( $course_id ) . "\");' url='" . esc_url_raw( site_url() ) . "/wp-content/plugins/scormcloud/ajax.php' value='" . esc_attr__( 'Start Training', 'scormcloud' ) . "'/>";
						echo "<div class='launchMessage'>message</div></div>";
					} else {
						echo "<span class='courseTitle' title='" . esc_attr__( 'This course is currently inactive.', 'scormcloud' ) . "'>" . esc_attr( $course_title ) . '</span>';
					}
				}// End if().
				echo '</div>';
			}// End foreach().
			echo '</div>';
			wp_enqueue_script( 'scormcloud-widget' , plugins_url( '/../scripts/scormcloud.widget.js',  __FILE__ ) );
		}// End if().
		// After the widget.
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Saves the widgets settings.
	 *
	 * @param array $new_instance new widget instance.
	 * @param array $old_instance old widget instance.
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance                 = $old_instance;
		$instance['title']        = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['requirelogin'] = ! empty( $new_instance['requirelogin'] ) ? 1 : 0;

		return $instance;
	}

	/**
	 * Creates the edit form for the widget.
	 *
	 * @param array $instance widget instance.
	 *
	 * @return string
	 */
	function form( $instance ) {
		// Defaults.
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
		) );

		$title        = htmlspecialchars( $instance['title'] );
		$require_login = isset( $instance['requirelogin'] ) ? (bool) $instance['requirelogin'] : true;

		// Output the options.
		echo '<p style="text-align:left;"><label for="' . esc_attr( $this->get_field_name( 'title' ) ) . '">' . esc_attr__( 'Title:', 'scormcloud' ) . ' <input style="width: 150px;" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $title ) . '" /></label></p>';
		echo '<p><input type="checkbox" class="checkbox" id="' . esc_attr( $this->get_field_id( 'requirelogin' ) ) . '" name="' . esc_attr( $this->get_field_name( 'requirelogin' ) ) . '"' . ( $require_login ? 'checked="checked"' : '' ) . ' />';
		echo '<label for="' . esc_attr( $this->get_field_id( 'requirelogin' ) ) . '"> ' . esc_attr__( 'Require user login', 'scormcloud' ) . '</label></p>';
		return 'form';

	}


}// END class
?>
