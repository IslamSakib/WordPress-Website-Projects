<?php
/**
 * Plugin Name: Job Board by Sakib Islam
 * Description: Manage job listings and applications with resume uploads.
 * Version: 1.4.1
 * Author: Sakib Islam
 * License: GPL v2 or later
 * Text Domain: online-job-board
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

namespace OnlineJobBoard;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'OJB_VERSION', '1.4.1' );
define( 'OJB_PLUGIN_FILE', __FILE__ );
define( 'OJB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OJB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'OJB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

final class Job_Board_Plugin {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->include_files();
		$this->init_hooks();
	}

	private function include_files() {
		require_once OJB_PLUGIN_DIR . 'includes/class-job-post-type.php';
		require_once OJB_PLUGIN_DIR . 'includes/class-job-meta-fields.php';
		require_once OJB_PLUGIN_DIR . 'includes/class-application-database.php';
		require_once OJB_PLUGIN_DIR . 'includes/class-application-handler.php';
		require_once OJB_PLUGIN_DIR . 'includes/class-email-service.php';
		require_once OJB_PLUGIN_DIR . 'includes/class-rest-api.php';
		require_once OJB_PLUGIN_DIR . 'includes/class-frontend-display-react.php';
		require_once OJB_PLUGIN_DIR . 'includes/class-assets-loader-react.php';
		require_once OJB_PLUGIN_DIR . 'admin/class-admin-menu.php';
		require_once OJB_PLUGIN_DIR . 'admin/class-applications-list.php';
	}

	private function init_hooks() {
		register_activation_hook( OJB_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( OJB_PLUGIN_FILE, array( $this, 'deactivate' ) );
		add_action( 'plugins_loaded', array( $this, 'initialize_components' ) );
		add_filter( 'site_transient_update_plugins', array( $this, 'disable_plugin_updates' ) );
		add_filter( 'auto_update_plugin', array( $this, 'disable_auto_updates' ), 10, 2 );
	}

	public function activate() {
		Application_Database::create_application_table();
		Job_Post_Type::register_post_type();
		flush_rewrite_rules();
		update_option( 'ojb_plugin_version', OJB_VERSION );
	}

	public function deactivate() {
		flush_rewrite_rules();
	}

	public function initialize_components() {
		Job_Post_Type::instance();
		Job_Meta_Fields::instance();
		Application_Handler::instance();
		Email_Service::instance();
		REST_API::instance();
		Frontend_Display_React::instance();
		Assets_Loader_React::instance();

		if ( is_admin() ) {
			Admin_Menu::instance();
			Applications_List::instance();
		}
	}

	public function disable_plugin_updates( $transient ) {
		if ( isset( $transient->response[ OJB_PLUGIN_BASENAME ] ) ) {
			unset( $transient->response[ OJB_PLUGIN_BASENAME ] );
		}
		return $transient;
	}

	public function disable_auto_updates( $update, $item ) {
		if ( isset( $item->plugin ) && OJB_PLUGIN_BASENAME === $item->plugin ) {
			return false;
		}
		return $update;
	}
}

function job_board() {
	return Job_Board_Plugin::instance();
}

job_board();