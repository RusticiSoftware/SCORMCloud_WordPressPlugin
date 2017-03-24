<?php

require_once( SCORMCLOUD_BASE . 'scormcloudplugin.php' );
require_once( SCORMCLOUD_BASE . 'ui/scormcloudcatalogwidget.php' );
require_once( SCORMCLOUD_BASE . 'ui/scormcloudregistrationswidget.php' );
require_once( SCORMCLOUD_BASE . 'admin/scormcloudadminui.php' );

/**
 * Class ScormCloudUi
 */
class ScormCloudUi {
	/**
	 * Initialize the ui elements of the scormcloud plugin.
	 */
	public static function initialize() {
		self::enqueue_includes();

		self::embed_plugin();

		ScormCloudAdminUi::initialize();
	}

	/**
	 * Initialize the widgets.
	 */
	public static function initialize_widgets() {
		register_widget( 'ScormCloudRegistrationsWidget' );
		register_widget( 'ScormCloudCatalogWidget' );
	}

	/**
	 * Equeue all of our styles and scripts.
	 */
	public static function enqueue_includes() {
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		wp_enqueue_script( 'scormclouddialog', plugins_url( '/scripts/scormcloud.dialog.js', __FILE__ ) );
		wp_enqueue_script( 'scormcloud-post', plugins_url( '/scripts/scormcloud.post.js', __FILE__ ) );
		wp_register_style( 'scormcloud-post-style', plugins_url( '/css/scormcloud.post.css', __FILE__ ) );
		wp_enqueue_style( 'scormcloud-post-style' );
	}

	/**
	 * Embed the editor button.
	 */
	public static function embed_plugin() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		add_filter( 'mce_external_plugins', array( __CLASS__, 'embed_editor_plugin' ) );
		add_filter( 'mce_buttons', array( __CLASS__, 'embed_editor_button' ) );
	}

	/**
	 * Embed the tinymce plugin.
	 *
	 * @param array $plugins array of plugins.
	 *
	 * @return mixed
	 */
	public static function embed_editor_plugin( $plugins ) {
		$plugins['scormCloudEmbed'] = plugins_url( '/tinymce3/editor_plugin.js', __FILE__ );

		return $plugins;
	}

	/**
	 * Add our editor button.
	 *
	 * @param array $buttons array of buttons.
	 *
	 * @return mixed
	 */
	public static function embed_editor_button( $buttons ) {
		array_push( $buttons, '|', 'scormCloudEmbed' );

		return $buttons;
	}

}