<?php
namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets_Loader_React {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets() {
		wp_enqueue_script( 'wp-element' );
		wp_enqueue_script( 'wp-api-fetch' );

		wp_enqueue_script(
			'ojb-react-app',
			OJB_PLUGIN_URL . 'assets/js/react-app.js',
			array( 'wp-element', 'wp-api-fetch' ),
			OJB_VERSION,
			true
		);

		wp_enqueue_style(
			'ojb-frontend-styles',
			OJB_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			OJB_VERSION
		);

		wp_localize_script(
			'ojb-react-app',
			'ojbData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ojb_submit_application' ),
				'apiUrl'  => rest_url(),
			)
		);
	}
}