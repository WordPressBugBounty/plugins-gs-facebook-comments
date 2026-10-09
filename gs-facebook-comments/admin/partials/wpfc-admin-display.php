<?php
/**
 * Settings page markup.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
?>
<div class="wrap wpfc_wrap genialsouls-social-comments-admin">
	<header class="gsc-page-header">
		<div class="gsc-page-brand">
			<span class="dashicons dashicons-admin-comments" aria-hidden="true"></span>
			<div>
				<h1><?php esc_html_e( 'Genial Social Comments', 'gs-facebook-comments' ); ?></h1>
				<p><?php esc_html_e( 'Engage your audience with popular comment providers.', 'gs-facebook-comments' ); ?></p>
			</div>
		</div>
		<?php /* translators: Plugin version number. */ ?>
		<span class="gsc-version"><?php echo esc_html( sprintf( __( 'Version %s', 'gs-facebook-comments' ), $this->version ) ); ?></span>
	</header>

	<?php $this->render_facebook_notice(); ?>
	<?php $this->render_provider_warning(); ?>
	<?php settings_errors( 'wpfc_settings' ); ?>

	<div class="gsc-admin-grid">
		<main class="gsc-main-column">
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wpfc_options' );
				do_settings_sections( 'wpfc_options' );
				submit_button( __( 'Save Settings', 'gs-facebook-comments' ), 'primary gsc-save-button' );
				?>
			</form>
		</main>

		<aside class="gsc-sidebar" aria-label="<?php esc_attr_e( 'Plugin information', 'gs-facebook-comments' ); ?>">
			<section class="gsc-sidebar-card">
				<h2><?php esc_html_e( 'About Genial Social Comments', 'gs-facebook-comments' ); ?></h2>
				<p><?php esc_html_e( 'Choose how comments are presented on your public content while keeping your existing settings and WordPress comments safe.', 'gs-facebook-comments' ); ?></p>
				<ul class="gsc-check-list">
					<li><?php esc_html_e( 'Facebook settings retained for compatibility', 'gs-facebook-comments' ); ?></li>
					<li><?php esc_html_e( 'Disqus lazy-loading support', 'gs-facebook-comments' ); ?></li>
					<li><?php esc_html_e( 'Existing shortcode preserved', 'gs-facebook-comments' ); ?></li>
				</ul>
			</section>

			<section class="gsc-sidebar-card">
				<h2><?php esc_html_e( 'Disqus account', 'gs-facebook-comments' ); ?></h2>
				<p><?php esc_html_e( 'Disqus is an external commenting service. Its advertising, privacy, and account features are controlled by Disqus and your selected plan.', 'gs-facebook-comments' ); ?></p>
				<a class="button" href="https://disqus.com/admin/create/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Create a Disqus site', 'gs-facebook-comments' ); ?></a>
			</section>

			<section class="gsc-sidebar-card">
				<h2><?php esc_html_e( 'Information', 'gs-facebook-comments' ); ?></h2>
				<p><a href="https://disqus.com/terms/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Disqus Terms', 'gs-facebook-comments' ); ?></a></p>
				<p><a href="https://disqus.com/privacy-policy/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Disqus Privacy Policy', 'gs-facebook-comments' ); ?></a></p>
				<p><a href="https://wordpress.org/plugins/gs-facebook-comments/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WordPress.org plugin page', 'gs-facebook-comments' ); ?></a></p>
				<?php /* translators: Plugin version number. */ ?>
				<p class="gsc-sidebar-version"><?php echo esc_html( sprintf( __( 'Version %s', 'gs-facebook-comments' ), $this->version ) ); ?></p>
			</section>
		</aside>
	</div>
</div>
