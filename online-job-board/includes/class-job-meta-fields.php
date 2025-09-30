<?php
namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job_Meta_Fields {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_job_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_job_meta_data' ), 10, 2 );
	}

	public function add_job_meta_box() {
		add_meta_box(
			'job_details_meta_box',
			__( 'Job Details', 'online-job-board' ),
			array( $this, 'render_meta_box' ),
			Job_Post_Type::POST_TYPE_SLUG,
			'normal',
			'high'
		);
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'save_job_meta_data', 'job_meta_nonce' );

		$company_name = get_post_meta( $post->ID, '_job_company_name', true );
		$company_addr = get_post_meta( $post->ID, '_job_company_address', true );
		$deadline     = get_post_meta( $post->ID, '_job_deadline', true );
		?>

		<style>
			.job-meta-field { margin-bottom: 20px; }
			.job-meta-field label { display: block; font-weight: 600; margin-bottom: 8px; }
			.job-meta-field input[type="text"],
			.job-meta-field input[type="date"],
			.job-meta-field textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
			.job-meta-field textarea { resize: vertical; min-height: 80px; }
			.required-mark { color: #dc3232; }
		</style>

		<div class="job-meta-fields-wrapper">
			<div class="job-meta-field">
				<label for="job_company_name">
					<?php esc_html_e( 'Company Name', 'online-job-board' ); ?>
					<span class="required-mark">*</span>
				</label>
				<input
					type="text"
					id="job_company_name"
					name="job_company_name"
					value="<?php echo esc_attr( $company_name ); ?>"
					required
				/>
			</div>

			<div class="job-meta-field">
				<label for="job_company_address">
					<?php esc_html_e( 'Company Address', 'online-job-board' ); ?>
					<span class="required-mark">*</span>
				</label>
				<textarea
					id="job_company_address"
					name="job_company_address"
					rows="3"
					required
				><?php echo esc_textarea( $company_addr ); ?></textarea>
			</div>

			<div class="job-meta-field">
				<label for="job_deadline">
					<?php esc_html_e( 'Application Deadline', 'online-job-board' ); ?>
					<span class="required-mark">*</span>
				</label>
				<input
					type="date"
					id="job_deadline"
					name="job_deadline"
					value="<?php echo esc_attr( $deadline ); ?>"
					min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>"
					required
				/>
				<p class="description">
					<?php esc_html_e( 'Set the last date for accepting applications.', 'online-job-board' ); ?>
				</p>
			</div>

			<div class="job-meta-field">
				<label for="job_closing_date">
					<?php esc_html_e( 'Job Closing Date', 'online-job-board' ); ?>
					<span class="required-mark">*</span>
				</label>
				<input
					type="date"
					id="job_closing_date"
					name="job_closing_date"
					value="<?php echo esc_attr( get_post_meta( $post->ID, '_job_closing_date', true ) ); ?>"
					min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>"
					required
				/>
				<p class="description">
					<?php esc_html_e( 'Set the date when this job posting will close and no longer be visible.', 'online-job-board' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	public function save_job_meta_data( $post_id, $post ) {
		// Security checks
		if ( ! isset( $_POST['job_meta_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['job_meta_nonce'] ) ), 'save_job_meta_data' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( Job_Post_Type::POST_TYPE_SLUG !== $post->post_type ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save company name
		if ( isset( $_POST['job_company_name'] ) ) {
			update_post_meta(
				$post_id,
				'_job_company_name',
				sanitize_text_field( wp_unslash( $_POST['job_company_name'] ) )
			);
		}

		// Save company address
		if ( isset( $_POST['job_company_address'] ) ) {
			update_post_meta(
				$post_id,
				'_job_company_address',
				sanitize_textarea_field( wp_unslash( $_POST['job_company_address'] ) )
			);
		}

		// Save deadline
		if ( isset( $_POST['job_deadline'] ) ) {
			update_post_meta(
				$post_id,
				'_job_deadline',
				sanitize_text_field( wp_unslash( $_POST['job_deadline'] ) )
			);
		}

		// Save closing date
		if ( isset( $_POST['job_closing_date'] ) ) {
			update_post_meta(
				$post_id,
				'_job_closing_date',
				sanitize_text_field( wp_unslash( $_POST['job_closing_date'] ) )
			);
		}
	}
}