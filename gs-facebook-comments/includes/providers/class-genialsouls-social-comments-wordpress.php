<?php
/**
 * WordPress Native Comments provider.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Leaves native comment placement and rendering to WordPress and the theme. */
class GenialSouls_Social_Comments_WordPress implements GenialSouls_Social_Comments_Provider_Interface {

	/** Return the stable provider ID. */
	public function get_id() {
		return GenialSouls_Social_Comments_Provider_Resolver::PROVIDER_WORDPRESS;
	}

	/** Return the translated provider label. */
	public function get_label() {
		return __( 'WordPress Native Comments', 'gs-facebook-comments' );
	}

	/**
	 * Enqueue no provider assets.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function enqueue_assets( array $settings ) {}

	/**
	 * Render no provider head markup.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function render_head( array $settings ) {}

	/**
	 * Render no provider root element; the active theme owns native placement.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function render_root( array $settings ) {}

	/**
	 * Do not inject a native comments section into post content.
	 *
	 * WordPress themes own native comment placement. Returning an empty string
	 * prevents duplicate sections alongside classic comments templates and block
	 * theme Comments blocks while leaving core comments behavior untouched.
	 *
	 * @param int                 $post_id Post ID.
	 * @param array<string,mixed> $settings Stored settings.
	 * @return string Empty string; rendering remains theme-owned.
	 */
	public function render( $post_id, array $settings ) {
		return '';
	}
}
