<?php
namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend_Display_React {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'job_board', array( $this, 'render_job_board' ) );
	}

	public function render_job_board( $atts ) {
		$atts = shortcode_atts(
			array(
				'per_page' => 6,
			),
			$atts
		);

		return sprintf(
			'<div id="ojb-job-board-root" data-per-page="%d"></div>',
			absint( $atts['per_page'] )
		);
	}
}