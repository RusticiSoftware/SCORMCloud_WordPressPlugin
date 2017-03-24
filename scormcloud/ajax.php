<?php
if ( defined( 'ABSPATH' ) ) {
	require_once( ABSPATH . 'wp-load.php' );
} else {
	require_once( '../../../wp-load.php' );
}
require_once( ABSPATH . 'wp-admin/includes/admin.php' );

require_once( SCORMCLOUD_BASE . 'scormcloudplugin.php' );
require_once( SCORMCLOUD_BASE . 'scormcloudemailer.php' );
require_once( SCORMCLOUD_BASE . 'db/scormclouddatabase.php' );

$cloud_service          = ScormCloudPlugin::get_cloud_service();
$action = get_post_arg_as_string( 'action' );

global $current_user;
wp_get_current_user();

switch ( $action ) {
	case 'addregistration':

		$invite_id   = uniqid();
		$app_id       = ScormCloudPlugin::get_wp_option( 'scormcloud_appid' );
		$post_id      = '__direct_invite__';
		$course_id    = get_post_arg_as_string( 'courseid' );
		$course_title = get_post_arg_as_string( 'coursetitle' );
		$send_email   = get_post_arg_as_string( 'sendemail' );
		$user_id_strings = get_post_arg_as_string( 'userids' );
		$all_users   = get_post_arg_as_string( 'allusers' );
		$roles      = get_post_arg_as_string( 'roles' );

		$header      = '';
		$description = '';

		$require_login    = 1;
		$show_course_info = 0;

		$inserted = $wpdb->insert( ScormCloudDatabase::get_invitations_table(),
			array(
				'invite_id'        => $invite_id,
				'blog_id'          => $GLOBALS['blog_id'],
				'app_id'           => $app_id,
				'post_id'          => $post_id,
				'course_id'        => $course_id,
				'course_title'     => $course_title,
				'header'           => $header,
				'description'      => $description,
				'require_login'    => $require_login,
				'show_course_info' => $show_course_info,
			),
		array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ) );// db call ok; no-cache ok.

		if ( ! $inserted ) {
			echo 'Could not insert invitation record.';
			break;
		}

		// create the cloud registration(s).
		$users = array();

		if ( isset( $all_users ) ) {
			$users = get_users();

		} elseif ( isset( $user_id_strings ) ) {
			$user_ids       = explode( ',', $user_id_strings );
			$wp_user_search = new WP_User_Query( array(
				'include' => $user_ids,
			) );
			$users          = $wp_user_search->get_results();

		} elseif ( isset( $roles ) ) {
			$role_array = explode( ',', $roles );
			foreach ( $role_array as $this_role ) {
				$wp_user_search = new WP_User_Query( array(
					'role' => $this_role,
				) );
				$users = array_merge( $users, $wp_user_search->get_results() );
			}
			$users = array_unique( $users, SORT_REGULAR );
		}

		$response_string = 'success';
		foreach ( $users as $user ) {
			$user_data = get_userdata( $user->ID );
			$user_first_name = $user_data->user_firstname;
			if ( ! ( $user_first_name ) || strlen( $user_first_name ) < 1 ) {
				$user_first_name = $user_data->display_name;
			}
			$user_last_name = $user_data->user_lastname;
			if ( ! ( $user_last_name ) || strlen( $user_last_name ) < 1 ) {
				$user_last_name = $user_data->display_name;
			}


			$reg_id   = $invite_id . '-' . uniqid();
			$registration_service = $cloud_service->getRegistrationService();
			$response = $registration_service->CreateRegistration( $reg_id, $course_id, $user_data->user_email, $user_first_name, $user_last_name, $user_data->user_email );

			$xml = simplexml_load_string( $response );
			if ( isset( $xml->success ) ) {
				$wpdb->insert( ScormCloudDatabase::get_registrations_table(),
					array(
						'invite_id'  => $invite_id,
						'reg_id'     => $reg_id,
						'user_id'    => $user_data->ID,
						'user_email' => $user_data->user_email,
					),
				array( '%s', '%s', '%d', '%s' ) );// db call ok; no-cache ok.

				if ( $send_email ) {
					$display_name = $user_data->display_name;
					$message      = "<p>Hello $display_name,</p>";
					$message .= '<p>' . $current_user->display_name . " has invited you to take the training '$course_title'." . ' You can view all available trainings by visiting the site <a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'name' ) . '</a>.</p>';
					$message .= '<p>This email was automatically sent by the SCORM Cloud plugin for WordPress.</p>';

					ScormCloudEmailer::send_email( $user_data, 'Training Invitation', $message );
				}
			} elseif ( '4' === (string) $xml->err['code'] ) {
				$response_string = 'There was a problem creating a new training. The maximum number of registrations for this account has been reached.';
			} else {
				$response_string = 'There was a problem creating a new training. ' . $xml->err['msg'];
			}
		}// End foreach().
		echo esc_textarea( $response_string );

		break;
	case 'addPostInvite':

		$invite_id        = uniqid();
		$app_id           = ScormCloudPlugin::get_wp_option( 'scormcloud_appid' );
		$course_id        = get_post_arg_as_string( 'courseid' );
		$course_title     = get_post_arg_as_string( 'coursetitle' );
		$header           = get_post_arg_as_string( 'header' );
		$description      = get_post_arg_as_string( 'description' );
		$require_login    = get_post_arg_as_string( 'requirelogin' );
		$show_course_info = get_post_arg_as_string( 'showcourseinfo' );

		$wpdb->insert( ScormCloudDatabase::get_invitations_table(),
			array(
				'invite_id'        => $invite_id,
				'blog_id'          => $GLOBALS['blog_id'],
				'app_id'           => $app_id,
				'course_id'        => $course_id,
				'course_title'     => $course_title,
				'header'           => $header,
				'description'      => $description,
				'require_login'    => $require_login,
				'show_course_info' => $show_course_info,
			),
		array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ) );// db call ok; no-cache ok.

		echo esc_textarea( $invite_id );

		break;

	case 'updatePostInvite':

		$invite_id        = get_post_arg_as_string( 'inviteid' );
		$header           = get_post_arg_as_string( 'header' );
		$description      = get_post_arg_as_string( 'description' );
		$require_login    = get_post_arg_as_string( 'requirelogin' );
		$show_course_info = get_post_arg_as_string( 'showcourseinfo' );

		$wpdb->update( ScormCloudDatabase::get_invitations_table(),
			array(
				'header'           => $header,
				'description'      => $description,
				'require_login'    => (int) $require_login,
				'show_course_info' => (int) $show_course_info,
			),
			array(
			'invite_id' => $invite_id,
			),
		array( '%s', '%s', '%d', '%d' ) );// db call ok; no-cache ok.

		break;

	case 'addAnonRegGetLaunchUrl':

		$user_first_name = get_post_arg_as_string( 'fname' );
		$user_last_name  = get_post_arg_as_string( 'lname' );
		$user_email      = get_post_arg_as_string( 'email' );
		$invite_id       = get_post_arg_as_string( 'inviteid' );
		$return_url       = get_post_arg_as_string( 'returnurl' );

		$invite = ScormCloudDatabase::get_invitation( $invite_id );

		$app_id = $invite->app_id;
		$course_id = $invite->course_id;

		$course_tags = '';
		if ( '__direct_invite__' !== $invite->post_id && '__catalog_widget__' !== $invite->post_id ) {
			$post_categories = get_the_category( $invite->post_id );
			if ( is_array( $post_categories ) ) {
				foreach ( $post_categories as $category ) {
					$course_tags .= ',' . $category->cat_name;
				}
			}
			$post_tags = get_the_tags( $invite->post_id );
			if ( is_array( $post_tags ) ) {
				foreach ( $post_tags as $tag ) {
					$course_tags .= ',' . $tag->name;
				}
			}
		}


		$reg_tags = $GLOBALS['blog_id'] . ',' . $invite_id . $course_tags;

		if ( strlen( $course_tags ) > 0 ) {
			$course_tags = substr( $course_tags, 1 );
		}

		$learner_tags = 'anonymous';

		$registration_service = $cloud_service->getRegistrationService();

		$invite_reg = ScormCloudDatabase::get_invitation_reg( array(
																'invite_id'  => $invite_id,
																'user_email' => $user_email,
		) );

		if ( null !== $invite_reg ) {
			$reg_id = (string) $invite_reg->reg_id;
		} else {
			$reg_id = $invite_id . '-' . uniqid();

			// create the cloud registration.
			$registration_service->CreateRegistration( $reg_id, $course_id, $user_email, $user_first_name, $user_last_name, $user_email );

			$wpdb->insert( ScormCloudDatabase::get_registrations_table(),
				array(
					'invite_id'  => $invite_id,
					'reg_id'     => $reg_id,
					'user_email' => $user_email,
			) );// db call ok; no-cache ok.
		}


		if ( function_exists( 'bp_activity_add' ) ) {
			global $bp;

			if ( '__direct_invite__' !== $invite->post_id && '__catalog_widget__' !== $invite->post_id ) {
				$this_post      = get_post( $invite->post_id );
				$post_permalink = get_permalink( $this_post->ID );
				$action_str      = sprintf( '%s launched course "%s" from post %s', $user_first_name . ' ' . $user_last_name, $invite->course_title, '<a href="' . $post_permalink . '">' . $this_post->post_title . '</a>' );
			} else {
				$action_str = sprintf( $user_first_name . ' ' . $user_last_name, $from_user_link, $invite->course_title );
			}

			$activity_args = array(
				'action'  => $action_str,
				// The activity action - e.g. "Jon Doe posted an update".
				'content' => '',
				// Optional: The content of the activity item e.g. "BuddyPress is awesome guys!".
				'component' => 'scormcloud',
				// The name/ID of the component e.g. groups, profile, mycomponent.
				'type' => 'training_launch',
				// The activity type e.g. activity_update, profile_updated.
				'primary_link' => '',
				// Optional: The primary URL for this item in RSS feeds (defaults to activity permalink).
				'user_id'           => false,
				// Optional: The user to record the activity for, can be false if this activity is not for a user.
				'item_id'           => false,
				// Optional: The ID of the specific item being recorded, e.g. a blog_id.
				'secondary_item_id' => false,
				// Optional: A second ID used to further filter e.g. a comment_id.
				'recorded_time'     => gmdate( 'Y-m-d H:i:s' ),
				// The GMT time that this activity was recorded.
				'hide_sitewide'     => false,
				// Should this be hidden on the sitewide activity stream?
			);
			$bp_activity_id  = bp_activity_add( $activity_args );
		}
		$css_url = ScormCloudPlugin::get_wp_option( 'scormcloud_player_cssurl' );

		echo esc_url_raw( $registration_service->GetLaunchUrl( $reg_id, $return_url, $css_url, null, $course_tags, $learner_tags, $reg_tags ) );

		break;

	case 'addUserRegGetLaunchUrl':
		$invite_id = get_post_arg_as_string( 'inviteid' );
		$return_url = get_post_arg_as_string( 'returnurl' );

		$reg_id = $invite_id . '-' . uniqid();

		global $current_user;
		global $wpdb;
		wp_get_current_user();

		$user_email = $current_user->user_email;
		$user_first_name = $current_user->user_firstname;
		$user_last_name = $current_user->user_lastname;
		if ( ! ( $user_first_name ) || strlen( $user_first_name ) < 1 ) {
			$user_first_name = $current_user->display_name;
		}
		if ( ! ( $user_last_name ) || strlen( $user_last_name ) < 1 ) {
			$user_last_name = $current_user->display_name;
		}

		$invite = ScormCloudDatabase::get_invitation( $invite_id );

		$course_id = $invite->course_id;

		$course_tags = '';
		if ( '__direct_invite__' !== $invite->post_id && '__catalog_widget__' !== $invite->post_id ) {
			$post_categories = get_the_category( $invite->post_id );
			if ( is_array( $post_categories ) ) {
				foreach ( $post_categories as $category ) {
					$course_tags .= ',' . $category->cat_name;
				}
			}
			$post_tags = get_the_tags( $invite->post_id );
			if ( is_array( $post_tags ) ) {
				foreach ( $post_tags as $tag ) {
					$course_tags .= ',' . $tag->name;
				}
			}
		}

		$reg_tags = $GLOBALS['blog_id'] . ',' . $invite_id . $course_tags;

		if ( strlen( $course_tags ) > 0 ) {
			$course_tags = substr( $course_tags, 1 );
		}

		$learner_tags = isset( $current_user->roles[0] ) ? $current_user->roles[0] : 'anonymous';

		// create the cloud registration.
		$registration_service = $cloud_service->getRegistrationService();
		$registration_service->CreateRegistration( $reg_id, $course_id, $user_email, $user_first_name, $user_last_name, $user_email );

		$wpdb->insert( ScormCloudDatabase::get_registrations_table(),
			array(
				'invite_id'  => $invite_id,
				'reg_id'     => $reg_id,
				'user_id'    => $current_user->ID,
				'user_email' => $user_email,
			),
		array( '%s', '%s', '%d', '%s' ) );// db call ok; no-cache ok.

		if ( function_exists( 'bp_activity_add' ) ) {
			global $bp;

			$from_user_link = bp_core_get_userlink( $bp->loggedin_user->id );
			if ( '__direct_invite__' !== $invite->post_id && '__catalog_widget__' !== $invite->post_id ) {
				$this_post      = get_post( $invite->post_id );
				$post_permalink = get_permalink( $this_post->ID );
				$action_str      = sprintf( '%s launched course "%s" from post %s', $from_user_link, $invite->course_title, '<a href="' . $post_permalink . '">' . $this_post->post_title . '</a>' );
			} else {
				$action_str = sprintf( '%s launched course "%s"', $from_user_link, $invite->course_title );
			}

			$activity_args = array(
				'action'  => $action_str,
				// The activity action - e.g. "Jon Doe posted an update".
				'content' => '',
				// Optional: The content of the activity item e.g. "BuddyPress is awesome guys!".
				'component' => 'scormcloud',
				// The name/ID of the component e.g. groups, profile, mycomponent.
				'type' => 'training_launch',
				// The activity type e.g. activity_update, profile_updated.
				'primary_link' => '',
				// Optional: The primary URL for this item in RSS feeds (defaults to activity permalink).
				'user_id'           => $bp->loggedin_user->id,
				// Optional: The user to record the activity for, can be false if this activity is not for a user.
				'item_id'           => false,
				// Optional: The ID of the specific item being recorded, e.g. a blog_id.
				'secondary_item_id' => false,
				// Optional: A second ID used to further filter e.g. a comment_id.
				'recorded_time'     => gmdate( 'Y-m-d H:i:s' ),
				// The GMT time that this activity was recorded.
				'hide_sitewide'     => false,
				// Should this be hidden on the sitewide activity stream?
			);
			$bp_activity_id  = bp_activity_add( $activity_args );
		}// End if().
		$css_url = ScormCloudPlugin::get_wp_option( 'scormcloud_player_cssurl' );

		echo esc_url_raw( $registration_service->GetLaunchUrl( $reg_id, $return_url, $css_url, null, $course_tags, $learner_tags, $reg_tags ) );

		break;

	case 'getLaunchUrl':

		global $current_user;
		global $wpdb;
		wp_get_current_user();

		$reg_id = get_post_arg_as_string( 'regid' );
		$return_url  = get_post_arg_as_string( 'returnurl' );
		$widget_name = get_post_arg_as_string( 'widgetname' );

		$invite_reg = ScormCloudDatabase::get_invitation_reg( $reg_id );

		$reg_tags = $GLOBALS['blog_id'] . ',' . (string) $invite_reg->invite_id;

		$learner_tags = isset( $current_user->roles[0] ) ? $current_user->roles[0] : 'anonymous';

		$registration_service = $cloud_service->getRegistrationService();

		if ( function_exists( 'bp_activity_add' ) ) {
			global $bp;
			$invite = ScormCloudDatabase::get_invitation( $invite_reg->invite_id );


			$from_user_link = bp_core_get_userlink( $bp->loggedin_user->id );

			if ( isset( $widget_name ) ) {
				$action_str = sprintf( '%s launched course "%s" from the %s widget', $from_user_link, $invite->course_title, $widget_name );
			} elseif ( '__direct_invite__' !== $invite->post_id && '__catalog_widget__' !== $invite->post_id ) {
				$this_post      = get_post( $invite->post_id );
				$post_permalink = get_permalink( $this_post->ID );
				$action_str      = sprintf( '%s launched course "%s" from post %s', $from_user_link, $invite->course_title, '<a href="' . $post_permalink . '">' . $this_post->post_title . '</a>' );
			} else {
				$action_str = sprintf( '%s launched course "%s"', $from_user_link, $invite->course_title );
			}
			$activity_args = array(
				'action'  => $action_str,
				// The activity action - e.g. "Jon Doe posted an update".
				'content' => '',
				// Optional: The content of the activity item e.g. "BuddyPress is awesome guys!".
				'component' => 'scormcloud',
				// The name/ID of the component e.g. groups, profile, mycomponent.
				'type' => 'training_launch',
				// The activity type e.g. activity_update, profile_updated.
				'primary_link' => '',
				// Optional: The primary URL for this item in RSS feeds (defaults to activity permalink).
				'user_id'           => $bp->loggedin_user->id,
				// Optional: The user to record the activity for, can be false if this activity is not for a user.
				'item_id'           => false,
				// Optional: The ID of the specific item being recorded, e.g. a blog_id.
				'secondary_item_id' => false,
				// Optional: A second ID used to further filter e.g. a comment_id.
				'recorded_time'     => gmdate( 'Y-m-d H:i:s' ),
				// The GMT time that this activity was recorded.
				'hide_sitewide'     => false,
				// Should this be hidden on the sitewide activity stream?.
			);
			$bp_activity_id  = bp_activity_add( $activity_args );
		}// End if().
		$css_url = ScormCloudPlugin::get_wp_option( 'scormcloud_player_cssurl' );

		echo esc_url_raw( $registration_service->GetLaunchUrl( $reg_id, $return_url, $css_url, null, null, $learner_tags, $reg_tags ) );

		break;

	case 'getPropertiesEditorUrl':
		$course_id     = get_post_arg_as_string( 'courseid' );
		$course_service = $cloud_service->getCourseService();
		$cssurl        = site_url() . '/wp-content/plugins/scormcloud/css/scormcloud.ppeditor.css';

		echo esc_url_raw( $course_service->GetPropertyEditorUrl( $course_id, $cssurl, null ) );

		break;

	case 'getPreviewUrl':
		$course_id = get_post_arg_as_string( 'courseid' );
		$return_url = get_post_arg_as_string( 'returnurl' );

		$css_url = ScormCloudPlugin::get_wp_option( 'scormcloud_player_cssurl' );

		$course_service = $cloud_service->getCourseService();
		echo esc_url_raw( $course_service->GetPreviewUrl( $course_id, $return_url, $css_url ) );

		break;

	case 'deletecourse':
		$course_id = get_post_arg_as_string( 'courseid' );

		$inv_table = ScormCloudDatabase::get_invitations_table();
		$reg_table = ScormCloudDatabase::get_registrations_table();
		$wpdb->query( $wpdb->prepare( "DELETE r FROM $inv_table AS i LEFT JOIN $reg_table AS r ON i.invite_id = r.invite_id WHERE course_id = %s", [ $course_id ] ) );// db call ok; no-cache ok.
		$wpdb->query( $wpdb->prepare( "DELETE FROM $inv_table WHERE course_id = %s", [ $course_id ] ) );// db call ok; no-cache ok.

		$course_service = $cloud_service->getCourseService();
		echo esc_textarea( $course_service->DeleteCourse( $course_id ) );

		break;

	case 'getCourseReportUrl':
		$course_id  = get_post_arg_as_string( 'courseid' );
		$reportage_service = $cloud_service->getReportingService();
		$reportage_auth    = $reportage_service->GetReportageAuth( 'FREENAV', true );
		echo esc_url_raw( $reportage_service->LaunchCourseReport( $reportage_auth, $course_id ) );

		break;

	case 'getRegReportUrl';
		$invite_id = get_post_arg_as_string( 'inviteid' );
		$registration_id    = get_post_arg_as_string( 'regid' );

		$inv_table = ScormCloudDatabase::get_invitations_table();
		$reg_table = ScormCloudDatabase::get_registrations_table();

		$invite = $wpdb->get_row( $wpdb->prepare( "SELECT inv.course_id, reg.user_email FROM $inv_table inv
        	JOIN $reg_table reg ON inv.invite_id = reg.invite_id
        	WHERE reg.invite_id = %s AND reg.reg_id = %s AND inv.app_id = %s", array(
			$invite_id,
			$registration_id,
			ScormCloudPlugin::get_wp_option( 'scormcloud_appid' ),
		) ), OBJECT );// db call ok; no-cache ok.

		$course_id = $invite->course_id;
		$user_id   = rawurlencode( $invite->user_email );

		$reportage_service = $cloud_service->getReportingService();
		$reportage_auth           = $reportage_service->GetReportageAuth( 'FREENAV', true );

		$reportage_service_url = $reportage_service->GetReportageServiceUrl();
		$reportage_url          = $reportage_service_url . 'Reportage/reportage.php?appId=' . $cloud_service->getAppId() . '&registrationId=$regId';
		$reportage_url .= "&courseId=$course_id";
		$reportage_url .= "&learnerId=$user_id";
		echo esc_url_raw( $reportage_service->GetReportUrl( $reportage_auth, $reportage_url ) );

		break;

	case 'getInviteReportUrl':
		$invite_id    = get_post_arg_as_string( 'inviteid' );


		$reportage_service = $cloud_service->getReportingService();
		$reportage_auth           = $reportage_service->GetReportageAuth( 'FREENAV', true );

		$reportage_service_url = $reportage_service->GetReportageServiceUrl();
		$reportage_url          = $reportage_service_url . 'Reportage/reportage.php?appId=' . $cloud_service->getAppId() . '&registrationTags=$invite_id|_all';
		echo esc_url_raw( $reportage_service->GetReportUrl( $reportage_auth, $reportage_url ) );

		break;

	case 'getRegistrations':

		$invite_id = get_post_arg_as_string( 'inviteid' );

		$inv_table = ScormCloudDatabase::get_invitations_table();
		$reg_table   = ScormCloudDatabase::get_registrations_table();

		$invite_regs = $wpdb->get_results( $wpdb->prepare( "SELECT reg.*, inv.course_id 
									FROM $reg_table reg 
									JOIN $inv_table inv
        						 		ON reg.invite_id = inv.invite_id
        						 	WHERE reg.invite_id = %s AND inv.app_id = %s ORDER BY reg.update_date DESC LIMIT 10", [
			$invite_id,
			ScormCloudPlugin::get_wp_option( 'scormcloud_appid' ),
		] ), OBJECT );// db call ok; no-cache ok.

		$registration_service = $cloud_service->getRegistrationService();
		$registration_xml_string  = $registration_service->GetRegistrationListResults( $invite_regs[0]->course_id, null, 0 );

		$regs_xml = simplexml_load_string( $registration_xml_string );
		$reg_list  = $regs_xml->registrationlist;


		echo '<table class="widefat" cellspacing="0" id="InvitationListTable" >';
		echo '<thead>';
		echo '<tr class="thead"><th class="manage-column">User</th>
            <th class="manage-column">Completion</th>
            <th class="manage-column">Success</th>
            <th class="manage-column">Score</th>
            <th class="manage-column">Time</th>
            <th class="manage-column"></th></tr></thead>';
		foreach ( $invite_regs as $invite_reg ) {
			$reg_result = $reg_list->xpath( "//registration[@id='" . $invite_reg->reg_id . "']" );
			if ( count( $reg_result ) > 0 ) {
				$reg_report = $reg_result[0]->registrationreport;

				echo "<tr key='" . esc_attr( $invite_reg->reg_id ) . "'>";
				$user_id = $invite_reg->user_id;
				if ( $user_id ) {
					$wp_user = get_userdata( $user_id );
					echo '<td>' . esc_textarea( $wp_user->display_name ) . '</td>';
				} else {
					echo '<td>' . esc_textarea( $invite_reg->user_email ) . '</td>';
				}

				echo '<td class="' . esc_attr( $reg_report->complete ) . '"">' . esc_textarea( $reg_report->complete ) . '</td>';
				echo '<td class="' . esc_attr( $reg_report->success ) . '"">' . esc_textarea( $reg_report->success ) . '</td>';
				$score = (string) $reg_report->score;
				echo '<td>' . esc_textarea( 'unknown' === $score ? '-' : $score . '%' ) . '</td>';
				$seconds = $reg_report->totaltime;
				echo '<td>' . esc_textarea( floor( $seconds / 60 ) . 'min ' . ( $seconds % 60 ) ) . 'sec</td>';
				echo "<td><a href='javascript:void(0);' class='viewRegDetails' onclick='Scormcloud_loadRegReport(\"" . esc_js( $invite_reg->invite_id ) . '","' . esc_js( $invite_reg->reg_id ) . "\"); return false;' key='" . esc_attr( $invite_reg->invite_id ) . "'>View Details</a></tr>";
			}
		}
		echo '</table>';

		if ( count( $invite_regs ) >= 10 ) {
			echo '<div class="viewInviteLink"><a href="' . esc_url_raw( site_url() . '/wp-admin/admin.php?page=scormcloudtraining&inviteid=' . $invite_id ) . '">Click here to see complete training history.</a></div>';
		}
		break;

	case 'setactive':
		$invite_id = get_post_arg_as_string( 'inviteid' );
		$active    = get_post_arg_as_string( 'active' );

		$wpdb->update( ScormCloudDatabase::get_invitations_table(), array(
			'active' => $active,
			), array(
				'invite_id' => $invite_id,
		) );// db call ok; no-cache ok.

		break;

	case 'addCatalogRegGetLaunchUrl':
		$course_id   = get_post_arg_as_string( 'courseid' );
		$course_title = get_post_arg_as_string( 'coursetitle' );
		$return_url   = get_post_arg_as_string( 'returnurl' );

		$invite_id = uniqid();
		$reg_id   = $invite_id . '-' . uniqid();

		$app_id = ScormCloudPlugin::get_wp_option( 'scormcloud_appid' );
		global $current_user;
		global $wpdb;
		wp_get_current_user();

		$user_email = $current_user->user_email;
		$user_first_name = $current_user->user_firstname;
		$user_last_name = $current_user->user_lastname;
		if ( ! ( $user_first_name ) || strlen( $user_first_name ) < 1 ) {
			$user_first_name = $current_user->display_name;
		}
		if ( ! ( $user_last_name ) || strlen( $user_last_name ) < 1 ) {
			$user_last_name = $current_user->display_name;
		}
		$post_id = '__catalog_widget__';

		$header      = '';
		$description = '';

		$require_login    = 0;
		$show_course_info = 0;

		$wpdb->insert( ScormCloudDatabase::get_invitations_table(),
			array(
				'invite_id'        => $invite_id,
				'blog_id'          => $GLOBALS['blog_id'],
				'app_id'           => $app_id,
				'post_id'          => $post_id,
				'course_id'        => $course_id,
				'course_title'     => $course_title,
				'header'           => $header,
				'description'      => $description,
				'require_login'    => $require_login,
				'show_course_info' => $show_course_info,
			),
		array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ) );// db call ok; no-cache ok.

		$course_tags = 'catalog_widget';
		$reg_tags     = $GLOBALS['blog_id'] . ',' . $invite_id . ',' . $course_tags;

		$learner_tags = isset( $current_user->roles[0] ) ? $current_user->roles[0] : 'anonymous';

		// create the cloud registration.
		$registration_service = $cloud_service->getRegistrationService();
		$registration_service->CreateRegistration( $reg_id, $course_id, $user_email, $user_first_name, $user_last_name, $user_email );

		$wpdb->insert( ScormCloudDatabase::get_registrations_table(),
			array(
				'invite_id'  => $invite_id,
				'reg_id'     => $reg_id,
				'user_id'    => $current_user->ID,
				'user_email' => $user_email,
			),
		array( '%s', '%s', '%d', '%s' ) );// db call ok; no-cache ok.

		if ( function_exists( 'bp_activity_add' ) ) {
			global $bp;

			$from_user_link = bp_core_get_userlink( $bp->loggedin_user->id );
			$action_str      = sprintf( '%s launched course "%s" from the Catalog Widget', $from_user_link, $course_title );

			$activity_args = array(
				'action'  => $action_str,
				// The activity action - e.g. "Jon Doe posted an update".
				'content' => '',
				// Optional: The content of the activity item e.g. "BuddyPress is awesome guys!".
				'component' => 'scormcloud',
				// The name/ID of the component e.g. groups, profile, mycomponent.
				'type' => 'training_launch',
				// The activity type e.g. activity_update, profile_updated.
				'primary_link' => '',
				// Optional: The primary URL for this item in RSS feeds (defaults to activity permalink).
				'user_id'           => $bp->loggedin_user->id,
				// Optional: The user to record the activity for, can be false if this activity is not for a user.
				'item_id'           => false,
				// Optional: The ID of the specific item being recorded, e.g. a blog_id.
				'secondary_item_id' => false,
				// Optional: A second ID used to further filter e.g. a comment_id.
				'recorded_time'     => gmdate( 'Y-m-d H:i:s' ),
				// The GMT time that this activity was recorded.
				'hide_sitewide'     => false,
				// Should this be hidden on the sitewide activity stream?.
			);
			$bp_activity_id  = bp_activity_add( $activity_args );
		}

		$css_url = ScormCloudPlugin::get_wp_option( 'scormcloud_player_cssurl' );

		echo esc_url_raw( $registration_service->GetLaunchUrl( $reg_id, $return_url, $css_url, null, $course_tags, $learner_tags, $reg_tags ) );


		break;

	case 'addAnonCatalogRegGetLaunchUrl':

		$user_first_name = get_post_arg_as_string( 'fname' );
		$user_last_name  = get_post_arg_as_string( 'lname' );
		$user_email      = get_post_arg_as_string( 'email' );

		$course_id = get_post_arg_as_string( 'courseid' );
		$course_title = get_post_arg_as_string( 'coursetitle' );
		$return_url   = get_post_arg_as_string( 'returnurl' );


		$post_id = '__catalog_widget__';

		$header      = '';
		$description = '';

		$registration_service = $cloud_service->getRegistrationService();

		$inv_table = ScormCloudDatabase::get_invitations_table();
		$reg_table   = ScormCloudDatabase::get_registrations_table();
		$invite_reg = $wpdb->get_row( $wpdb->prepare( "SELECT r.reg_id, r.invite_id 
									FROM $inv_table i
                                 	JOIN $reg_table r 
                                 		ON i.invite_id = r.invite_id
                                 	WHERE r.user_email = %s AND i.course_id = %s AND i.app_id", array(
			$user_email,
			$course_id,
			ScormCloudPlugin::get_wp_option( 'scormcloud_appid' ),
		) ), OBJECT );// db call ok; no-cache ok.

		if ( null !== $invite_reg ) {
			$reg_id    = (string) $invite_reg->reg_id;
			$invite_id = (string) $invite_reg->invite_id;
		} else {
			$invite_id = uniqid();
			$reg_id     = $invite_id . '-' . uniqid();

			$require_login    = 0;
			$show_course_info = 0;
			$wpdb->insert( ScormCloudDatabase::get_invitations_table(),
				array(
					'invite_id'        => $invite_id,
					'blog_id'          => $GLOBALS['blog_id'],
					'app_id'           => ScormCloudPlugin::get_wp_option( 'scormcloud_appid' ),
					'post_id'          => $post_id,
					'course_id'        => $course_id,
					'course_title'     => $course_title,
					'header'           => $header,
					'description'      => $description,
					'require_login'    => $require_login,
					'show_course_info' => $show_course_info,
				),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ) );// db call ok; no-cache ok.

			// create the cloud registration.
			$registration_service->CreateRegistration( $reg_id, $course_id, $user_email, $user_first_name, $user_last_name, $user_email );

			$wpdb->insert( ScormCloudDatabase::get_registrations_table(),
				array(
					'invite_id'  => $invite_id,
					'reg_id'     => $reg_id,
					'user_email' => $user_email,
				),
			array( '%s', '%s', '%s' ) );// db call ok; no-cache ok.
		}

		$course_tags = 'catalog_widget';
		$reg_tags     = $GLOBALS['blog_id'] . ',' . $invite_id . ',' . $course_tags;

		$learner_tags = isset( $current_user->roles[0] ) ? $current_user->roles[0] : 'anonymous';

		if ( function_exists( 'bp_activity_add' ) ) {
			global $bp;

			$action_str = sprintf( '%s launched course "%s" from the Catalog Widget', $user_first_name . ' ' . $user_last_name, $course_title );

			$activity_args = array(
				'action'  => $action_str,
				// The activity action - e.g. "Jon Doe posted an update".
				'content' => '',
				// Optional: The content of the activity item e.g. "BuddyPress is awesome guys!".
				'component' => 'scormcloud',
				// The name/ID of the component e.g. groups, profile, mycomponent.
				'type' => 'training_launch',
				// The activity type e.g. activity_update, profile_updated.
				'primary_link' => '',
				// Optional: The primary URL for this item in RSS feeds (defaults to activity permalink).
				'user_id'           => false,
				// Optional: The user to record the activity for, can be false if this activity is not for a user.
				'item_id'           => false,
				// Optional: The ID of the specific item being recorded, e.g. a blog_id.
				'secondary_item_id' => false,
				// Optional: A second ID used to further filter e.g. a comment_id.
				'recorded_time'     => gmdate( 'Y-m-d H:i:s' ),
				// The GMT time that this activity was recorded.
				'hide_sitewide'     => false,
				// Should this be hidden on the sitewide activity stream?.
			);
			$bp_activity_id  = bp_activity_add( $activity_args );
		}

		$css_url = ScormCloudPlugin::get_wp_option( 'scormcloud_player_cssurl' );

		echo esc_url_raw( $registration_service->GetLaunchUrl( $reg_id, $return_url, $css_url, null, $course_tags, $learner_tags, $reg_tags ) );

		break;

	default:
		break;
}

/**
 * Checks to see if an argument exists in $_POST and returns it.
 *
 * @param string $arg_name the arugment to look for.
 *
 * @return string/null
 */
function get_post_arg_as_string( $arg_name ) {
	$return_val = isset( $_POST[ $arg_name ] ) ? wp_unslash( $_POST[ $arg_name ] ) : null ;// Input var okay.
	if ( is_string( $return_val ) ) {
		return $return_val;
	}
	return null;
}

?>