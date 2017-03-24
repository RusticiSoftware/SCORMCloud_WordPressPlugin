<?php

require_once( SCORMCLOUD_BASE . 'scormcloudplugin.php' );
require_once( SCORMCLOUD_BASE . 'db/scormclouddatabase.php' );

class ScormCloudRegistrationsWidget extends WP_Widget {
	/**
	 * Declares the UserRegistrationsWidget class.
	 */
	function __construct() {
		$widget_ops  = array(
							'classname'   => 'widget_userreg_widget',
		                      'description' => __( 'Widget for displaying SCORM registrations to users.', 'scormcloud' ),
							);
		$control_ops = array(
							'width' => 200,
							'height' => 300,
							);
		parent::__construct( 'scormcloudregistrations', __( 'Scorm Cloud User Training Widget', 'scormcloud' ), $widget_ops, $control_ops );
	}

	/**
	 * Displays the Widget
	 *
	 * @param array $args widget args.
	 * @param array $instance widget instance.
	 */
	function widget( $args, $instance ) {
		$title     = apply_filters( 'widget_title', empty( $instance['title'] ) ? '&nbsp;' : $instance['title'] );
		$limitregs = isset( $instance['limitregs'] ) ? (bool) $instance['limitregs'] : false;

		// Before the widget.
		echo wp_kses_post( $args['before_widget'] );

		// The title.
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		// Make the widget.
		wp_enqueue_style( 'scormcloud', site_url() . '/wp-content/plugins/scormcloud/css/scormcloud.widget.css' );

		global $current_user;
		global $wpdb;
		wp_get_current_user();

		if ( ! is_user_logged_in() ) {
			echo '<a href="wp-login.php">' . esc_attr__( 'Log in', 'scormcloud' ) . '</a> ' . esc_attr__( 'to see your training history', 'scormcloud' ) . '.' ;
		} else {
			$cloud_service = ScormCloudPlugin::get_cloud_service();
			$registration_service   = $cloud_service->getRegistrationService();

			$invitations_table = ScormCloudDatabase::get_invitations_table();
			$registrations_table = ScormCloudDatabase::get_registrations_table();
			$regs     = $wpdb->get_results( $wpdb->prepare( 'SELECT reg.reg_id, inv.course_title, inv.course_id, inv.active, reg.update_date 
										FROM ' . esc_sql( $registrations_table ) . ' reg 
										JOIN ' . esc_sql( $invitations_table ) . ' inv ON reg.invite_id = inv.invite_id 
										WHERE user_id = %s AND inv.blog_id = %s ORDER BY reg.update_date DESC',
			[ $current_user->ID, $GLOBALS['blog_id'] ] ), OBJECT ); // db call ok; no-cache ok.

			echo '<div class="courselistDiv">';

			if ( count( $regs ) > 0 ) {
				echo '<div class="helpMsg">' . esc_textarea( __( 'Click course title to launch.', 'scormcloud' ) ) . '</div>';
			} else {
				echo '<div class="helpMsg">' . esc_textarea( __( 'You have not taken any training.', 'scormcloud' ) ) . '</div>';
			}
			$courses_displayed = array();
			foreach ( $regs as $reg ) {
				try {
					$reg_id = $reg->reg_id;
					if ( ( $limitregs && in_array( $reg->course_id, $courses_displayed, true ) ) || ! $registration_service->Exists( $reg_id ) ) {
						continue;
					} else {
						$courses_displayed[] = $reg->course_id;

						$registration_result = $registration_service->GetRegistrationResult( $reg_id, 0, 0 );
						$result_xml           = simplexml_load_string( $registration_result );

						$completion = (string) $result_xml->registrationreport->complete;
						$success    = (string) $result_xml->registrationreport->success;
						$seconds    = (string) $result_xml->registrationreport->totaltime;
						$score      = (string) $result_xml->registrationreport->score;

						$course_title = $reg->course_title;
						echo "<div class='usercourseblock'>";
						if ( 1 === (int) $reg->active ) {
							echo  "<a class='courseTitle' href='javascript:void(0);' key='" . esc_attr( $reg_id ) . "' onclick='ScormCloud.Widget.getLaunchURL(\"" . esc_textarea( $reg_id ) . "\",\"Training\");' url='" . esc_url_raw( site_url() ) . "/wp-content/plugins/scormcloud/ajax.php' title='Click to launch course " . esc_textarea( $course_title ) . "'>" . esc_textarea( $course_title ) . '</a>' ;
						} else {
							echo  '<span class="courseTitle" title="' . esc_attr__( 'This course is currently inactive.', 'scormcloud' ) . '">' . esc_attr( $course_title ) . '</span>';
						}

						echo  "<br/><a href='javascript:void(0);' class='toggleButton showDetails' toggleobject='.courselistDiv .regs.courseDetails." . esc_attr( $reg_id ) . "' onText='hide details' offText='show details'>" . esc_textarea( __( 'show details', 'scormcloud' ) ) . '</a>';

						echo "<div class='regs courseDetails " . esc_attr( $reg_id ) . "' >";
						if ( $seconds > 0 ) {
							echo  "<div class=''>" . esc_textarea( __( 'Completion', 'scormcloud' ) ) . ": <span class='" . esc_attr( $completion ) . "'>" . esc_textarea( $completion ) . '</span></div>';
							echo  "<div class=''>" . esc_textarea( __( 'Success', 'scormcloud' ) ) . ": <span class='" . esc_attr( $success ) . "'>" . esc_textarea( $success ) . '</span></div>';
							echo  "<div class=''>" . esc_textarea( __( 'Score', 'scormcloud' ) . ': ' . ( 'unknown' === $score ? '-' : $score . '%' ) ) . '</div>';

							echo  '<div class="time">' . esc_textarea( floor( $seconds / 60 ) . 'min ' . ( $seconds % 60 ) . __( 'sec spent in course', 'scormcloud' ) ) . '</div>';


						} else {
							echo  '<div class="">' . esc_textarea( __( 'Not Started', 'scormcloud' ) ) . '</div>' ;
						}

						echo '</div>';
						echo '</div>';
					}// End if().
				} catch ( Exception $e ) {
					echo '<span>' . esc_textarea( $e->getMessage() ) . '</span>';
				}// End try().
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
	 * @param array $new_instance updated widget instance.
	 * @param array $old_instance original wiget instance.
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance              = $old_instance;
		$instance['title']     = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['limitregs'] = ! empty( $new_instance['limitregs'] ) ? 1 : 0;

		return $instance;
	}

	/**
	 * Creates the edit form for the widget.
	 *
	 * @param array $instance widget instance.
	 *
	 * @return string 'form'.
	 */
	function form( $instance ) {
		// Defaults.
		$instance = wp_parse_args( (array) $instance, array(
															'title' => '',
		) );

		$title     = htmlspecialchars( $instance['title'] );
		$limitregs = isset( $instance['limitregs'] ) ? (bool) $instance['limitregs'] : false;

		// Output the options.
		echo  '<p style="text-align:left;"><label for="' . esc_attr( $this->get_field_name( 'title' ) ) . '">' . esc_textarea( __( 'Title:', 'scormcloud' ) ) . ' <input style="width: 150px;" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $title ) . '" /></label></p>';
		echo  '<p><input type="checkbox" class="checkbox" id="' . esc_attr( $this->get_field_id( 'limitregs' ) ) . '" name="' . esc_attr( $this->get_field_name( 'limitregs' ) ) . '"' . ( $limitregs ? 'checked="checked"' : '' ) . ' />';
		echo  '<label for="' . esc_attr( $this->get_field_id( 'limitregs' ) ) . '"> ' . esc_textarea( __( 'Limit to latest training per course.', 'scormcloud' ) ) . '</label></p>';
		return 'form';
	}

}// END class
?>