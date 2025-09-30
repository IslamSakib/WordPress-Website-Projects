<?php
namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Applications_List {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_post_delete_job_application', array( $this, 'handle_delete' ) );
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'online-job-board' ) );
		}

		// Get filter
		$job_filter = isset( $_GET['job_id'] ) ? absint( $_GET['job_id'] ) : 0;

		// Get applications
		if ( $job_filter > 0 ) {
			$applications = Application_Database::get_applications_by_job( $job_filter );
		} else {
			$per_page = 20;
			$paged    = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
			$offset   = ( $paged - 1 ) * $per_page;

			$applications = Application_Database::get_all_applications( $per_page, $offset );
			$total        = Application_Database::get_total_count();
		}
		?>

		<div class="wrap">
			<h1><?php esc_html_e( 'Job Applications', 'online-job-board' ); ?></h1>

			<?php if ( isset( $_GET['deleted'] ) && 'success' === $_GET['deleted'] ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Application deleted successfully.', 'online-job-board' ); ?></p>
				</div>
			<?php endif; ?>

			<!-- Filter -->
			<div class="tablenav top">
				<div class="alignleft actions">
					<select name="job_id" id="job-filter">
						<option value="0"><?php esc_html_e( 'All Jobs', 'online-job-board' ); ?></option>
						<?php
						$jobs = get_posts(
							array(
								'post_type'      => Job_Post_Type::POST_TYPE_SLUG,
								'posts_per_page' => -1,
								'post_status'    => 'publish',
								'orderby'        => 'title',
								'order'          => 'ASC',
							)
						);

						foreach ( $jobs as $job ) :
							$count    = Application_Database::get_total_count( $job->ID );
							$selected = ( $job_filter === $job->ID ) ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr( $job->ID ); ?>" <?php echo esc_attr( $selected ); ?>>
								<?php echo esc_html( $job->post_title ); ?> (<?php echo esc_html( $count ); ?>)
							</option>
						<?php endforeach; ?>
					</select>
					<button type="button" id="filter-btn" class="button"><?php esc_html_e( 'Filter', 'online-job-board' ); ?></button>
				</div>
			</div>

			<!-- Table -->
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Candidate Name', 'online-job-board' ); ?></th>
						<th><?php esc_html_e( 'Job Position', 'online-job-board' ); ?></th>
						<th><?php esc_html_e( 'Email', 'online-job-board' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'online-job-board' ); ?></th>
						<th><?php esc_html_e( 'Submitted', 'online-job-board' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'online-job-board' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $applications ) ) : ?>
						<?php foreach ( $applications as $app ) : ?>
							<?php $job_title = get_the_title( $app['job_id'] ); ?>
							<tr>
								<td><strong><?php echo esc_html( $app['full_name'] ); ?></strong></td>
								<td><?php echo esc_html( $job_title ); ?></td>
								<td><a href="mailto:<?php echo esc_attr( $app['email'] ); ?>"><?php echo esc_html( $app['email'] ); ?></a></td>
								<td><?php echo esc_html( $app['phone'] ); ?></td>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $app['submitted_at'] ) ) ); ?></td>
								<td>
									<a href="#TB_inline?width=600&height=550&inlineId=app-detail-<?php echo esc_attr( $app['id'] ); ?>" class="thickbox button button-small">
										<?php esc_html_e( 'View', 'online-job-board' ); ?>
									</a>
									<a href="<?php echo esc_url( $app['resume_url'] ); ?>" class="button button-small" target="_blank">
										<?php esc_html_e( 'Resume', 'online-job-board' ); ?>
									</a>
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=delete_job_application&app_id=' . $app['id'] ), 'delete_app_' . $app['id'] ) ); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'online-job-board' ); ?>');">
										<?php esc_html_e( 'Delete', 'online-job-board' ); ?>
									</a>

									<!-- Detail Modal -->
									<div id="app-detail-<?php echo esc_attr( $app['id'] ); ?>" style="display:none;">
										<div style="padding: 20px;">
											<h2><?php esc_html_e( 'Application Details', 'online-job-board' ); ?></h2>
											<table class="form-table">
												<tr>
													<th><?php esc_html_e( 'Name:', 'online-job-board' ); ?></th>
													<td><?php echo esc_html( $app['full_name'] ); ?></td>
												</tr>
												<tr>
													<th><?php esc_html_e( 'Email:', 'online-job-board' ); ?></th>
													<td><?php echo esc_html( $app['email'] ); ?></td>
												</tr>
												<tr>
													<th><?php esc_html_e( 'Phone:', 'online-job-board' ); ?></th>
													<td><?php echo esc_html( $app['phone'] ); ?></td>
												</tr>
												<tr>
													<th><?php esc_html_e( 'Job:', 'online-job-board' ); ?></th>
													<td><?php echo esc_html( $job_title ); ?></td>
												</tr>
												<tr>
													<th><?php esc_html_e( 'Cover Letter:', 'online-job-board' ); ?></th>
													<td><?php echo nl2br( esc_html( $app['cover_letter'] ) ); ?></td>
												</tr>
												<tr>
													<th><?php esc_html_e( 'Resume:', 'online-job-board' ); ?></th>
													<td><a href="<?php echo esc_url( $app['resume_url'] ); ?>" target="_blank" class="button"><?php esc_html_e( 'Download', 'online-job-board' ); ?></a></td>
												</tr>
											</table>
										</div>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="6" style="text-align: center;">
								<?php esc_html_e( 'No applications found.', 'online-job-board' ); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php
			// Pagination
			if ( 0 === $job_filter && isset( $total ) && $total > 20 ) :
				$pages = ceil( $total / 20 );
				if ( $pages > 1 ) :
					?>
					<div class="tablenav bottom">
						<div class="tablenav-pages">
							<?php
							echo wp_kses_post(
								paginate_links(
									array(
										'base'    => add_query_arg( 'paged', '%#%' ),
										'format'  => '',
										'current' => $paged,
										'total'   => $pages,
									)
								)
							);
							?>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#filter-btn').on('click', function() {
				var jobId = $('#job-filter').val();
				var url = '<?php echo esc_url( admin_url( 'edit.php?post_type=' . Job_Post_Type::POST_TYPE_SLUG . '&page=job-applications' ) ); ?>';
				if (jobId > 0) {
					url += '&job_id=' + jobId;
				}
				window.location.href = url;
			});
		});
		</script>
		<?php
	}

	public function handle_delete() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'online-job-board' ) );
		}

		$app_id = isset( $_GET['app_id'] ) ? absint( $_GET['app_id'] ) : 0;

		if ( ! $app_id ) {
			wp_die( esc_html__( 'Invalid application ID.', 'online-job-board' ) );
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'delete_app_' . $app_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'online-job-board' ) );
		}

		Application_Database::delete_application( $app_id );

		$redirect = add_query_arg(
			array(
				'post_type' => Job_Post_Type::POST_TYPE_SLUG,
				'page'      => 'job-applications',
				'deleted'   => 'success',
			),
			admin_url( 'edit.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}
}