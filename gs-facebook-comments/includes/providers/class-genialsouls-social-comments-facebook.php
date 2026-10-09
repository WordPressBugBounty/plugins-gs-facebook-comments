<?php
/**
 * Legacy Facebook/Meta provider.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Preserves the existing Facebook rendering and stored settings. */
class GenialSouls_Social_Comments_Facebook implements GenialSouls_Social_Comments_Provider_Interface {

	/** Return the legacy provider ID. */
	public function get_id() {
		return 'facebook';
	}

	/** Return the legacy provider label. */
	public function get_label() {
		return __( 'Facebook / Meta', 'gs-facebook-comments' );
	}

	/** Enqueue the legacy Facebook SDK.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function enqueue_assets( array $settings ) {
		$locale = isset( $settings['wpfc_option_data_locale'] ) && preg_match( '/^[a-z]{2}_[A-Z]{2}$/', $settings['wpfc_option_data_locale'] )
			? $settings['wpfc_option_data_locale']
			: 'en_US';
		$app_id = isset( $settings['wpfc_text_fb_appid'] ) && preg_match( '/^\d{5,32}$/', $settings['wpfc_text_fb_appid'] )
			? $settings['wpfc_text_fb_appid']
			: '';

		$parameters = array( 'xfbml=1', 'version=v12.0', 'autoLogAppEvents=1' );
		if ( '' !== $app_id ) {
			$parameters[] = 'appId=' . rawurlencode( $app_id );
		}

		$sdk_url = 'https://connect.facebook.net/' . rawurlencode( $locale ) . '/sdk.js#' . implode( '&', $parameters );
		wp_enqueue_script( 'genialsouls-social-comments-provider', esc_url_raw( $sdk_url ), array(), '12.0', true );
	}

	/** Render legacy Facebook metadata.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function render_head( array $settings ) {
		$app_id = isset( $settings['wpfc_text_fb_appid'] ) ? $settings['wpfc_text_fb_appid'] : '';
		if ( '' !== $app_id && preg_match( '/^\d{5,32}$/', $app_id ) ) {
			printf( '<meta property="fb:app_id" content="%s">' . "\n", esc_attr( $app_id ) );
		}
	}

	/** Render the legacy Facebook root element.
	 *
	 * @param array<string,mixed> $settings Stored settings.
	 */
	public function render_root( array $settings ) {
		echo '<div id="fb-root"></div>';
	}

	/**
	 * Render legacy Facebook comments.
	 *
	 * @param int                 $post_id Post ID.
	 * @param array<string,mixed> $options Stored settings.
	 * @return string Rendered markup.
	 */
	public function render( $post_id, array $options ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || ! get_post( $post_id ) ) {
			return '';
		}

		$count        = isset( $options['wpfc_text_comments_count'] ) ? absint( $options['wpfc_text_comments_count'] ) : 10;
		$count        = min( 9999, max( 1, $count ) );
		$width        = isset( $options['wpfc_text_data_width'] ) ? absint( $options['wpfc_text_data_width'] ) : 0;
		$width        = $width ? min( 1500, max( 320, $width ) ) . 'px' : '100%';
		$color_scheme = isset( $options['wpfc_option_data_colorscheme'] ) && in_array( $options['wpfc_option_data_colorscheme'], array( 'light', 'dark' ), true ) ? $options['wpfc_option_data_colorscheme'] : 'light';
		$order        = isset( $options['wpfc_option_data_orderby'] ) && in_array( $options['wpfc_option_data_orderby'], array( 'social', 'time', 'reverse_time' ), true ) ? $options['wpfc_option_data_orderby'] : 'social';
		$background   = isset( $options['wpfc_option_bg_color'] ) ? sanitize_hex_color( $options['wpfc_option_bg_color'] ) : '#ffffff';
		$background   = $background ? $background : '#ffffff';
		$permalink    = get_permalink( $post_id );

		if ( ! empty( $options['wpfc_checkbox_fb_cache'] ) ) {
			$this->request_service_refresh( $permalink );
		}

		$notice = '';
		if ( ! empty( $options['wpfc_checkbox_eu_notice'] ) ) {
			$notice = sprintf(
				'<div class="alert warning"><strong>%s</strong> %s</div>',
				esc_html__( 'External comments notice:', 'gs-facebook-comments' ),
				esc_html__( 'You may need to sign in to the external service to view or post comments.', 'gs-facebook-comments' )
			);
		}

		return sprintf(
			'<div class="fb-background-color" style="%1$s"><div class="fb-comments" style="%2$s" data-href="%3$s" data-numposts="%4$s" data-lazy="true" data-colorscheme="%5$s" data-order-by="%6$s" data-mobile="true"></div></div>%7$s',
			esc_attr( safecss_filter_attr( 'background-color:' . $background ) ),
			esc_attr( safecss_filter_attr( 'width:' . $width ) ),
			esc_url( $permalink ),
			esc_attr( $count ),
			esc_attr( $color_scheme ),
			esc_attr( $order ),
			$notice // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped static translations.
		);
	}

	/** Refresh a public URL in the legacy service.
	 *
	 * @param string $permalink Public post URL.
	 */
	private function request_service_refresh( $permalink ) {
		$url = add_query_arg(
			array(
				'q'      => esc_url_raw( $permalink ),
				'scrape' => 'true',
			),
			'https://developers.facebook.com/tools/debug/'
		);
		wp_safe_remote_get(
			$url,
			array(
				'timeout'     => 5,
				'redirection' => 2,
			)
		);
	}
}
