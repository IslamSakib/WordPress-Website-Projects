<?php
/**
 * Frontend Display & Shortcode
 *
 * @package OnlineJobBoard
 * @author Sakib Islam
 */

namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend Display Class
 */
class Frontend_Display {

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

	/**
	 * Render job board shortcode
	 */
	public function render_job_board( $atts ) {
		$atts = shortcode_atts(
			array(
				'per_page' => 6,
			),
			$atts
		);

		ob_start();

		// Get current page
		$current_page = isset( $_GET['jobpage'] ) ? absint( $_GET['jobpage'] ) : 1;

		// Get search query
		$search = isset( $_GET['jobsearch'] ) ? sanitize_text_field( wp_unslash( $_GET['jobsearch'] ) ) : '';

		// Query arguments
		$args = array(
			'post_type'      => Job_Post_Type::POST_TYPE_SLUG,
			'post_status'    => 'publish',
			'posts_per_page' => absint( $atts['per_page'] ),
			'paged'          => $current_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		$query = new \WP_Query( $args );
		?>

		<div class="ojb-job-board-container">
			<!-- Search Form -->
			<div class="ojb-search-wrapper">
				<form method="get" class="ojb-search-form">
					<?php
					// Preserve other query vars
					foreach ( $_GET as $key => $value ) {
						if ( ! in_array( $key, array( 'jobsearch', 'jobpage' ), true ) ) {
							echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
						}
					}
					?>
					<input
						type="text"
						name="jobsearch"
						class="ojb-search-input"
						placeholder="<?php esc_attr_e( 'Search jobs by title, company...', 'online-job-board' ); ?>"
						value="<?php echo esc_attr( $search ); ?>"
					>
					<button type="submit" class="ojb-search-btn">
						<?php esc_html_e( 'Search', 'online-job-board' ); ?>
					</button>
					<?php if ( ! empty( $search ) ) : ?>
						<a href="<?php echo esc_url( remove_query_arg( array( 'jobsearch', 'jobpage' ) ) ); ?>" class="ojb-clear-btn">
							<?php esc_html_e( 'Clear', 'online-job-board' ); ?>
						</a>
					<?php endif; ?>
				</form>
			</div>

			<?php if ( $query->have_posts() ) : ?>
				<div class="ojb-jobs-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						$this->render_job_card( get_the_ID() );
					endwhile;
					wp_reset_postdata();
					?>
				</div>

				<?php if ( $query->max_num_pages > 1 ) : ?>
					<div class="ojb-pagination">
						<?php
						$total_pages = $query->max_num_pages;
						$range = 2; // Number of pages to show on each side of current page

						// Previous
						if ( $current_page > 1 ) :
							$prev_url = add_query_arg( 'jobpage', $current_page - 1 );
							if ( ! empty( $search ) ) {
								$prev_url = add_query_arg( 'jobsearch', $search, $prev_url );
							}
							?>
							<a href="<?php echo esc_url( $prev_url ); ?>" class="ojb-page-link ojb-prev">
								<?php esc_html_e( '‹ Prev', 'online-job-board' ); ?>
							</a>
						<?php endif; ?>

						<?php
						// Smart pagination with ellipsis
						for ( $i = 1; $i <= $total_pages; $i++ ) :
							// Always show first page, last page, current page, and pages within range
							if ( $i === 1 || $i === $total_pages || ( $i >= $current_page - $range && $i <= $current_page + $range ) ) :
								$page_url = add_query_arg( 'jobpage', $i );
								if ( ! empty( $search ) ) {
									$page_url = add_query_arg( 'jobsearch', $search, $page_url );
								}
								$active = ( $i === $current_page ) ? 'ojb-active' : '';
								?>
								<a href="<?php echo esc_url( $page_url ); ?>" class="ojb-page-link ojb-page-num <?php echo esc_attr( $active ); ?>">
									<?php echo esc_html( $i ); ?>
								</a>
								<?php
							elseif ( $i === $current_page - $range - 1 || $i === $current_page + $range + 1 ) :
								// Show ellipsis
								?>
								<span class="ojb-page-ellipsis">…</span>
								<?php
							endif;
						endfor;
						?>

						<?php
						// Next
						if ( $current_page < $total_pages ) :
							$next_url = add_query_arg( 'jobpage', $current_page + 1 );
							if ( ! empty( $search ) ) {
								$next_url = add_query_arg( 'jobsearch', $search, $next_url );
							}
							?>
							<a href="<?php echo esc_url( $next_url ); ?>" class="ojb-page-link ojb-next">
								<?php esc_html_e( 'Next ›', 'online-job-board' ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

			<?php else : ?>
				<div class="ojb-no-jobs">
					<p><?php esc_html_e( 'No job listings found.', 'online-job-board' ); ?></p>
					<?php if ( ! empty( $search ) ) : ?>
						<a href="<?php echo esc_url( remove_query_arg( array( 'jobsearch', 'jobpage' ) ) ); ?>">
							<?php esc_html_e( 'View all jobs', 'online-job-board' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<!-- Job Details Modal -->
			<?php $this->render_job_details_modal(); ?>

			<!-- Application Modal -->
			<?php $this->render_application_modal(); ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Render job card
	 */
	private function render_job_card( $job_id ) {
		$company_name = get_post_meta( $job_id, '_job_company_name', true );
		$company_addr = get_post_meta( $job_id, '_job_company_address', true );
		$deadline     = get_post_meta( $job_id, '_job_deadline', true );
		$closing_date = get_post_meta( $job_id, '_job_closing_date', true );

		$is_expired = false;
		if ( $deadline && strtotime( $deadline ) < time() ) {
			$is_expired = true;
		}

		// Check if job is closed
		$is_closed = false;
		if ( $closing_date && strtotime( $closing_date ) < time() ) {
			$is_closed = true;
		}

		// Get short excerpt (2 lines max)
		$content        = get_post_field( 'post_content', $job_id );
		$excerpt        = wp_strip_all_tags( $content );
		$short_excerpt  = wp_trim_words( $excerpt, 20, '...' );
		?>

		<div class="ojb-job-card <?php echo ( $is_expired || $is_closed ) ? 'ojb-expired' : ''; ?>"
			data-job-id="<?php echo esc_attr( $job_id ); ?>"
			data-job-title="<?php echo esc_attr( get_the_title( $job_id ) ); ?>"
			data-company="<?php echo esc_attr( $company_name ); ?>"
			data-location="<?php echo esc_attr( $company_addr ); ?>"
			data-deadline="<?php echo esc_attr( $deadline ); ?>"
			data-closing-date="<?php echo esc_attr( $closing_date ); ?>"
			data-expired="<?php echo ( $is_expired || $is_closed ) ? '1' : '0'; ?>">

			<h3 class="ojb-job-title">
				<?php echo esc_html( get_the_title( $job_id ) ); ?>
				<?php if ( $is_closed ) : ?>
					<span class="ojb-badge-expired"><?php esc_html_e( 'Closed', 'online-job-board' ); ?></span>
				<?php elseif ( $is_expired ) : ?>
					<span class="ojb-badge-expired"><?php esc_html_e( 'Expired', 'online-job-board' ); ?></span>
				<?php endif; ?>
			</h3>

			<div class="ojb-job-meta">
				<p class="ojb-company">
					<strong><?php esc_html_e( 'Company:', 'online-job-board' ); ?></strong>
					<?php echo esc_html( $company_name ); ?>
				</p>

				<p class="ojb-location">
					<strong><?php esc_html_e( 'Location:', 'online-job-board' ); ?></strong>
					<?php echo esc_html( $company_addr ); ?>
				</p>

				<?php if ( $deadline ) : ?>
					<p class="ojb-deadline <?php echo $is_expired ? 'expired' : ''; ?>">
						<strong><?php esc_html_e( 'Deadline:', 'online-job-board' ); ?></strong>
						<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $deadline ) ) ); ?>
					</p>
				<?php endif; ?>

				<?php if ( $closing_date ) : ?>
					<p class="ojb-closing-date <?php echo $is_closed ? 'expired' : ''; ?>">
						<strong><?php esc_html_e( 'Closing Date:', 'online-job-board' ); ?></strong>
						<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $closing_date ) ) ); ?>
					</p>
				<?php endif; ?>
			</div>

			<div class="ojb-job-excerpt">
				<p><?php echo esc_html( $short_excerpt ); ?></p>
			</div>

			<div class="ojb-card-actions">
				<button class="ojb-view-details-btn" data-job-id="<?php echo esc_attr( $job_id ); ?>">
					<?php esc_html_e( 'View Details', 'online-job-board' ); ?>
				</button>
				<?php if ( ! $is_expired && ! $is_closed ) : ?>
					<button class="ojb-apply-btn" data-job-id="<?php echo esc_attr( $job_id ); ?>">
						<?php esc_html_e( 'Apply Now', 'online-job-board' ); ?>
					</button>
				<?php endif; ?>
			</div>

			<!-- Hidden full content -->
			<div class="ojb-full-content" style="display: none;">
				<?php echo wp_kses_post( apply_filters( 'the_content', $content ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render job details modal
	 */
	private function render_job_details_modal() {
		?>
		<div id="ojb-job-details-modal" class="ojb-modal" style="display: none;">
			<div class="ojb-modal-dialog ojb-modal-large">
				<div class="ojb-modal-header">
					<h3 id="ojb-detail-job-title"><?php esc_html_e( 'Job Details', 'online-job-board' ); ?></h3>
					<button type="button" class="ojb-modal-close">&times;</button>
				</div>

				<div class="ojb-modal-body">
					<div class="ojb-job-detail-content">
						<div class="ojb-job-info-box">
							<div class="ojb-info-item">
								<strong><?php esc_html_e( 'Company:', 'online-job-board' ); ?></strong>
								<span id="ojb-detail-company"></span>
							</div>
							<div class="ojb-info-item">
								<strong><?php esc_html_e( 'Location:', 'online-job-board' ); ?></strong>
								<span id="ojb-detail-location"></span>
							</div>
							<div class="ojb-info-item">
								<strong><?php esc_html_e( 'Application Deadline:', 'online-job-board' ); ?></strong>
								<span id="ojb-detail-deadline"></span>
							</div>
							<div class="ojb-info-item">
								<strong><?php esc_html_e( 'Closing Date:', 'online-job-board' ); ?></strong>
								<span id="ojb-detail-closing-date"></span>
							</div>
						</div>

						<div class="ojb-job-description">
							<h4><?php esc_html_e( 'Job Description', 'online-job-board' ); ?></h4>
							<div id="ojb-detail-description"></div>
						</div>

						<div class="ojb-detail-actions">
							<button type="button" class="ojb-apply-from-detail-btn" id="ojb-apply-from-detail">
								<?php esc_html_e( 'Apply for this Position', 'online-job-board' ); ?>
							</button>
							<button type="button" class="ojb-close-detail-btn">
								<?php esc_html_e( 'Close', 'online-job-board' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render application modal
	 */
	private function render_application_modal() {
		?>
		<div id="ojb-application-modal" class="ojb-modal" style="display: none;">
			<div class="ojb-modal-dialog">
				<div class="ojb-modal-header">
					<h3><?php esc_html_e( 'Apply for this Position', 'online-job-board' ); ?></h3>
					<button type="button" class="ojb-modal-close">&times;</button>
				</div>

				<div class="ojb-modal-body">
					<form id="ojb-application-form" enctype="multipart/form-data">
						<input type="hidden" name="job_id" id="ojb-job-id" value="">

						<div class="ojb-form-field">
							<label for="full_name">
								<?php esc_html_e( 'Full Name', 'online-job-board' ); ?>
								<span class="required">*</span>
							</label>
							<input type="text" id="full_name" name="full_name" required>
						</div>

						<div class="ojb-form-field">
							<label for="email">
								<?php esc_html_e( 'Email Address', 'online-job-board' ); ?>
								<span class="required">*</span>
							</label>
							<input type="email" id="email" name="email" required>
						</div>

						<div class="ojb-form-field">
							<label for="phone">
								<?php esc_html_e( 'Phone Number', 'online-job-board' ); ?>
								<span class="required">*</span>
							</label>
							<input type="tel" id="phone" name="phone" required>
						</div>

						<div class="ojb-form-field full-width">
							<label for="cover_letter">
								<?php esc_html_e( 'Cover Letter', 'online-job-board' ); ?>
								<span class="required">*</span>
							</label>
							<textarea id="cover_letter" name="cover_letter" rows="5" required></textarea>
						</div>

						<div class="ojb-form-field full-width">
							<label for="resume">
								<?php esc_html_e( 'Resume (PDF only)', 'online-job-board' ); ?>
								<span class="required">*</span>
							</label>
							<input type="file" id="resume" name="resume" accept=".pdf" required>
							<small><?php esc_html_e( 'Maximum file size: 5MB', 'online-job-board' ); ?></small>
						</div>

						<div id="ojb-form-message" class="ojb-message full-width" style="display: none;"></div>

						<div class="ojb-form-actions full-width">
							<button type="submit" class="ojb-submit-btn">
								<?php esc_html_e( 'Submit Application', 'online-job-board' ); ?>
							</button>
							<button type="button" class="ojb-cancel-btn">
								<?php esc_html_e( 'Close', 'online-job-board' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
	}
}