<?php
namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_API {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_meta_fields' ) );
	}

	public function register_meta_fields() {
		register_rest_field(
			'job_listing',
			'meta',
			array(
				'get_callback' => array( $this, 'get_job_meta' ),
				'schema'       => null,
			)
		);
	}

	public function get_job_meta( $object ) {
		$job_id = $object['id'];

		return array(
			'_job_company_name'    => get_post_meta( $job_id, '_job_company_name', true ),
			'_job_company_address' => get_post_meta( $job_id, '_job_company_address', true ),
			'_job_deadline'        => get_post_meta( $job_id, '_job_deadline', true ),
			'_job_closing_date'    => get_post_meta( $job_id, '_job_closing_date', true ),
		);
	}
}