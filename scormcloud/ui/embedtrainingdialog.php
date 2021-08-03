<?php

/* Finding the path to the wp-admin folder */
$iswin = preg_match( '/:\\\/', dirname( __file__ ) );
$slash = ( $iswin ) ? "\\" : "/";

$wp_path = preg_split( '/(?=((\\\|\/)wp-content)).*/', dirname( __file__ ) );
$wp_path = ( isset( $wp_path[ 0 ] ) && $wp_path[ 0 ] !== "" ) ? $wp_path[ 0 ] : $_SERVER[ "DOCUMENT_ROOT" ];

/** Load WordPress Administration Bootstrap */
require_once( $wp_path . $slash . 'wp-admin' . $slash . 'admin.php' );

global $wpdb;


require_once( SCORMCLOUD_BASE . 'scormcloudplugin.php' );

$scorm_service = ScormCloudPlugin::get_cloud_service();
try {
	$is_valid_account = $scorm_service->isValidAccount();
} catch ( Exception $e ) {
	$is_valid_account = false;
}

if ( $is_valid_account ) {
	?>
	<div id="embedTrainingDialog">
		<h1><?php esc_attr_e( 'Add training to your post', 'scormcloud' ); ?></h1>


		<span class="labelheader"><?php esc_attr_e( 'First select a course', 'scormcloud' ); ?>:
</span>
		<select class="courseSelector">

			<?php

			echo "<option value=''></option>";
			$courses_filter = ( ScormCloudPlugin::is_network_managed() && get_site_option( 'scormcloud_sharecourses' ) !== '1' ) ? $GLOBALS['blog_id'] . '-.*' : null;
			$course_service  = $scorm_service->getCourseService();
			$all_results     = $course_service->GetCourseList( $courses_filter );
			foreach ( $all_results as $course ) {
				echo "<option value='" . esc_attr( $course->getCourseId() ) . "'>" . esc_attr( $course->getTitle() ) . '</option>';
			}

			?>
		</select> <br/>
		<br/>

		<div class='selectOptionsDiv'><span class="labelheader"><?php esc_attr_e( 'Next select some options', 'scormcloud' ); ?>
				:
</span>
			<table>
				<tr>
					<td class="label"><?php esc_attr_e( 'Training Header Text', 'scormcloud' ); ?>:</td>
					<td><input type="text" name="trainingHeaderTxt"
							   value="<?php esc_attr_e( 'Launch Training Now', 'scormcloud' ); ?>"/></td>
				</tr>
				<tr>
					<td class="label"><?php esc_attr_e( 'Training Description', 'scormcloud' ); ?>:</td>
					<td><textarea type="text"
								  name="trainingDesc"><?php esc_attr_e( "Click 'Start Training' to take your training.", 'scormcloud' ); ?></textarea>
					</td>
				</tr>
				<tr>
					<td colspan='2'><input type="checkbox" checked
										   name="trainingRequireLogin"/><?php esc_attr_e( 'Require that learners be authenticated users.', 'scormcloud' ); ?>
					</td>
				</tr>
				<tr>
					<td colspan='2'><input type="checkbox" checked
										   name="showCourseInfo"/><?php esc_attr_e( 'Show the course title and description.', 'scormcloud' ); ?>
					</td>
				</tr>

			</table>


			<input type="button" class="generateTrainingTag button"
				   name="generateTrainingTag"
				   value="<?php esc_attr_e( 'Embed This Training', 'scormcloud' ); ?>"/> <a
					href="javascript:void(0);" class="toggleButton previewPostEmbed"
					toggleobject='#embedTrainingDialog .previewDiv'
					onText='<?php esc_attr_e( 'hide preview', 'scormcloud' ); ?>'
					offText='<?php esc_attr_e( 'show preview', 'scormcloud' ); ?>'><?php esc_attr_e( 'show preview', 'scormcloud' ); ?></a>
			<div class="previewDiv">
				<div class="scormCloudInvitation">
					<h3><?php esc_attr_e( 'Launch Training Now', 'scormcloud' ); ?></h3>
					<p class="description"><?php esc_attr_e( "Click 'Start Training' to take your training.", 'scormcloud' ); ?></p>
					<div class="courseInfo">
						<div class="title"></div>
						<div class='desc'><?php esc_attr_e( 'This will be the metadata description for your course (if it exists).  Also, the displayed duration will render if it exists in the metadata.', 'scormcloud' ); ?></div>
						<div class='duration'><?php esc_attr_e( 'Duration: 10 minutes', 'scormcloud' ); ?></div>
					</div>
					<p class='inputs'><?php esc_attr_e( 'My name is', 'scormcloud' ); ?> <input
								disabled name="scormcloudfname" placeholder="First Name" type="text"> <input
								name="scormcloudlname" disabled placeholder="Last Name"
								type='text'> <?php esc_attr_e( 'and my email is', 'scormcloud' ); ?>
						<input name="scormcloudemail" disabled placeholder="Email" type="text">
						.</p>
					<input type="button" class="button"
						   value="<?php esc_attr_e( 'Start Training', 'scormcloud' ); ?>"
						   onclick="return false;" name="launch"></div>
			</div>

		</div>
		<script type="text/javascript" charset="utf-8">
			var $j = jQuery.noConflict();
			$j(document).ready(function () {

				$j('#TB_ajaxContent').removeAttr('style');
				$j('#TB_window').css('overflow-y', 'auto');

				$j('#embedTrainingDialog .toggleButton').toggle(function () {
					$j($j(this).attr('toggleobject')).fadeIn('slow');
					$j(this).text($j(this).attr('onText'));
				}, function () {
					$j($j(this).attr('toggleobject')).fadeOut('slow');
					$j(this).text($j(this).attr('offText'));
				});

				$j('#embedTrainingDialog .courseSelector').change(function () {
					var selObj = $j('#embedTrainingDialog .courseSelector option:selected');
					if (selObj.val() != '') {
						//var newheader = 'Training: ' + selObj.text();
						//$j('#embedTrainingDialog input[name="trainingHeaderTxt"]').val(newheader);
						//$j('#embedTrainingDialog .previewDiv h3').text(newheader);
						$j('#embedTrainingDialog .previewDiv div.courseInfo div.title').text('<?php esc_attr_e( 'Title', 'scormcloud' ); ?>: ' + selObj.text());
						$j('#embedTrainingDialog .selectOptionsDiv').fadeIn('slow');
						tb_position();
					}
				});

				$j('#embedTrainingDialog input[name="trainingHeaderTxt"]').change(function () {
					$j('#embedTrainingDialog .previewDiv h3').text($j(this).val());
				});
				$j('#embedTrainingDialog textarea[name="trainingDesc"]').change(function () {
					$j('#embedTrainingDialog p.description').text($j(this).val());
				});
				$j('#embedTrainingDialog input[name="trainingRequireLogin"]').change(function () {
					$j('#embedTrainingDialog .previewDiv p.inputs').toggle();
				});
				$j('#embedTrainingDialog input[name="showCourseInfo"]').change(function () {
					$j('#embedTrainingDialog .previewDiv div.courseInfo').toggle();
				});

				$j("#embedTrainingDialog .generateTrainingTag").click(function (e) {


					var courseId = $j('#embedTrainingDialog .courseSelector option:selected').attr('value');
					var courseTitle = $j('#embedTrainingDialog .courseSelector option:selected').text();
					var header = $j('#embedTrainingDialog input[name="trainingHeaderTxt"]').attr('value');
					var description = $j('#embedTrainingDialog textarea[name="trainingDesc"]').attr('value');
					var requirelogin = $j('#embedTrainingDialog input[name="trainingRequireLogin"]:checked').length;
					var showcourseinfo = $j('#embedTrainingDialog input[name="showCourseInfo"]:checked').length;

					$j.ajax({
						type: "POST",
						url: "<?php echo esc_url_raw( site_url() . '/wp-content/plugins/scormcloud/ajax.php' ); ?>",
						data: "action=addPostInvite" +
						"&courseid=" + courseId +
						"&coursetitle=" + courseTitle +
						"&header=" + header +
						"&description=" + description +
						"&requirelogin=" + requirelogin +
						"&showcourseinfo=" + showcourseinfo,
						success: function (html) {
							ScormCloud.Dialog.insertTag("[scormcloud.training:" + html + "]");
						}
					});


				});


			});


		</script>
	</div>
	<?php
} else {
	echo '<div>
			<h2>' . esc_attr( __( 'Please configure your SCORM Cloud settings to add training to your posts.', 'scormcloud' ) ) . '</h2>
		</div>';
	echo '<div class="settingsPageLink"><a href="' . esc_url_raw( site_url() . '/wp-admin/admin.php?page=scormcloudsettings' ) .
				'title="' . esc_attr( __( 'Click here to configure your SCORM Cloud plugin.', 'scormcloud' ) ) . '">' . esc_attr( __( 'Click Here to go to the settings page.', 'scormcloud' ) ) . '</a></div>';


}
?>
