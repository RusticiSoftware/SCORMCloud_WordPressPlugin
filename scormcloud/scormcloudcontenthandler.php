<?php

require_once( SCORMCLOUD_BASE . 'scormcloudplugin.php' );
require_once( SCORMCLOUD_BASE . 'db/scormclouddatabase.php' );

/**
 * Class ScormCloudContentHandler
 */
class ScormCloudContentHandler {
	/**
	 * Make a blog entry.
	 *
	 * @param mixed $content  blog entry content.
	 *
	 * @return mixed
	 */
	public static function make_blog_entry( $content ) {
		global $wpdb;

		preg_match_all( '/\\[scormcloud.training:.*?\\]/', $content, $cloud_tag_array );

		$cloud_tags = $cloud_tag_array[0];

		foreach ( $cloud_tags as $tag_string ) {
			$cloud_service = ScormCloudPlugin::get_cloud_service();
			try {
				$is_valid_account = $cloud_service->isValidAccount();
			} catch ( Exception $e ) {
				$is_valid_account = false;
			}

			$invite_id = substr( $tag_string, 21, strlen( $tag_string ) - 22 );

			$invite = ScormCloudDatabase::get_invitation( $invite_id );
			if ( null === $invite ) {
				$content = str_replace( $tag_string, '', $content );
			}

			$invite_html = "<div class='scormCloudInvitation' key='$invite_id'>";
			$invite_html .= '<h3>' . stripcslashes( $invite->header ) . '</h3>';
			$invite_html .= '<p class="description">' . stripcslashes( $invite->description ) . '</p>';

			$course_service = $cloud_service->getCourseService();

			if ( ! $course_service->Exists( $invite->course_id ) ) {
				$invite_html .= '<h3>' . __( 'This training is not currently available.', 'scormcloud' ) . ' </h3>';
			} else {

				if ( 1 === (int) $invite->show_course_info ) {
					// get course info.
					$invite_html .= "<div class='courseInfo'>";

					if ( $is_valid_account ) {
						$course_metadata_xml = $course_service->GetMetadata( $invite->course_id, 0, 0, 0 );
						$course_metadata_xml = simplexml_load_string( $course_metadata_xml );
						$metadata    = $course_metadata_xml->package->metadata;
					}
					$invite_html .= '<div class="title">Title: ' . $invite->course_title . '</div>';

					if ( $is_valid_account && null !== $metadata ) {
						$invite_html .= '<div class="desc">' . $metadata->description . '</div>';

						// assuming seconds coming back for now.
						$duration = ( (int) $metadata->duration ) / 100;
						if ( $duration > 0 ) {
							$invite_html .= '<div class="duration">Duration: ' . floor( $duration / 60 ) . 'min ' . ( $duration % 60 ) . 'sec </div>';
						}
					}


					$invite_html .= '</div>';
				}

				if ( ! $is_valid_account || 1 !== (int) $invite->active ) {
					$invite_html .= '<h3>' . __( 'This training is not currently active.', 'scormcloud' ) . '</h3>';
				} else {

					$remaining_registrations = ScormCloudPlugin::remaining_registrations();

					global $current_user;

					wp_get_current_user();

					// if not logged in.
					if ( ! isset( $current_user->user_login ) || '' === $current_user->user_login ) {
						if ( $remaining_registrations > 0 ) {
							if ( 0 === (int) $invite->require_login ) {
								$invite_html .= "<p class='inputs'>My name is <input name='scormcloudfname' placeholder='First Name' type='text' key='$invite_id'/>&nbsp;<input name='scormcloudlname' placeholder='Last Name' type='text' key='$invite_id'/>";
								$invite_html .= " and my email is <input name='scormcloudemail' placeholder='Email' type='text' key='$invite_id'/> .</p>";
								$invite_html .= "<input name='launch' type='button' key='$invite_id' onclick='ScormCloud.Post.makeAnonRegLaunch(\"$invite_id\");' url='" . site_url() . "/wp-content/plugins/scormcloud/ajax.php' value='Start Training'/>";
							} else {
								$invite_html .= '<h3>' . __( 'Please log in to take this training.', 'scormcloud' ) . '</h3>';
							}
						} else {
							$invite_html .= '<h3>' . __( 'This training is not currently active.', 'scormcloud' ) . '</h3>';
						}
					} else {
						$user_id = $current_user->ID;
						$reg    = $wpdb->get_row( $wpdb->prepare( 'SELECT reg_id FROM ' . esc_sql( ScormCloudDatabase::get_registrations_table() ) . ' WHERE invite_id = %s AND
	                                             user_id = %s ORDER BY update_date DESC', [ $invite_id, $user_id ] ), OBJECT );// db call ok; no-cache ok.
						if ( null !== $reg ) {
							$reg_id = $reg->reg_id;

							$registration_service       = $cloud_service->getRegistrationService();
							$registration_result = $registration_service->GetRegistrationResult( $reg_id, 0, 0 );
							$result_xml           = simplexml_load_string( $registration_result );


							$completion = (string) $result_xml->registrationreport->complete;
							$success    = (string) $result_xml->registrationreport->success;
							$seconds    = (string) $result_xml->registrationreport->totaltime;
							$score      = (string) $result_xml->registrationreport->score;

							$invite_html .= '<table class="result_table"><tr>' .
							               '<td class="head">' . __( 'Completion', 'scormcloud' ) . '</td>' .
							               '<td class="head">' . __( 'Success', 'scormcloud' ) . '</td>' .
							               '<td class="head">' . __( 'Score', 'scormcloud' ) . '</td>' .
							               '<td class="head">' . __( 'Total Time', 'scormcloud' ) . '</td>' .
							               '</tr><tr>' .
							               "<td class='$completion'>" . $completion . '</td>' .
							               "<td class='$success'>" . $success . '</td>' .
							               "<td class='' . ( $score === 'unknown' ? __( 'unknown' ) : '' ) . ''>" . ( 'unknown' === $score ? '-' : $score . '%' ) . '</td>' .
							                '<td class="time">' . floor( $seconds / 60 ) . 'min ' . ( $seconds % 60 ) . __( 'sec spent in course', 'scormcloud' ) . '</td>' .
							               '</tr></table>';


							$invite_html .= "<input name='launch' type='button' key='$invite_id' onclick='ScormCloud.Post.getLaunchURL(\"$invite_id\",\"$reg_id\");' url='" . site_url() . "/wp-content/plugins/scormcloud/ajax.php' value='" . __( 'Relaunch Training', 'scormcloud' ) . "' />";


						} else {
							if ( $remaining_registrations > 0 ) {
								$invite_html .= "<input name='launch' type='button' key='$invite_id' onclick='ScormCloud.Post.makeUserRegLaunch(\"$invite_id\");' url='" . site_url() . "/wp-content/plugins/scormcloud/ajax.php' value='Start Training'/>";
							} else {
								$invite_html .= '<h3>' . __( 'This training is not currently active.', 'scormcloud' ) . '</h3>';
							}
						}// End if().
					}// End if().
				}// End if().
			}// End if().
			$invite_html .= "<div class='inviteMessage'>message</div>";
			$invite_html .= '</div>';
			$content = str_replace( $tag_string, $invite_html, $content );
		}// End foreach().

		preg_match_all( '/\\[scormcloud.reportage:.*?\\]/', $content, $cloud_rep_array );

		$cloud_reportage_links = $cloud_rep_array[0];

		foreach ( $cloud_reportage_links as $tag_string ) {
			$cloud_service = ScormCloudPlugin::get_cloud_service();
			try {
				$is_valid_account = $cloud_service->isValidAccount();
			} catch ( Exception $e ) {
				$is_valid_account = false;
			}

			if ( $is_valid_account ) {
				$link_text     = substr( $tag_string, 22, strlen( $tag_string ) - 23 );
				$reporting_service   = $cloud_service->getReportingService();
				$reportage_service_url  = $reporting_service->GetReportageServiceUrl();
				$reportage_auth      = $reporting_service->GetReportageAuth( 'FREENAV', true );
				$reportage_url = $reportage_service_url . 'Reportage/reportage.php?appId=' . $cloud_service->getAppId() . '&registrationTags=' . $GLOBALS['blog_id'] . '|_all';
				$rep_html      = '<a id="ReportageLink" href="' . $reporting_service->GetReportUrl( $reportage_auth, $reportage_url ) . '" 
							title="' . __( 'Open the SCORM Reportage Console in a new window.', 'scormcloud' ) . '">' . $link_text . '</a>';


				$content = str_replace( $tag_string, $rep_html, $content );
			}
		}

		return $content;
	}

	/**
	 * Update the invitation post.
	 *
	 * @param WP_Post $post_id post identifier.
	 */
	public static function update_post_invite( $post_id ) {
		global $wpdb;
		$post    = get_post( $post_id );
		$content = $post->post_content;

		$parent_id = wp_is_post_revision( $post_id );
		if ( $parent_id ) {
			$post_id = $parent_id;
		}


		preg_match_all( '/\[scormcloud.training:.*\]/', $content, $cloud_tag_array );

		$cloud_tags = $cloud_tag_array[0];

		foreach ( $cloud_tags as $tag_string ) {
			$invite_id = substr( $tag_string, 21, strlen( $tag_string ) - 22 );

			$wpdb->update( ScormCloudDatabase::get_invitations_table(), array(
				'post_id' => $post_id,
				), array(
				'invite_id' => $invite_id,
			) );// db call ok; no-cache ok.
		}

	}

	/**
	 * Update learner info.
	 *
	 * @param mixed $user_id user id object.
	 */
	public static function update_learner_info( $user_id ) {
		global $wpdb;
		$cloud_service = ScormCloudPlugin::get_cloud_service();
		$registration_service   = $cloud_service->getRegistrationService();
		$user_data     = get_userdata( $user_id );
		/* pushing blank data into SCORMCloud generates a stack trace */
		if ( ! empty( $user_data->user_firstname ) &&
		     ! empty( $user_data->user_lastname )
		) {
			$response = $registration_service->UpdateLearnerInfo( $user_data->user_email, $user_data->user_firstname, $user_data->user_lastname );
			write_log( $response );
		} else {
			write_log( "profile update skipped for {$user_data->user_email} due to missing first or last name" );
		}
	}
}
