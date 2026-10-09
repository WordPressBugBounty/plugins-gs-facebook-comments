<?php
/**
 * Disqus Universal Embed provider.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Renders a privacy-aware local Disqus loader without API credentials. */
class GenialSouls_Social_Comments_Disqus implements GenialSouls_Social_Comments_Provider_Interface {

	/** Return the Disqus provider ID. */
	public function get_id() {
		return 'disqus';
	}

	/** Return the Disqus provider label. */
	public function get_label() {
		return __( 'Disqus', 'gs-facebook-comments' );
	}

	/** Keep Disqus external loading deferred to the local trigger.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function enqueue_assets( array $settings ) {
		// Disqus is injected by the local script only after the selected trigger.
	}

	/** Disqus does not require head markup.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function render_head( array $settings ) {}

	/** Disqus does not require a provider root element.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function render_root( array $settings ) {}

	/**
	 * Render a local lazy-loading Disqus placeholder.
	 *
	 * @param int                 $post_id Post ID.
	 * @param array<string,mixed> $settings Stored settings.
	 * @return string Rendered markup.
	 */
	public function render( $post_id, array $settings ) {
		$post_id     = absint( $post_id );
		$post        = get_post( $post_id );
		$shortname   = isset( $settings['genialsouls_disqus_shortname'] ) ? self::sanitize_shortname( $settings['genialsouls_disqus_shortname'] ) : '';
		$mode        = isset( $settings['genialsouls_disqus_load_mode'] ) && in_array( $settings['genialsouls_disqus_load_mode'], array( 'click', 'viewport', 'immediate' ), true ) ? $settings['genialsouls_disqus_load_mode'] : 'click';
		$button_text = isset( $settings['genialsouls_disqus_button_text'] ) ? sanitize_text_field( $settings['genialsouls_disqus_button_text'] ) : __( 'Load comments', 'gs-facebook-comments' );
		$button_text = '' !== $button_text ? $button_text : __( 'Load comments', 'gs-facebook-comments' );

		if ( ! $post_id || ! $post || '' === $shortname ) {
			return '<div class="genialsouls-social-comments-disqus disqus-not-configured"><p>' . esc_html__( 'Disqus comments are not configured yet.', 'gs-facebook-comments' ) . '</p></div>';
		}

		$permalink = get_permalink( $post_id );
		if ( ! $permalink ) {
			return '<div class="genialsouls-social-comments-disqus disqus-not-configured"><p>' . esc_html__( 'Comments cannot be loaded for this content.', 'gs-facebook-comments' ) . '</p></div>';
		}

		$config = array(
			'shortname'  => $shortname,
			'identifier' => (string) $post_id,
			'url'        => esc_url_raw( $permalink ),
			'title'      => wp_strip_all_tags( get_the_title( $post_id ) ),
			'mode'       => $mode,
		);
		$json   = wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

		return sprintf(
			'<div class="genialsouls-social-comments-disqus" data-gsc-disqus-config="%1$s"><button type="button" class="gsc-disqus-load" aria-controls="disqus_thread">%2$s</button><noscript><p>%3$s</p></noscript><div class="gsc-disqus-target" aria-live="polite"></div></div>',
			esc_attr( $json ),
			esc_html( $button_text ),
			esc_html__( 'Enable JavaScript to load Disqus comments.', 'gs-facebook-comments' )
		);
	}

	/**
	 * Sanitize and validate a Disqus shortname.
	 *
	 * @param mixed $value Candidate shortname.
	 * @return string
	 */
	public static function sanitize_shortname( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}
		$value = strtolower( trim( (string) $value ) );
		return preg_match( '/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/D', $value ) ? $value : '';
	}
}
