<?php

require_once( SCORMCLOUD_BASE . 'scormcloudplugin.php' );

class ScormCloudAdminUi {
	/**
	 * Initialize the admin ui.
	 */
	public static function initialize() {
		add_action( 'admin_menu', [ __CLASS__, 'admin_menu_actions' ] );
		add_action( 'network_admin_menu', [ __CLASS__, 'network_admin_menu_actions' ] );
		add_action( 'admin_head', [ __CLASS__, 'set_js_vars' ] );
	}

	/**
	 * Enqueue all of the admin includes.
	 */
	public static function enqueue_admin_includes() {
		wp_enqueue_script( 'scormcloud-admin', plugins_url( '../scripts/scormcloud.admin.js', __FILE__ ) );
		wp_enqueue_script( 'scormcloud-date_format', plugins_url( '../scripts/date.format.js', __FILE__ ) );
		wp_register_style( 'scormcloud-admin-style', plugins_url( '../css/scormcloud.admin.css', __FILE__ ) );
		wp_enqueue_style( 'scormcloud-admin-style' );
	}

	/**
	 * Generate the admin menu actions.
	 */
	public static function admin_menu_actions() {
		self::enqueue_admin_includes();

		$topslug        = 'scormcloud/admin';
		$plugin_hooks   =& ScormCloudPlugin::$hooks;
		$plugin_hooks[] = add_menu_page( 'SCORM Cloud Overview', 'SCORM Cloud', 'publish_posts', $topslug, [
			__CLASS__,
			'show_start_page',
		] );
		$plugin_hooks[] = add_submenu_page( $topslug, 'SCORM Cloud Courses', 'Courses', 'publish_posts', 'scormcloud/manage_courses', [
			__CLASS__,
			'show_manage_courses',
		] );
		$plugin_hooks[] = add_submenu_page( $topslug, 'SCORM Cloud Training', 'Training', 'publish_posts', 'scormcloud/manage_training', [
			__CLASS__,
			'show_manage_training',
		] );
		$plugin_hooks[] = add_submenu_page( $topslug, 'SCORM Cloud Settings', 'Settings', 'publish_posts', 'scormcloud/admin/settings', [
			__CLASS__,
			'show_settings',
		] );

		add_action( 'contextual_help', [ __CLASS__, 'contextual_help' ], 10, 3 );
	}

	/**
	 * Generate the admin menu actions for network admins.
	 */
	public static function network_admin_menu_actions() {
		self::enqueue_admin_includes();

		$topslug        = 'scormcloud/network-admin';
		$plugin_hooks   =& ScormCloudPlugin::$hooks;
		$plugin_hooks[] = add_menu_page( 'SCORM Cloud Overview', 'SCORM Cloud', 'publish_posts', $topslug, [
			__CLASS__,
			'show_start_page',
		] );
		$plugin_hooks[] = add_submenu_page( $topslug, 'SCORM Cloud Settings', 'Settings', 'publish_posts', 'scormcloud/network-admin/settings', [
			__CLASS__,
			'show_network_settings',
		] );

		add_action( 'contextual_help', [ __CLASS__, 'contextual_help' ], 10, 3 );
	}

	/**
	 * Include the start page.
	 */
	public static function show_start_page() {
		include( 'startpage.php' );
	}

	/**
	 * Include the manage courses page.
	 */
	public static function show_manage_courses() {
		include( 'managecourses.php' );
	}

	/**
	 * Include the manage training page.
	 */
	public static function show_manage_training() {
		include( 'managetraining.php' );
	}

	/**
	 * Include the settings page.
	 */
	public static function show_settings() {
		include( 'settings.php' );
	}

	/**
	 * Include the network settings page.
	 */
	public static function show_network_settings() {
		include( 'network_settings.php' );
	}

	/**
	 * Set the embed training url.
	 */
	public static function set_js_vars() {
		?>
		<script type="text/javascript" charset="utf-8">
			if (typeof ScormCloud !== 'undefined' && typeof ScormCloud.Dialog !== 'undefined') {
				ScormCloud.Dialog.embedTrainingUrl = "<?php echo esc_url_raw( plugins_url( '/scormcloud/ui/embedtrainingdialog.php' ) ); ?>";
			}
		</script>
		<?php
	}

