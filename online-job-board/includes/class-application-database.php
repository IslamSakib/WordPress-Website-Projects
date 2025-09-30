<?php
namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Application_Database {

	private static $table_name = 'job_applications';

	public static function create_application_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . self::$table_name;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			job_id BIGINT(20) UNSIGNED NOT NULL,
			full_name VARCHAR(255) NOT NULL,
			email VARCHAR(255) NOT NULL,
			phone VARCHAR(50) NOT NULL,
			cover_letter TEXT NOT NULL,
			resume_url VARCHAR(500) NOT NULL,
			submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
			status VARCHAR(20) DEFAULT 'new' NOT NULL,
			PRIMARY KEY (id),
			KEY job_id (job_id),
			KEY email (email),
			KEY submitted_at (submitted_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::$table_name;
	}

	public static function insert_application( $data ) {
		global $wpdb;

		$table = self::get_table_name();

		$insert_data = array(
			'job_id'       => absint( $data['job_id'] ),
			'full_name'    => sanitize_text_field( $data['full_name'] ),
			'email'        => sanitize_email( $data['email'] ),
			'phone'        => sanitize_text_field( $data['phone'] ),
			'cover_letter' => sanitize_textarea_field( $data['cover_letter'] ),
			'resume_url'   => esc_url_raw( $data['resume_url'] ),
			'submitted_at' => current_time( 'mysql' ),
			'status'       => 'new',
		);

		$format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

		$result = $wpdb->insert( $table, $insert_data, $format );

		return $result ? $wpdb->insert_id : false;
	}

	public static function has_applied( $job_id, $email ) {
		global $wpdb;

		$table = self::get_table_name();

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE job_id = %d AND email = %s",
				absint( $job_id ),
				sanitize_email( $email )
			)
		);

		return $count > 0;
	}

	public static function get_all_applications( $limit = 20, $offset = 0 ) {
		global $wpdb;

		$table = self::get_table_name();

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY submitted_at DESC LIMIT %d OFFSET %d",
				absint( $limit ),
				absint( $offset )
			),
			ARRAY_A
		);

		return $results;
	}

	public static function get_applications_by_job( $job_id ) {
		global $wpdb;

		$table = self::get_table_name();

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE job_id = %d ORDER BY submitted_at DESC",
				absint( $job_id )
			),
			ARRAY_A
		);

		return $results;
	}

	public static function get_total_count( $job_id = 0 ) {
		global $wpdb;

		$table = self::get_table_name();

		if ( $job_id > 0 ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} WHERE job_id = %d",
					absint( $job_id )
				)
			);
		} else {
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		}

		return absint( $count );
	}

	public static function delete_application( $id ) {
		global $wpdb;

		$table = self::get_table_name();

		return $wpdb->delete(
			$table,
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);
	}
}