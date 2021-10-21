<?php

require_once( SCORMCLOUD_BASE . 'scormcloudui.php' );
require_once( SCORMCLOUD_BASE . 'scormcloudcontenthandler.php' );
require_once( SCORMCLOUD_BASE . 'db/scormclouddatabase.php' );

/**
 * Class ScormCloudPlugin.
 */
class ScormCloudPlugin {
	/**
	 * Array of filter hooks.
	 *
	 * @var array hooks.
	 */
	public static $hooks = array();

	/**
	 * Activate the plugin and install the scorm cloud db.
	 */
	public static function activate() {
		ScormCloudDatabase::install();
	}

	/**
	 * Deactivate the plugin.
	 */
	public static function deactivate() {

	}

	/**
	 * Uninstall the plugin.
	 */
	public static function uninstall() {
		if ( __FILE__ !== WP_UNINSTALL_PLUGIN ) {
			return;
		}
	}

	/**
	 * Initialize the plugin.
	 */
	public static function initialize() {
		add_filter( 'the_content', array( 'ScormCloudContentHandler', 'make_blog_entry' ) );

		add_action( 'save_post', array( 'ScormCloudContentHandler', 'update_post_invite' ) );
		add_action( 'profile_update', array( 'ScormCloudContentHandler', 'update_learner_info' ) );
		
		add_action('wp_loaded',array( 'ScormCloudContentHandler','boot_session'));
	}
	  
	/**
	 * Check for updates.
	 */
	public static function update_check() {
		ScormCloudDatabase::update_check();
	}

	/**
	 * Check to see if site is network managed.
	 *
	 * @return bool.
	 */
	public static function is_network_managed() {
		if ( ! function_exists( 'get_site_option' ) ) {
			return false;
		}
		if ( null !== get_site_option( 'scormcloud_networkmanaged' ) ) {
			return (bool) get_site_option( 'scormcloud_networkmanaged' );
		} else {
			return false;
		}
	}

	/**
	 * Build an instance of the cloud api client.
	 *
	 * @param bool $force_network_settings use site options.
	 *
	 * @return ScormEngineService
	 */
	public static function get_cloud_service( $force_network_settings = false ) {
		require_once( 'SCORMCloud_PHPLibrary/ScormEngineService2.php' );
		require_once( 'SCORMCloud_PHPLibrary/ScormEngineUtilities.php' );

		if ( ScormCloudPlugin::is_network_managed() || $force_network_settings ) {
			$appid      = get_site_option( 'scormcloud_appid' );
			$secretkey  = get_site_option( 'scormcloud_secretkey' );
			$engine_url = get_site_option( 'scormcloud_engine_url' );
			$proxy      = get_site_option( 'proxy' );

		} else {
			$appid      = get_option( 'scormcloud_appid' );
			$secretkey  = get_option( 'scormcloud_secretkey' );
			$engine_url = get_option( 'scormcloud_engine_url' );
			$proxy      = get_option( 'proxy' );
		}

		$origin = ScormEngineUtilities::getCanonicalOriginString( 'Rustici Software', 'WordPress', '2.0.2' );

		if ( strlen( $engine_url ) < 1 ) {
			$engine_url = 'https://cloud.scorm.com/api/v2';
		}

		return new ScormEngineService( $engine_url, $appid, $secretkey, $origin, $proxy );
	}

	/**
	 * Wraps the WordPress get_option and get_site_option functions, using whichever is appropriate for the
	 * current settings.
	 *
	 * @param string $option The option name.
	 *
	 * @return String
	 */
	public static function get_wp_option( $option ) {
		return ( self::is_network_managed() ) ? get_site_option( $option ) : get_option( $option );
	}

	/**
	 * Fetch the number of available registrations for an account.
	 *
	 * @return int
	 */
	public static function remaining_registrations() {
		$cloud_service = ScormCloudPlugin::get_cloud_service();
		$account_service  = $cloud_service->getReportingService();
		$response     = $account_service->GetAccountInfo(); 
		
		$account_info = json_decode($response);
		if ( 'trial' !== (string) $account_info->accountType && 'false' === (string) $account_info->strictLimit ) {
			return 1;
		} else {
			$reg_limit = (int) $account_info->regLimit;
			$reg_usage = (int) $account_info->usage->regCount;
			return $reg_limit - $reg_usage;
		}
	}

	/**
	 * Check to see if this Registration already exists.
	 *
	 * @return bool
	 */
	public static function registration_exists($regId) {
		$exists = false;
		$cloud_service = ScormCloudPlugin::get_cloud_service();
		$reg_service  = $cloud_service->getRegistrationService();
		try{
			$response     = $reg_service->GetRegistration($regId); 
			$exists = true;
		} catch (Exception $ex) {
			$exists = false;
		}
		return $exists;
	}

	/**
	 * Check to see if this Course already exists.
	 *
	 * @return bool
	 */
	public static function course_exists($courseId) {
		$exists = false;
		$cloud_service = ScormCloudPlugin::get_cloud_service();
		$course_service  = $cloud_service->getCourseService();
		try{
			$response     = $course_service->GetCourse($courseId); 
			$exists = true;
		} catch (Exception $ex) {
			$exists = false;
		}
		return $exists;
	}

	/**
	 * Removes http: and https: from a url.
	 *
	 * @param string $src The url to strip.
	 * @param string $handle The handle of the script/css being enqueued.
	 *
	 * @return String
	 */
	public static function agnostic_loader( $src, $handle ) {
		return preg_replace( '/^(http|https):/', '', $src );
	}

	/**
	 * Helper method to get all courses recursively to match v1 functionality
	 */
	function loadAllCourses($courseService) {
		$courseResponse = $courseService->getCourses(null, null, 'updated', null, null, null, null, null, 'false', 'true');
		$more = $courseResponse->getMore();
		$courseArray = $courseResponse->getCourses();
	
		if ($more != '') {
			$moreCourses = self::handleMoreCourses($more, $courseService);
			foreach($moreCourses as $course) {
				array_push($courseArray, $course);
			}
		}
	
		return $courseArray;
	}
	
	/**
	 * Helper method to handle the more courses token
	 */
	function handleMoreCourses($more, $courseService) {
		if ($more != '') {
			// there are more results to load them up recursively if needed
			$moreResponse = $courseService->getCourses(null, null, 'updated', null, null, null, null, $more, 'false', 'true');
			$moreCourses = $moreResponse->getCourses();
			$moreMore = $moreResponse->getMore();
			if ($moreMore != '') {
				$evenMoreCourses = self::handleMoreCourses($moreMore, $courseService);
				foreach($evenMoreCourses as $course) {
					array_push($moreCourses, $course);
				}
			}
			return $moreCourses;
		}
	}
}

