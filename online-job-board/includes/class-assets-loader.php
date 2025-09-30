<?php
/**
 * Assets Loader (CSS & JavaScript)
 *
 * @package OnlineJobBoard
 * @author Sakib Islam
 */

namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets Loader Class
 */
class Assets_Loader {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets' ) );
	}

	/**
	 * Load frontend assets
	 */
	public function load_frontend_assets() {
		// CSS
		wp_enqueue_style(
			'ojb-frontend-styles',
			OJB_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			OJB_VERSION
		);

		// JavaScript
		wp_enqueue_script( 'jquery' );

		wp_enqueue_script(
			'ojb-frontend-scripts',
			OJB_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			OJB_VERSION,
			true
		);

		// Localize script for AJAX
		wp_localize_script(
			'ojb-frontend-scripts',
			'ojbData',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'job_application_nonce' ),
				'messages' => array(
					'submitting'       => __( 'Submitting...', 'online-job-board' ),
					'submit'           => __( 'Submit Application', 'online-job-board' ),
					'fillAllFields'    => __( 'Please fill in all required fields.', 'online-job-board' ),
					'invalidEmail'     => __( 'Please enter a valid email address.', 'online-job-board' ),
					'selectPDF'        => __( 'Please select a PDF file.', 'online-job-board' ),
					'pdfOnly'          => __( 'Only PDF files are allowed.', 'online-job-board' ),
					'fileTooLarge'     => __( 'File size must not exceed 5MB.', 'online-job-board' ),
					'errorOccurred'    => __( 'An error occurred. Please try again.', 'online-job-board' ),
				),
			)
		);
	}

	/**
	 * Load admin assets
	 */
	public function load_admin_assets( $hook ) {
		// Load only on applications page
		$screen = get_current_screen();

		if ( $screen && 'job_listing_page_job-applications' === $screen->id ) {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );
		}
	}
}