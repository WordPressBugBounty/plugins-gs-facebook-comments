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
				<h1><?php esc_html_e( 'Genial Comments', 'gs-facebook-comments' ); ?></h1>
				<p><?php esc_html_e( 'Choose Disqus or your theme’s WordPress comments; native appearance styling is optional.', 'gs-facebook-comments' ); ?></p>
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
				<h2><?php esc_html_e( 'About Genial Comments', 'gs-facebook-comments' ); ?></h2>
				<ul class="gsc-check-list">
					<li><?php esc_html_e( 'Disqus privacy-aware loading options', 'gs-facebook-comments' ); ?></li>
					<li><?php esc_html_e( 'Optional styling for theme-owned WordPress comments', 'gs-facebook-comments' ); ?></li>
				</ul>
			</section>

			<section class="gsc-sidebar-card">
				<h2><?php esc_html_e( 'Need Help?', 'gs-facebook-comments' ); ?></h2>
				<p><?php esc_html_e( 'Your active theme controls where WordPress comments appear.', 'gs-facebook-comments' ); ?></p>
				<a class="button" href="<?php echo esc_url( admin_url( 'options-discussion.php' ) ); ?>"><?php esc_html_e( 'Open Discussion Settings', 'gs-facebook-comments' ); ?></a>
				<p><a href="<?php echo esc_url( admin_url( 'edit-comments.php' ) ); ?>"><?php esc_html_e( 'Manage WordPress comments', 'gs-facebook-comments' ); ?></a></p>
			</section>

			<section class="gsc-sidebar-card">
				<h2><?php esc_html_e( 'Helpful Links', 'gs-facebook-comments' ); ?></h2>
				<p><a href="https://disqus.com/admin/create/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Create a Disqus site', 'gs-facebook-comments' ); ?></a></p>
				<p><a href="https://disqus.com/terms/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Disqus Terms', 'gs-facebook-comments' ); ?></a></p>
				<p><a href="https://disqus.com/privacy-policy/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Disqus Privacy Policy', 'gs-facebook-comments' ); ?></a></p>
				<p><a href="https://wordpress.org/plugins/gs-facebook-comments/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WordPress.org plugin page', 'gs-facebook-comments' ); ?></a></p>
				<p><a href="https://developers.facebook.com/blog/post/2025/11/10/platform-evolution-facebook-social-plugins-to-be-discontinued-february-2026/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Meta Developers (Facebook)', 'gs-facebook-comments' ); ?></a></p>
				<?php /* translators: Plugin version number. */ ?>
				<p class="gsc-sidebar-version"><?php echo esc_html( sprintf( __( 'Version %s', 'gs-facebook-comments' ), $this->version ) ); ?></p>
			</section>
		</aside>
	</div>
</div>
