<?php
/**
 * Public-facing functionality for GenialSouls Social Comments.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Public-facing hooks and renderers. */
class GenialSouls_Social_Comments_Public {

	/**
	 * Plugin handle.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Registered provider implementations.
	 *
	 * @var GenialSouls_Social_Comments_Provider_Registry
	 */
	private $providers;

	/**
	 * Whether a Disqus instance has already been rendered on this request.
	 *
	 * @var bool
	 */
	private $disqus_rendered = false;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_name Plugin handle.
	 * @param string $version     Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->providers   = new GenialSouls_Social_Comments_Provider_Registry();
		$this->providers->register( new GenialSouls_Social_Comments_Facebook() );
		$this->providers->register( new GenialSouls_Social_Comments_Disqus() );
	}

	/** Register frontend styles. */
	public function enqueue_styles() {
		wp_enqueue_style(
			'genialsouls-social-comments',
			plugin_dir_url( __FILE__ ) . 'css/wpfc-public.css',
			array(),
			$this->version
		);
	}

	/** Register the existing frontend script for backward compatibility. */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'genialsouls-social-comments',
			plugin_dir_url( __FILE__ ) . 'js/wpfc-public.js',
			array( 'jquery' ),
			$this->version,
			true
		);
	}

	/** Output the optional App ID metadata. */
	public function add_app_id_meta() {
		$provider = $this->get_provider();
		if ( $provider ) {
			$provider->render_head( $this->get_settings() );
		}
	}

	/** Enqueue the external service SDK used by the existing provider. */
	public function enqueue_service_sdk() {
		$provider = $this->get_provider();
		if ( $provider ) {
			$provider->enqueue_assets( $this->get_settings() );
		}
	}

	/** Output the provider root element. */
	public function render_service_root() {
		$provider = $this->get_provider();
		if ( $provider ) {
			$provider->render_root( $this->get_settings() );
		}
	}

	/**
	 * Append automatic comments to enabled public post types.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function append_comments( $content ) {
		$post_id = get_the_ID();
		$options = $this->get_settings();
		$enabled = metadata_exists( 'post', $post_id, '_fb_comments' ) ? (bool) get_post_meta( $post_id, '_fb_comments', true ) : true;
		$type    = get_post_type( $post_id );

		if ( ! $enabled || ! isset( $options['wpfc_checkbox_post_types'][ $type ] ) ) {
			return $content;
		}

		if ( has_shortcode( $content, 'gs-fb-comments' ) ) {
			return $content;
		}

		return $content . $this->render_provider( $post_id, $options );
	}

	/**
	 * Preserve the existing [gs-fb-comments] shortcode.
	 *
	 * @param array|string $atts Shortcode attributes (currently unused).
	 * @return string
	 */
	public function render_shortcode( $atts = array() ) {
		shortcode_atts( array(), is_array( $atts ) ? $atts : array(), 'gs-fb-comments' );
		$options = $this->get_settings();
		return $this->render_provider( get_the_ID(), $options );
	}

	/**
	 * Render the selected provider.
	 *
	 * @param int                 $post_id Post ID.
	 * @param array<string,mixed> $options Stored settings.
	 * @return string Rendered markup.
	 */
	private function render_provider( $post_id, $options ) {
		$provider = $this->get_provider();
		if ( ! $provider ) {
			return '<div class="genialsouls-social-comments-provider-error"><p>' . esc_html__( 'The selected comment provider is invalid. Please review the Social Comments settings.', 'gs-facebook-comments' ) . '</p></div>';
		}

		if ( 'disqus' === $provider->get_id() ) {
			if ( $this->disqus_rendered ) {
				return '';
			}
			$this->disqus_rendered = true;
		}
		return $provider->render( $post_id, $options );
	}

	/** Return the selected provider according to the centralized policy. */
	private function get_provider() {
		$provider_id = GenialSouls_Social_Comments_Provider_Resolver::resolve( $this->get_settings() );
		return $provider_id ? $this->providers->get( $provider_id ) : null;
	}

	/**
	 * Get normalized stored settings.
	 *
	 * @return array<string,mixed>
	 */
	private function get_settings() {
		$options = get_option( 'wpfc_settings', array() );
		return is_array( $options ) ? $options : array();
	}
}
