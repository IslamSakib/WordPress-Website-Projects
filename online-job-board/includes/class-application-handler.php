<?php
namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Application_Handler {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_ojb_submit_application', array( $this, 'handle_application_submission' ) );
		add_action( 'wp_ajax_nopriv_ojb_submit_application', array( $this, 'handle_application_submission' ) );
	}

	public function handle_application_submission() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ojb_submit_application' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security verification failed. Please refresh and try again.', 'online-job-board' ),
			) );
		}

		// Validate required fields
		$required = array( 'job_id', 'full_name', 'email', 'phone', 'cover_letter' );
		foreach ( $required as $field ) {
			if ( empty( $_POST[ $field ] ) ) {
				wp_send_json_error( array(
					'message' => sprintf( __( 'Please fill in the %s field.', 'online-job-board' ), str_replace( '_', ' ', $field ) ),
				) );
			}
		}

		// Sanitize inputs
		$job_id       = absint( $_POST['job_id'] );
		$full_name    = sanitize_text_field( wp_unslash( $_POST['full_name'] ) );
		$email        = sanitize_email( wp_unslash( $_POST['email'] ) );
		$phone        = sanitize_text_field( wp_unslash( $_POST['phone'] ) );
		$cover_letter = sanitize_textarea_field( wp_unslash( $_POST['cover_letter'] ) );

		// Validate email
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please provide a valid email address.', 'online-job-board' ),
			) );
		}

		// Validate job exists
		$job = get_post( $job_id );
		if ( ! $job || Job_Post_Type::POST_TYPE_SLUG !== $job->post_type ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid job listing.', 'online-job-board' ),
			) );
		}

		// Check deadline
		$deadline = get_post_meta( $job_id, '_job_deadline', true );
		if ( $deadline && strtotime( $deadline ) < time() ) {
			wp_send_json_error( array(
				'message' => __( 'Application deadline has passed for this job.', 'online-job-board' ),
			) );
		}

		// Check closing date
		$closing_date = get_post_meta( $job_id, '_job_closing_date', true );
		if ( $closing_date && strtotime( $closing_date ) < time() ) {
			wp_send_json_error( array(
				'message' => __( 'This job posting has been closed.', 'online-job-board' ),
			) );
		}

		// Check for duplicate application
		if ( Application_Database::has_applied( $job_id, $email ) ) {
			wp_send_json_error( array(
				'message' => __( 'You have already applied for this position.', 'online-job-board' ),
			) );
		}

		// Validate resume file
		if ( empty( $_FILES['resume'] ) || empty( $_FILES['resume']['name'] ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please upload your resume in PDF format.', 'online-job-board' ),
			) );
		}

		// Check file type
		$file_info = wp_check_filetype( sanitize_file_name( wp_unslash( $_FILES['resume']['name'] ) ) );
		if ( 'pdf' !== $file_info['ext'] ) {
			wp_send_json_error( array(
				'message' => __( 'Only PDF files are allowed. Please upload a PDF resume.', 'online-job-board' ),
			) );
		}

		// Check file size (max 5MB)
		if ( $_FILES['resume']['size'] > 5242880 ) {
			wp_send_json_error( array(
				'message' => __( 'File size exceeds 5MB limit. Please upload a smaller file.', 'online-job-board' ),
			) );
		}

		// Upload resume
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$upload_dir = wp_upload_dir();
		$ojb_upload_dir = $upload_dir['basedir'] . '/job-resumes';

		// Create directory if not exists
		if ( ! file_exists( $ojb_upload_dir ) ) {
			wp_mkdir_p( $ojb_upload_dir );
		}

		$upload_overrides = array(
			'test_form' => false,
			'mimes'     => array( 'pdf' => 'application/pdf' ),
		);

		$uploaded = wp_handle_upload( $_FILES['resume'], $upload_overrides );

		if ( isset( $uploaded['error'] ) ) {
			wp_send_json_error( array(
				'message' => $uploaded['error'],
			) );
		}

		// Prepare application data
		$application_data = array(
			'job_id'       => $job_id,
			'full_name'    => $full_name,
			'email'        => $email,
			'phone'        => $phone,
			'cover_letter' => $cover_letter,
			'resume_url'   => $uploaded['url'],
		);

		// Insert into database
		$app_id = Application_Database::insert_application( $application_data );

		if ( ! $app_id ) {
			// Delete uploaded file on failure
			wp_delete_file( $uploaded['file'] );

			wp_send_json_error( array(
				'message' => __( 'Failed to submit application. Please try again.', 'online-job-board' ),
			) );
		}

		// Send email notification
		Email_Service::send_application_confirmation( $application_data, $job );

		// Success response
		wp_send_json_success( array(
			'message' => __( 'Application submitted successfully! Check your email for confirmation.', 'online-job-board' ),
		) );
	}
}