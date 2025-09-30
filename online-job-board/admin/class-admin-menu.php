<?php
namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Menu {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
	}

	public function register_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=' . Job_Post_Type::POST_TYPE_SLUG,
			__( 'Job Applications', 'online-job-board' ),
			__( 'Applications', 'online-job-board' ),
			'manage_options',
			'job-applications',
			array( 'OnlineJobBoard\Applications_List', 'render_page' )
		);
	}
}