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
		require_once( 'SCORMCloud_PHPLibrary/ScormEngineService.php' );
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

		$origin = ScormEngineUtilities::getCanonicalOriginString( 'Rustici Software', 'WordPress', '1.1.2' );

		// arbitrary number 17 is the length of 'EngineWebServices'.
		if ( strlen( $engine_url ) < 17 ) {
			$engine_url = 'http://cloud.scorm.com/EngineWebServices';
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
		$account_service  = $cloud_service->getAccountService();
		$response     = $account_service->GetAccountInfo();
		$response_xml      = simplexml_load_string( $response );

		if ( 'trial' !== (string) $response_xml->account->accounttype && 'false' === (string) $response_xml->account->strictlimit ) {
			return 1;
		} else {
			$reg_limit = (int) $response_xml->account->reglimit;
			$reg_usage = (int) $response_xml->account->usage->regcount;
			return $reg_limit - $reg_usage;
		}
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
}

