<?php
namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job_Post_Type {

	private static $instance = null;
	const POST_TYPE_SLUG = 'job_listing';

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'manage_' . self::POST_TYPE_SLUG . '_posts_columns', array( $this, 'admin_columns' ) );
		add_action( 'manage_' . self::POST_TYPE_SLUG . '_posts_custom_column', array( $this, 'admin_column_content' ), 10, 2 );
	}

	public static function register_post_type() {
		$labels = array(
			'name'               => __( 'Job Listings', 'online-job-board' ),
			'singular_name'      => __( 'Job Listing', 'online-job-board' ),
			'add_new'            => __( 'Add New Job', 'online-job-board' ),
			'add_new_item'       => __( 'Add New Job Listing', 'online-job-board' ),
			'edit_item'          => __( 'Edit Job Listing', 'online-job-board' ),
			'new_item'           => __( 'New Job Listing', 'online-job-board' ),
			'view_item'          => __( 'View Job Listing', 'online-job-board' ),
			'search_items'       => __( 'Search Jobs', 'online-job-board' ),
			'not_found'          => __( 'No jobs found', 'online-job-board' ),
			'not_found_in_trash' => __( 'No jobs found in trash', 'online-job-board' ),
			'menu_name'          => __( 'Job Board', 'online-job-board' ),
			'all_items'          => __( 'All Job Listings', 'online-job-board' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'has_archive'         => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'jobs' ),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => 25,
			'menu_icon'           => 'dashicons-businessman',
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest'        => true,
		);

		register_post_type( self::POST_TYPE_SLUG, $args );
	}

	public function admin_columns( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;

			if ( 'title' === $key ) {
				$new_columns['company']  = __( 'Company', 'online-job-board' );
				$new_columns['location'] = __( 'Location', 'online-job-board' );
				$new_columns['deadline'] = __( 'Application Deadline', 'online-job-board' );
			}
		}

		return $new_columns;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'company':
				$company = get_post_meta( $post_id, '_job_company_name', true );
				echo $company ? esc_html( $company ) : '—';
				break;

			case 'location':
				$location = get_post_meta( $post_id, '_job_company_address', true );
				echo $location ? esc_html( $location ) : '—';
				break;

			case 'deadline':
				$deadline = get_post_meta( $post_id, '_job_deadline', true );
				if ( $deadline ) {
					$timestamp = strtotime( $deadline );
					$expired   = $timestamp < time();
					$class     = $expired ? 'style="color: red;"' : '';
					echo '<span ' . $class . '>';
					echo esc_html( date_i18n( get_option( 'date_format' ), $timestamp ) );
					if ( $expired ) {
						echo ' (' . __( 'Expired', 'online-job-board' ) . ')';
					}
					echo '</span>';
				} else {
					echo '—';
				}
				break;
		}
	}
}