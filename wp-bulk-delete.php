<?php
/**
 * Plugin Name:       WP Bulk Delete
 * Plugin URI:        http://xylusthemes.com/plugins/wp-bulk-delete/
 * Description:       Bulk delete and cleanup anything like posts, comments, users, meta fields, taxonomy terms. with powerful filter options.
 * Version:           1.2.8
 * Author:            Xylus Themes
 * Author URI:        http://xylusthemes.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-bulk-delete
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'WP_Bulk_Delete' ) ):

/**
* Main WP Bulk Delete class
*/
class WP_Bulk_Delete{
	
	/** Singleton *************************************************************/
	/**
	 * WP_Bulk_Delete The one true WP_Bulk_Delete.
	 */
	private static $instance;
	public $api;

    /**
     * Main WP Bulk Delete Instance.
     * 
     * Insure that only one instance of WP_Bulk_Delete exists in memory at any one time.
     * Also prevents needing to define globals all over the place.
     *
     * @since 1.0.0
     * @static object $instance
     * @uses WP_Bulk_Delete::setup_constants() Setup the constants needed.
     * @uses WP_Bulk_Delete::includes() Include the required files.
     * @uses WP_Bulk_Delete::laod_textdomain() load the language files.
     * @see wpbulkdelete()
     * @return object| WP Bulk Delete the one true WP Bulk Delete.
     */
	public static function instance() {
		if( ! isset( self::$instance ) && ! (self::$instance instanceof WP_Bulk_Delete ) ) {
			self::$instance = new WP_Bulk_Delete();
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->includes();
			self::$instance->api = new WPBD_Delete_API();
		}
		return self::$instance;	
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent WP_Bulk_Delete from being loaded more than once.
	 *
	 * @since 1.0.0
	 * @see WP_Bulk_Delete::instance()
	 * @see wpbulkdelete()
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent WP_Bulk_Delete from being cloned.
	 *
	 * @since 1.0.0
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-bulk-delete' ), '1.2.8' ); }

	/**
	 * A dummy magic method to prevent WP_Bulk_Delete from being unserialized.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-bulk-delete' ), '1.2.8' ); }


	/**
	 * Setup plugins constants.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if( ! defined( 'WPBD_VERSION' ) ){
			define( 'WPBD_VERSION', '1.2.8' );
		}

		// Plugin folder Path.
		if( ! defined( 'WPBD_PLUGIN_DIR' ) ){
			define( 'WPBD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL.
		if( ! defined( 'WPBD_PLUGIN_URL' ) ){
			define( 'WPBD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin root file.
		if( ! defined( 'WPBD_PLUGIN_FILE' ) ){
			define( 'WPBD_PLUGIN_FILE', __FILE__ );
		}
		// Pro plugin Buy now Link.
		if( ! defined( 'WPBD_PLUGIN_BUY_NOW_URL' ) ){
			define( 'WPBD_PLUGIN_BUY_NOW_URL', 'https://xylusthemes.com/plugins/wp-bulk-delete/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin' );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function includes() {
		require_once WPBD_PLUGIN_DIR . 'includes/scripts.php';
		require_once WPBD_PLUGIN_DIR . 'includes/class-delete-api.php';
		require_once WPBD_PLUGIN_DIR . 'includes/common-functions.php';
		require_once WPBD_PLUGIN_DIR . 'includes/ajax-functions.php';
		require_once WPBD_PLUGIN_DIR . 'includes/delele-posts-form-functions.php';
		require_once WPBD_PLUGIN_DIR . 'includes/delele-users-form-functions.php';
		require_once WPBD_PLUGIN_DIR . 'includes/delele-comments-form-functions.php';
		require_once WPBD_PLUGIN_DIR . 'includes/delele-meta-form-functions.php';
		require_once WPBD_PLUGIN_DIR . 'includes/delele-terms-form-functions.php';
		require_once WPBD_PLUGIN_DIR . 'includes/class-wpbd-plugin-deactivation.php';
		require_once WPBD_PLUGIN_DIR . 'includes/admin/admin-pages.php';
		require_once WPBD_PLUGIN_DIR . 'includes/admin/admin-sidebar.php';
		require_once WPBD_PLUGIN_DIR . 'includes/admin/posts/display-delete-posts.php';
		require_once WPBD_PLUGIN_DIR . 'includes/admin/comments/display-delete-comments.php';
		require_once WPBD_PLUGIN_DIR . 'includes/admin/users/display-delete-users.php';
		require_once WPBD_PLUGIN_DIR . 'includes/admin/meta/display-delete-meta.php';
		require_once WPBD_PLUGIN_DIR . 'includes/admin/terms/display-delete-terms.php';
		require_once WPBD_PLUGIN_DIR . 'includes/admin/cleanup/cleanup-form.php';
		require_once WPBD_PLUGIN_DIR . 'includes/admin/cleanup/cleanup-page.php';
		require_once WPBD_PLUGIN_DIR . 'includes/admin/support-page.php';
	}

	/**
	 * Loads the plugin language files.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain(){

		load_plugin_textdomain(
			'wp-bulk-delete',
			false,
			basename( dirname( __FILE__ ) ) . '/languages'
		);
	
	}
	
}

endif; // End If class exists check.

/**
 * The main function for that returns WP_Bulk_Delete
 *
 * The main function responsible for returning the one true WP_Bulk_Delete
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $wpbulkdelete = wpbulkdelete(); ?>
 *
 * @since 1.0.0
 * @return object|WP_Bulk_Delete The one true WP_Bulk_Delete Instance.
 */
function wpbulkdelete() {
	return WP_Bulk_Delete::instance();
}

// Get WP_Bulk_Delete Running.
wpbulkdelete();

/**
 * Check is pro active or not.
 *
 * @since  1.2.0
 * @return boolean
 */
function wpbd_is_pro() {
	if( !function_exists( 'is_plugin_active' ) ){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	if ( is_plugin_active( 'wp-bulk-delete-pro/wp-bulk-delete-pro.php' ) ) {
		return true;
	}
	return false;
}