	/**
	 * Build contextual help stubs.
	 */
	public static function contextual_help( $text, $screen_id, $screen ) {
		$plugin_hooks = ScormCloudPlugin::$hooks;

		if ( substr( $screen_id, - 8 ) == '-network' ) {
			$screen_id = substr_replace( $screen_id, '', - 8 );
		}

		if ( in_array( $screen_id, $plugin_hooks ) ) {

			$help_html = "<div class='sc_helpPanel'>";
			$help_html .= '<h2>SCORM Cloud Hints and Tips</h2>';

			if ( isset( $_GET[ 'page' ] ) ) {
				$plugin_page = stripslashes( $_GET[ 'page' ] );
				$plugin_page = plugin_basename( $plugin_page );
			}

			switch ( $plugin_page ) {

				case 'scormcloud/network-admin':
				case 'scormcloud/admin':
					$help_html .= "<p><span class='emph'>Overall Reportage Summary</span> is a results report for all trainings in your wordpress plugin.";
					$help_html .= "Note that the results are not reported in real time but are current as of the given date.  You can view the full report in the Reportage site by clicking the 'Scorm Cloud Reportage' link under the page header.";
					$help_html .= '</p>';
					break;

				case 'scormcloud/manage_courses':
					$help_html .= "<p><span class='emph'>Import a new course</span> provides functionality to upload a course to the SCORM Cloud for use in trainings in wordpress.
    	            Files uploaded should be zipped up SCORM or AICC course packages.</p>";
					$help_html .= "<p><span class='emph'>All Courses</span> lists all of the courses available for training in wordpress.  This list comes from the SCORM Cloud, and the courses can also be managed on the SCORM Cloud site.
    	            <ul>
    	            <li>Click the <span class='emph'>Preview</span> link to launch an untracked preview of the course.  This will launch the full course, but no results will be recorded.</li>
    	            <li>Clicking the <span class='emph'>View Course Report</span> link will open the full reportage report for the course in the Reportage Application.</li>
    	            <li>Clicking the <span class='emph'>Delete Course</span> link will delete the course from wordpress and from the SCORM Cloud.  Any trainings associated with the course, however,
    	            will not be deleted.</li>
    	            </ul></p>";
					break;

				case 'scormcloud/manage_training':

					if ( isset( $_GET[ 'inviteid' ] ) ) {
						$help_html .= "<p>Clicking the <span class='emph'>click to activate/deactivate</span> link will set the training as launchable or not launchable.</p>";
						$help_html .= "<p><span class='emph'>Invitation Details</span> provides a way to modify an existing post/page invitation.  An example of what the invitation will
    	                look like in the post is displayed to the right of the edit fields.  This section will not appear for 'Catalog Widget' and 'Quick Create Training' trainings.</p>";
						$help_html .= "<p><span class='emph'>Training Summary</span> is a results report for all learners who have started this training.
    	                Note that the results are not reported in real time but are current as of the given date.  You can view the full report in the Reportage site by clicking the 'View Full Results Report' link.";
						$help_html .= "<p><span class='emph>Training History</span> lists all of the learners that have started or have been assigned this training.
    	                You can view the full learners report in the Reportage site by clicking the 'View All Learners in Reportage' link.
    	                <ul>
    	                <li>Click the <span class='emph'>View Details</span> link see a detailed report of a learner's results in Reportage.</li>
    	                </ul>
    	                </p>";
					} else {

						$help_html .= "<p><span class='emph'>Quick Create Training</span> provides the ability to directly assign a course to existing wordpress users.  These trainings will not appear
    	                in a wordpress post or page, but the course will show up in the user's <span class='emph'>SCORM Cloud User Training Widget</span>; so it is important to add that widget to your wordpress blog for the users
    	                to be able to launch the assigned courses.  Note that using this functionality to assign courses pre-creates a registration in your SCORM Cloud account for each assigned course and user.</p>";
						$help_html .= "<p><span class='emph'>SCORM Cloud Training History</span> lists all of the trainings that have been created in your wordpress site.
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

				case 'scormcloud/admin/settings':
					$help_html .= '<p>The <span class="emph">App Id</span> and <span class="emph">Secret Key</span> can both be found by going to your <a href="http://cloud.scorm.com/sc/user/Apps">Apps Page</a> on the SCORM Cloud site. These values are essentially the "username and password" for WordPress to access your SCORM Cloud account.</p>';
					$help_html .= '<p>The <span class="emph">Cloud Engine URL</span> is set with a default value that does not need to be changed for most users.</p>';
					$help_html .= '<p>The <span class="emph">SCORM Player Stylesheet URL</span> controls the CSS stylesheet used to style the user interface of the SCORM player.</p>';
					break;

				case 'scormcloud/network-admin/settings':
					$help_html .= '<p>The <span class="emph">App Id</span> and <span class="emph">Secret Key</span> can both be found by going to your <a href="http://cloud.scorm.com/sc/user/Apps">Apps Page</a> on the SCORM Cloud site. These values are essentially the "username and password" for WordPress to access your SCORM Cloud account.</p>';
					$help_html .= '<p><span class="emph">Use same SCORM Cloud account across all sites:</span> If enabled, all sites in the network will use the SCORM Cloud account credentials and Engine URL and administrators for those sites will not be able to change these settings.</p>';
					$help_html .= '<p><span class="emph">Share courses among all sites:</span> If enabled, all sites will use and upload to the same course library on the SCORM Cloud for creating training.</p>';
					$help_html .= '<p>The <span class="emph">Cloud Engine URL</span> is set with a default value that does not need to be changed for most users.</p>';
					break;

				default:
					$help_html .= "<p><span class='emph'></span></p>";
					break;


			} // End switch().

			$help_html .= "<hr>";

			$help_html .= "<p>The SCORM Cloud plugin requires a SCORM Cloud account that can be created and managed on the <a href='https://cloud.scorm.com/'>SCORM Cloud</a> application site.</p>";
			$help_html .= "<p>The SCORM Cloud is brought to you by <a href='https://www.scorm.com/'>Rustici Software</a>.</p>";


			$help_html .= "</div>";

			return $help_html;
		} else {
			return $text;
		}
	}
}