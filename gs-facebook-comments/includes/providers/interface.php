<?php
/**
 * Provider contract for the comments integrations.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Minimal contract shared by legacy and new comment providers. */
interface GenialSouls_Social_Comments_Provider_Interface {
	/** Return the stable provider ID. */
	public function get_id();

	/** Return the translated provider label. */
	public function get_label();

	/** Enqueue provider assets when needed.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function enqueue_assets( array $settings );

	/** Render provider-specific head markup.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function render_head( array $settings );

	/** Render provider-specific root markup.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function render_root( array $settings );

	/**
	 * Render the provider for a WordPress object.
	 *
	 * @param int                 $post_id Post ID.
	 * @param array<string,mixed> $settings Stored settings.
	 * @return string Rendered markup.
	 */
	public function render( $post_id, array $settings );
}
