<?php
namespace OnlineJobBoard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Service {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
	}

	public function set_html_content_type() {
		return 'text/html';
	}

	public static function send_application_confirmation( $data, $job ) {
		$to      = $data['email'];
		$subject = sprintf(
			__( 'Application Received - %s', 'online-job-board' ),
			get_the_title( $job->ID )
		);

		$company_name = get_post_meta( $job->ID, '_job_company_name', true );
		$company_addr = get_post_meta( $job->ID, '_job_company_address', true );

		ob_start();
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #2271b1; color: #fff; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
				.content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
				.job-info { background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #2271b1; }
				.footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 14px; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php esc_html_e( 'Application Received!', 'online-job-board' ); ?></h1>
				</div>
				<div class="content">
					<p><?php printf( esc_html__( 'Dear %s,', 'online-job-board' ), esc_html( $data['full_name'] ) ); ?></p>

					<p><?php esc_html_e( 'Thank you for applying! We have successfully received your application for:', 'online-job-board' ); ?></p>

					<div class="job-info">
						<h2><?php echo esc_html( get_the_title( $job->ID ) ); ?></h2>
						<p><strong><?php esc_html_e( 'Company:', 'online-job-board' ); ?></strong> <?php echo esc_html( $company_name ); ?></p>
						<p><strong><?php esc_html_e( 'Location:', 'online-job-board' ); ?></strong> <?php echo esc_html( $company_addr ); ?></p>
					</div>

					<h3><?php esc_html_e( 'Your Application Details:', 'online-job-board' ); ?></h3>
					<ul>
						<li><strong><?php esc_html_e( 'Name:', 'online-job-board' ); ?></strong> <?php echo esc_html( $data['full_name'] ); ?></li>
						<li><strong><?php esc_html_e( 'Email:', 'online-job-board' ); ?></strong> <?php echo esc_html( $data['email'] ); ?></li>
						<li><strong><?php esc_html_e( 'Phone:', 'online-job-board' ); ?></strong> <?php echo esc_html( $data['phone'] ); ?></li>
						<li><strong><?php esc_html_e( 'Submitted:', 'online-job-board' ); ?></strong> <?php echo esc_html( current_time( 'F j, Y - g:i a' ) ); ?></li>
					</ul>

					<p><?php esc_html_e( 'We will review your application and contact you if your qualifications match our requirements.', 'online-job-board' ); ?></p>

					<p><?php esc_html_e( 'Best regards,', 'online-job-board' ); ?><br>
					<strong><?php echo esc_html( $company_name ); ?></strong></p>

					<div class="footer">
						<p><?php esc_html_e( 'This is an automated email. Please do not reply directly.', 'online-job-board' ); ?></p>
					</div>
				</div>
			</div>
		</body>
		</html>
		<?php
		$message = ob_get_clean();

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		return wp_mail( $to, $subject, $message, $headers );
	}
}