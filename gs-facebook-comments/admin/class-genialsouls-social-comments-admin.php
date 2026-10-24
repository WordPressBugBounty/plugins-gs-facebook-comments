<?php
/**
 * Admin functionality for GenialSouls Social Comments.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the settings page, post meta box, and authenticated AJAX handler.
 */
class GenialSouls_Social_Comments_Admin {

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
	 * Supported Facebook locales exposed by the existing interface.
	 *
	 * @var array<string,string>
	 */
	private $locales = array(
		'en_US' => 'English',
		'es_ES' => 'Español',
		'fr_FR' => 'Français',
		'de_DE' => 'Deutsch',
		'it_IT' => 'Italiano',
		'nl_NL' => 'Nederlands',
	);

	/**
	 * Constructor.
	 *
	 * @param string $plugin_name Plugin handle.
	 * @param string $version     Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/** Register admin styles on plugin and editor screens. */
	public function enqueue_styles() {
		$style_file    = plugin_dir_path( __FILE__ ) . 'css/wpfc-admin.css';
		$style_version = file_exists( $style_file ) ? (string) filemtime( $style_file ) : $this->version;

		wp_enqueue_style(
			'genialsouls-social-comments-admin',
			plugin_dir_url( __FILE__ ) . 'css/wpfc-admin.css',
			array(),
			$style_version
		);
	}

	/** Register the meta-box script. */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'genialsouls-social-comments-admin',
			plugin_dir_url( __FILE__ ) . 'js/wpfc-admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);
		wp_localize_script(
			'genialsouls-social-comments-admin',
			'genialSoulsSocialCommentsAdmin',
			array(
				'action'       => 'genialsouls_social_comments_save_post_setting',
				'errorMessage' => esc_html__( 'The setting could not be saved.', 'gs-facebook-comments' ),
			)
		);
	}

	/** Register the settings page. */
	public function register_admin_menu() {
		add_menu_page(
			esc_html__( 'Genial Comments', 'gs-facebook-comments' ),
			esc_html__( 'Genial Comments', 'gs-facebook-comments' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_setup_page' ),
			'dashicons-admin-comments',
			54
		);
	}

	/** Render the settings page. */
	public function display_plugin_setup_page() {
		include __DIR__ . '/partials/wpfc-admin-display.php';
	}

	/** Render the Meta discontinuation notice without changing stored settings. */
	public function render_facebook_notice() {
		$announcement_url = 'https://developers.facebook.com/blog/post/2025/11/10/platform-evolution-facebook-social-plugins-to-be-discontinued-february-2026/';
		$options          = $this->get_settings();
		$selected         = GenialSouls_Social_Comments_Provider_Resolver::resolve( $options );
		if ( 'facebook' !== $selected ) {
			return;
		}
		?>
		<div class="gsc-facebook-notice notice notice-warning" role="alert">
			<span class="dashicons dashicons-warning" aria-hidden="true"></span>
			<div>
				<h2><?php esc_html_e( 'Legacy Facebook provider selected', 'gs-facebook-comments' ); ?></h2>
				<p><?php esc_html_e( 'Facebook Comments has been discontinued. Existing settings are preserved, but Facebook comments are no longer displayed. Select Disqus for active external commenting.', 'gs-facebook-comments' ); ?></p>
				<p><a href="<?php echo esc_url( $announcement_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn more on Meta Developers', 'gs-facebook-comments' ); ?> <span aria-hidden="true">↗</span></a></p>
			</div>
		</div>
		<?php
	}

	/** Register settings, sections, and fields. */
	public function register_settings() {
		register_setting(
			'wpfc_options',
			'wpfc_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);

		add_settings_section( 'genialsouls_provider_section', esc_html__( 'Comments provider', 'gs-facebook-comments' ), array( $this, 'render_provider_intro' ), 'wpfc_options' );
		add_settings_section( 'genialsouls_disqus_section', esc_html__( 'Disqus', 'gs-facebook-comments' ), array( $this, 'render_disqus_intro' ), 'wpfc_options' );
		add_settings_section( 'genialsouls_native_appearance_section', '', array( $this, 'render_native_appearance' ), 'wpfc_options' );
		add_settings_section( 'wpfc_fb_appid_section', esc_html__( 'Facebook Comments settings', 'gs-facebook-comments' ), '__return_false', 'wpfc_options' );
		add_settings_section( 'wpfc_options_section', esc_html__( 'Comments box', 'gs-facebook-comments' ), '__return_false', 'wpfc_options' );
		add_settings_section( 'wpfc_posts_options_section', esc_html__( 'Post types', 'gs-facebook-comments' ), array( $this, 'render_post_types_intro' ), 'wpfc_options' );

		$this->add_field( 'genialsouls_provider', __( 'Provider', 'gs-facebook-comments' ), 'render_provider', 'genialsouls_provider_section' );
		$this->add_field( 'genialsouls_disqus_shortname', __( 'Disqus shortname', 'gs-facebook-comments' ), 'render_disqus_shortname', 'genialsouls_disqus_section' );
		$this->add_field( 'genialsouls_disqus_load_mode', __( 'Disqus loading', 'gs-facebook-comments' ), 'render_disqus_load_mode', 'genialsouls_disqus_section' );
		$this->add_field( 'genialsouls_disqus_button_text', __( 'Load button text', 'gs-facebook-comments' ), 'render_disqus_button_text', 'genialsouls_disqus_section' );
		$this->add_field( 'wpfc_text_fb_appid', __( 'Facebook App ID', 'gs-facebook-comments' ), 'render_app_id', 'wpfc_fb_appid_section' );
		$this->add_field( 'wpfc_checkbox_eu_notice', __( 'Show privacy notice?', 'gs-facebook-comments' ), 'render_eu_notice', 'wpfc_fb_appid_section' );
		$this->add_field( 'wpfc_checkbox_fb_cache', __( 'Refresh Facebook cache', 'gs-facebook-comments' ), 'render_cache_option', 'wpfc_fb_appid_section' );
		$this->add_field( 'wpfc_text_comments_count', __( 'Number of comments', 'gs-facebook-comments' ), 'render_comments_count', 'wpfc_options_section' );
		$this->add_field( 'wpfc_text_data_width', __( 'Comments box width', 'gs-facebook-comments' ), 'render_width', 'wpfc_options_section' );
		$this->add_field( 'wpfc_option_data_colorscheme', __( 'Color scheme', 'gs-facebook-comments' ), 'render_color_scheme', 'wpfc_options_section' );
		$this->add_field( 'wpfc_option_bg_color', __( 'Background color', 'gs-facebook-comments' ), 'render_background_color', 'wpfc_options_section' );
		$this->add_field( 'wpfc_option_data_orderby', __( 'Comment order', 'gs-facebook-comments' ), 'render_order', 'wpfc_options_section' );
		$this->add_field( 'wpfc_option_data_locale', __( 'Language', 'gs-facebook-comments' ), 'render_locale', 'wpfc_options_section' );
		$this->add_field( 'wpfc_checkbox_post_types', __( 'Enabled post types', 'gs-facebook-comments' ), 'render_post_types', 'wpfc_posts_options_section' );
	}

	/**
	 * Register one settings field.
	 *
	 * @param string $id      Field ID.
	 * @param string $label   Field label.
	 * @param string $method  Renderer method.
	 * @param string $section Section ID.
	 */
	private function add_field( $id, $label, $method, $section ) {
		add_settings_field( $id, $label, array( $this, $method ), 'wpfc_options', $section );
	}

	/**
	 * Sanitize the structured settings array.
	 *
	 * @param mixed $input Submitted settings.
	 * @return array<string,mixed>
	 */
	public function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			add_settings_error( 'wpfc_settings', 'invalid_settings', __( 'Settings must be submitted as an array.', 'gs-facebook-comments' ) );
			return array();
		}

		$existing = get_option( 'wpfc_settings', array() );
		$clean    = is_array( $existing ) ? $existing : array();

		if ( array_key_exists( 'genialsouls_provider', $input ) ) {
			$raw_provider = wp_unslash( $input['genialsouls_provider'] );
			$provider     = is_scalar( $raw_provider ) ? trim( (string) $raw_provider ) : '';
			if ( in_array( sanitize_key( $provider ), GenialSouls_Social_Comments_Provider_Resolver::PROVIDERS, true ) ) {
				$clean['genialsouls_provider'] = sanitize_key( $provider );
			} else {
				$clean['genialsouls_provider'] = $provider;
				add_settings_error( 'wpfc_settings', 'invalid_provider', __( 'The selected provider is invalid. Choose Disqus, WordPress Native Comments, or Facebook / Meta.', 'gs-facebook-comments' ), 'error' );
			}
		} elseif ( ! array_key_exists( 'genialsouls_provider', $clean ) ) {
			$clean['genialsouls_provider'] = GenialSouls_Social_Comments_Provider_Resolver::get_default_provider();
		}

		$shortname = isset( $input['genialsouls_disqus_shortname'] ) ? GenialSouls_Social_Comments_Disqus::sanitize_shortname( $input['genialsouls_disqus_shortname'] ) : ( isset( $clean['genialsouls_disqus_shortname'] ) ? GenialSouls_Social_Comments_Disqus::sanitize_shortname( $clean['genialsouls_disqus_shortname'] ) : '' );
		if ( isset( $input['genialsouls_disqus_shortname'] ) && '' === $shortname && '' !== trim( (string) $input['genialsouls_disqus_shortname'] ) ) {
			add_settings_error( 'wpfc_settings', 'invalid_disqus_shortname', __( 'Enter a valid Disqus shortname using letters, numbers, and hyphens only.', 'gs-facebook-comments' ) );
		}
		$clean['genialsouls_disqus_shortname'] = $shortname;

		$mode                                  = isset( $input['genialsouls_disqus_load_mode'] ) ? sanitize_key( wp_unslash( $input['genialsouls_disqus_load_mode'] ) ) : ( isset( $clean['genialsouls_disqus_load_mode'] ) ? $clean['genialsouls_disqus_load_mode'] : 'click' );
		$clean['genialsouls_disqus_load_mode'] = in_array( $mode, array( 'click', 'viewport', 'immediate' ), true ) ? $mode : 'click';

		$button                                  = isset( $input['genialsouls_disqus_button_text'] ) ? sanitize_text_field( wp_unslash( $input['genialsouls_disqus_button_text'] ) ) : ( isset( $clean['genialsouls_disqus_button_text'] ) ? sanitize_text_field( $clean['genialsouls_disqus_button_text'] ) : __( 'Load comments', 'gs-facebook-comments' ) );
		$clean['genialsouls_disqus_button_text'] = '' !== $button ? $button : __( 'Load comments', 'gs-facebook-comments' );

		$preset_key       = GenialSouls_Social_Comments_Native_Appearance::PRESET_OPTION;
		$preset_submitted = array_key_exists( $preset_key, $input );
		$preset_value     = $preset_submitted ? wp_unslash( $input[ $preset_key ] ) : ( isset( $clean[ $preset_key ] ) ? $clean[ $preset_key ] : 'default' );
		$preset           = GenialSouls_Social_Comments_Native_Appearance::normalize_preset( $preset_value );
		if ( $preset_submitted && ( ! is_scalar( $preset_value ) || sanitize_key( (string) $preset_value ) !== $preset ) ) {
			add_settings_error( 'wpfc_settings', 'invalid_native_appearance', __( 'Choose Default, Modern, or Minimal for Native Comments Appearance.', 'gs-facebook-comments' ) );
		}
		$clean[ $preset_key ] = $preset;

		$accent_key       = GenialSouls_Social_Comments_Native_Appearance::ACCENT_OPTION;
		$accent_submitted = array_key_exists( $accent_key, $input );
		$accent_value     = $accent_submitted ? wp_unslash( $input[ $accent_key ] ) : ( isset( $clean[ $accent_key ] ) ? $clean[ $accent_key ] : 'blue' );
		$accent           = GenialSouls_Social_Comments_Native_Appearance::normalize_accent( $accent_value );
		if ( $accent_submitted && ( ! is_scalar( $accent_value ) || sanitize_key( (string) $accent_value ) !== $accent ) ) {
			add_settings_error( 'wpfc_settings', 'invalid_native_accent', __( 'Choose one of the available Native Comments accent colors.', 'gs-facebook-comments' ) );
		}
		$clean[ $accent_key ] = $accent;

		$app_id = isset( $input['wpfc_text_fb_appid'] ) ? sanitize_text_field( wp_unslash( $input['wpfc_text_fb_appid'] ) ) : '';
		if ( '' !== $app_id && ! preg_match( '/^\d{5,32}$/', $app_id ) ) {
			add_settings_error( 'wpfc_settings', 'invalid_app_id', __( 'Facebook App ID must contain only digits.', 'gs-facebook-comments' ) );
			$app_id = '';
		}
		$clean['wpfc_text_fb_appid'] = $app_id;

		$clean['wpfc_checkbox_eu_notice'] = empty( $input['wpfc_checkbox_eu_notice'] ) ? 0 : 1;
		$clean['wpfc_checkbox_fb_cache']  = empty( $input['wpfc_checkbox_fb_cache'] ) ? 0 : 1;

		$count                             = isset( $input['wpfc_text_comments_count'] ) ? absint( $input['wpfc_text_comments_count'] ) : 10;
		$clean['wpfc_text_comments_count'] = min( 9999, max( 1, $count ) );

		$width                         = isset( $input['wpfc_text_data_width'] ) ? trim( sanitize_text_field( wp_unslash( $input['wpfc_text_data_width'] ) ) ) : '';
		$clean['wpfc_text_data_width'] = '' === $width ? '' : min( 1500, max( 320, absint( $width ) ) );

		$color_scheme                          = isset( $input['wpfc_option_data_colorscheme'] ) ? sanitize_key( wp_unslash( $input['wpfc_option_data_colorscheme'] ) ) : 'light';
		$clean['wpfc_option_data_colorscheme'] = in_array( $color_scheme, array( 'light', 'dark' ), true ) ? $color_scheme : 'light';

		$background                    = isset( $input['wpfc_option_bg_color'] ) ? sanitize_hex_color( wp_unslash( $input['wpfc_option_bg_color'] ) ) : '#ffffff';
		$clean['wpfc_option_bg_color'] = $background ? $background : '#ffffff';

		$order                             = isset( $input['wpfc_option_data_orderby'] ) ? sanitize_key( wp_unslash( $input['wpfc_option_data_orderby'] ) ) : 'social';
		$clean['wpfc_option_data_orderby'] = in_array( $order, array( 'social', 'time', 'reverse_time' ), true ) ? $order : 'social';

		$locale                           = isset( $input['wpfc_option_data_locale'] ) ? sanitize_text_field( wp_unslash( $input['wpfc_option_data_locale'] ) ) : 'en_US';
		$clean['wpfc_option_data_locale'] = isset( $this->locales[ $locale ] ) ? $locale : 'en_US';

		$clean['wpfc_checkbox_post_types'] = array();
		if ( isset( $input['wpfc_checkbox_post_types'] ) && is_array( $input['wpfc_checkbox_post_types'] ) ) {
			foreach ( array_keys( $input['wpfc_checkbox_post_types'] ) as $post_type ) {
				$post_type = sanitize_key( wp_unslash( $post_type ) );
				if ( post_type_exists( $post_type ) ) {
					$clean['wpfc_checkbox_post_types'][ $post_type ] = 1;
				}
			}
		}

		return $clean;
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

	/** Render App ID. */
	public function render_app_id() {
		$options = $this->get_settings();
		$value   = isset( $options['wpfc_text_fb_appid'] ) ? $options['wpfc_text_fb_appid'] : '';
		?>
		<input type="text" inputmode="numeric" pattern="[0-9]*" size="32" name="wpfc_settings[wpfc_text_fb_appid]" value="<?php echo esc_attr( $value ); ?>">
		<p class="description"><?php esc_html_e( 'Optional identifier supplied by the external comments service.', 'gs-facebook-comments' ); ?></p>
		<?php
	}

	/** Explain provider selection and preserve Facebook as the legacy default. */
	public function render_provider_intro() {
		?>
		<p><?php esc_html_e( 'Choose the provider used for new plugin comment displays. Existing installations without a provider selection continue using Facebook / Meta. WordPress Native Comments are displayed by your active theme; selecting a provider never deletes native WordPress comments.', 'gs-facebook-comments' ); ?></p>
		<?php
	}

	/** Render actionable provider configuration or validity guidance. */
	public function render_provider_warning() {
		$options = $this->get_settings();
		if ( GenialSouls_Social_Comments_Provider_Resolver::has_invalid_provider( $options ) ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html__( 'The saved comment provider is invalid. Select Disqus, WordPress Native Comments, or Facebook / Meta and save the settings. The invalid value will not be silently replaced.', 'gs-facebook-comments' ) );
			return;
		}

		if ( 'disqus' === GenialSouls_Social_Comments_Provider_Resolver::resolve( $options ) && '' === GenialSouls_Social_Comments_Disqus::sanitize_shortname( isset( $options['genialsouls_disqus_shortname'] ) ? $options['genialsouls_disqus_shortname'] : '' ) ) {
			printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_html__( 'Disqus is selected but not configured. Enter the Disqus shortname below before expecting comments to load.', 'gs-facebook-comments' ) );
		}
	}

	/** Render provider selection. */
	public function render_provider() {
		$options = $this->get_settings();
		$value   = GenialSouls_Social_Comments_Provider_Resolver::resolve( $options );
		if ( array_key_exists( 'genialsouls_provider', $options ) && is_scalar( $options['genialsouls_provider'] ) && GenialSouls_Social_Comments_Provider_Resolver::has_invalid_provider( $options ) ) {
			$value = sanitize_key( (string) $options['genialsouls_provider'] );
		}
		$providers = array(
			'disqus'    => array(
				'label'       => __( 'Disqus Comments', 'gs-facebook-comments' ),
				'badge'       => __( 'Recommended', 'gs-facebook-comments' ),
				'description' => __( 'Add Disqus-powered discussions with click-to-load, viewport, or immediate loading.', 'gs-facebook-comments' ),
				'url'         => 'https://disqus.com/',
				'link'        => __( 'Visit Disqus', 'gs-facebook-comments' ),
			),
			'wordpress' => array(
				'label'       => __( 'WordPress Native Comments', 'gs-facebook-comments' ),
				'badge'       => __( 'Built in', 'gs-facebook-comments' ),
				'description' => __( 'Use your theme\'s existing WordPress comments. No external account required.', 'gs-facebook-comments' ),
				'url'         => admin_url( 'options-discussion.php' ),
				'link'        => __( 'Discussion Settings', 'gs-facebook-comments' ),
			),
			'facebook'  => array(
				'label'       => __( 'Facebook Comments', 'gs-facebook-comments' ),
				'badge'       => __( 'Discontinued', 'gs-facebook-comments' ),
				'description' => __( 'Legacy compatibility only. Meta discontinued Facebook Comments in February 2026.', 'gs-facebook-comments' ),
				'url'         => 'https://developers.facebook.com/blog/post/2025/11/10/platform-evolution-facebook-social-plugins-to-be-discontinued-february-2026/',
				'link'        => __( 'Learn more on Meta Developers', 'gs-facebook-comments' ),
			),
		);
		?>
		<div class="gsc-provider-cards" role="radiogroup" aria-label="<?php esc_attr_e( 'Comment provider', 'gs-facebook-comments' ); ?>">
			<?php foreach ( $providers as $provider_id => $provider ) : ?>
				<div class="gsc-provider-card <?php echo esc_attr( 'gsc-provider-card-' . $provider_id ); ?> <?php echo $value === $provider_id ? 'is-selected' : ''; ?>">
					<label class="gsc-provider-card-label">
						<input type="radio" name="wpfc_settings[genialsouls_provider]" value="<?php echo esc_attr( $provider_id ); ?>" <?php checked( $value, $provider_id ); ?>>
						<span class="gsc-provider-card-body">
						<span class="gsc-provider-card-heading">
							<span class="gsc-provider-icon dashicons <?php echo 'facebook' === $provider_id ? 'dashicons-facebook' : ( GenialSouls_Social_Comments_Provider_Resolver::PROVIDER_WORDPRESS === $provider_id ? 'dashicons-wordpress' : 'dashicons-format-chat' ); ?>" aria-hidden="true"></span>
							<span class="gsc-provider-card-title"><?php echo esc_html( $provider['label'] ); ?></span>
							<span class="gsc-provider-badge <?php echo 'facebook' === $provider_id ? 'is-discontinued' : ( 'disqus' === $provider_id ? 'is-recommended' : 'is-native' ); ?>"><?php echo esc_html( $provider['badge'] ); ?></span>
						</span>
						<span class="gsc-provider-card-description"><?php echo esc_html( $provider['description'] ); ?></span>
						</span>
					</label>
					<?php if ( ! empty( $provider['url'] ) && ! empty( $provider['link'] ) ) : ?>
						<a class="gsc-provider-card-link" href="<?php echo esc_url( $provider['url'] ); ?>" target="<?php echo GenialSouls_Social_Comments_Provider_Resolver::PROVIDER_WORDPRESS === $provider_id ? '_self' : '_blank'; ?>" rel="noopener noreferrer"><?php echo esc_html( $provider['link'] ); ?> <span aria-hidden="true"><?php echo GenialSouls_Social_Comments_Provider_Resolver::PROVIDER_WORDPRESS === $provider_id ? '→' : '↗'; ?></span></a>
					<?php endif; ?>
					<?php if ( GenialSouls_Social_Comments_Provider_Resolver::PROVIDER_WORDPRESS === $provider_id ) : ?>
						<a class="gsc-provider-card-link" href="<?php echo esc_url( admin_url( 'edit-comments.php' ) ); ?>"><?php esc_html_e( 'Manage Comments', 'gs-facebook-comments' ); ?> <span aria-hidden="true">→</span></a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<p class="description gsc-provider-preservation"><span class="dashicons dashicons-lock" aria-hidden="true"></span> <?php esc_html_e( 'Changing provider selection does not delete existing WordPress comments, Facebook settings, or saved post settings.', 'gs-facebook-comments' ); ?></p>
		<?php
	}

	/** Explain the Disqus integration. */
	public function render_disqus_intro() {
		?>
		<p><?php esc_html_e( 'Disqus is an external commenting service. When comments are loaded, information about this page and the visitor may be transmitted to Disqus. Disqus Basic may display advertising; advertising, tracking, account features, and ad-free options are controlled by Disqus and your Disqus account or plan.', 'gs-facebook-comments' ); ?></p>
		<p><a href="https://disqus.com/terms/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Disqus Terms', 'gs-facebook-comments' ); ?></a> · <a href="https://disqus.com/privacy-policy/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Disqus Privacy Policy', 'gs-facebook-comments' ); ?></a></p>
		<?php
	}

	/** Render the contextual Native Comments appearance panel. */
	public function render_native_appearance() {
		$options      = $this->get_settings();
		$preset_value = GenialSouls_Social_Comments_Native_Appearance::normalize_preset( isset( $options[ GenialSouls_Social_Comments_Native_Appearance::PRESET_OPTION ] ) ? $options[ GenialSouls_Social_Comments_Native_Appearance::PRESET_OPTION ] : 'default' );
		$accent_value = GenialSouls_Social_Comments_Native_Appearance::normalize_accent( isset( $options[ GenialSouls_Social_Comments_Native_Appearance::ACCENT_OPTION ] ) ? $options[ GenialSouls_Social_Comments_Native_Appearance::ACCENT_OPTION ] : 'blue' );
		?>
		<section class="gsc-native-appearance" data-provider-context="wordpress" aria-labelledby="gsc-native-appearance-title">
			<h2 id="gsc-native-appearance-title"><?php esc_html_e( 'Native Comments Appearance', 'gs-facebook-comments' ); ?></h2>
			<p class="gsc-native-appearance-intro"><?php esc_html_e( 'Customize the appearance of your theme\'s existing WordPress comments. Styling is optional and may vary by theme.', 'gs-facebook-comments' ); ?></p>
			<fieldset>
				<legend><?php esc_html_e( 'Appearance preset', 'gs-facebook-comments' ); ?></legend>
				<?php
				$this->render_radio_group(
					GenialSouls_Social_Comments_Native_Appearance::PRESET_OPTION,
					$preset_value,
					array(
						'default' => __( 'Default — use existing theme styling', 'gs-facebook-comments' ),
						'modern'  => __( 'Modern — subtle cards, rounded corners, comfortable spacing', 'gs-facebook-comments' ),
						'minimal' => __( 'Minimal — simple dividers, clean spacing and typography', 'gs-facebook-comments' ),
					)
				);
				?>
			</fieldset>
			<fieldset>
				<legend><?php esc_html_e( 'Accent color', 'gs-facebook-comments' ); ?></legend>
				<?php
				$this->render_accent_radio_group(
					$accent_value,
					array(
						'blue'    => __( 'Blue', 'gs-facebook-comments' ),
						'green'   => __( 'Green', 'gs-facebook-comments' ),
						'purple'  => __( 'Purple', 'gs-facebook-comments' ),
						'neutral' => __( 'Neutral', 'gs-facebook-comments' ),
					)
				);
				?>
			</fieldset>
		</section>
		<?php
	}

	/**
	 * Render the native accent radios with decorative palette swatches.
	 *
	 * @param string               $selected Selected accent.
	 * @param array<string,string> $choices  Available accent labels.
	 */
	private function render_accent_radio_group( $selected, $choices ) {
		$key = GenialSouls_Social_Comments_Native_Appearance::ACCENT_OPTION;

		foreach ( $choices as $value => $label ) {
			$color = GenialSouls_Social_Comments_Native_Appearance::get_accent_color( $value );
			?>
			<label class="gsc-accent-color-option">
				<input type="radio" name="wpfc_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $selected, $value ); ?>>
				<span class="gsc-accent-color-swatch" style="background-color: <?php echo esc_attr( $color ); ?>;" aria-hidden="true"></span>
				<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
			</label>
			<?php
		}
	}

	/** Render the validated Disqus shortname. */
	public function render_disqus_shortname() {
		$options = $this->get_settings();
		$value   = isset( $options['genialsouls_disqus_shortname'] ) ? $options['genialsouls_disqus_shortname'] : '';
		?>
		<input type="text" class="regular-text" name="wpfc_settings[genialsouls_disqus_shortname]" value="<?php echo esc_attr( $value ); ?>" pattern="[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?" maxlength="63" autocomplete="off">
		<p class="description"><?php esc_html_e( 'Use the shortname from your Disqus site settings, not a URL. Letters, numbers, and hyphens only.', 'gs-facebook-comments' ); ?></p>
		<?php
	}

	/** Render Disqus loading mode. */
	public function render_disqus_load_mode() {
		$options = $this->get_settings();
		$value   = isset( $options['genialsouls_disqus_load_mode'] ) ? $options['genialsouls_disqus_load_mode'] : 'click';
		$this->render_radio_group(
			'genialsouls_disqus_load_mode',
			$value,
			array(
				'click'     => __( 'Click to load (recommended)', 'gs-facebook-comments' ),
				'viewport'  => __( 'Load near the viewport', 'gs-facebook-comments' ),
				'immediate' => __( 'Load immediately', 'gs-facebook-comments' ),
			)
		);
	}

	/** Render local Disqus activation text. */
	public function render_disqus_button_text() {
		$options = $this->get_settings();
		$value   = isset( $options['genialsouls_disqus_button_text'] ) ? $options['genialsouls_disqus_button_text'] : __( 'Load comments', 'gs-facebook-comments' );
		?>
		<input type="text" class="regular-text" name="wpfc_settings[genialsouls_disqus_button_text]" value="<?php echo esc_attr( $value ); ?>" maxlength="120">
		<?php
	}

	/** Render privacy notice option. */
	public function render_eu_notice() {
		$this->render_checkbox( 'wpfc_checkbox_eu_notice', __( 'Show a privacy notice above the external comments area.', 'gs-facebook-comments' ) );
	}

	/** Render crawler option. */
	public function render_cache_option() {
		$this->render_checkbox( 'wpfc_checkbox_fb_cache', __( 'Ask the external service to refresh the public post URL when comments render.', 'gs-facebook-comments' ) );
	}

	/**
	 * Render a checkbox option.
	 *
	 * @param string $key         Option key.
	 * @param string $description Field description.
	 */
	private function render_checkbox( $key, $description ) {
		$options = $this->get_settings();
		?>
		<label>
			<input type="checkbox" name="wpfc_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $options[ $key ] ) ); ?>>
			<?php echo esc_html( $description ); ?>
		</label>
		<?php
	}

	/** Render count. */
	public function render_comments_count() {
		$options = $this->get_settings();
		$value   = isset( $options['wpfc_text_comments_count'] ) ? absint( $options['wpfc_text_comments_count'] ) : 10;
		?>
		<input type="number" min="1" max="9999" name="wpfc_settings[wpfc_text_comments_count]" value="<?php echo esc_attr( $value ); ?>">
		<?php
	}

	/** Render width. */
	public function render_width() {
		$options = $this->get_settings();
		$value   = isset( $options['wpfc_text_data_width'] ) ? $options['wpfc_text_data_width'] : '';
		?>
		<input type="number" min="320" max="1500" name="wpfc_settings[wpfc_text_data_width]" value="<?php echo esc_attr( $value ); ?>">
		<span class="description"><?php esc_html_e( 'Pixels. Leave blank for full width.', 'gs-facebook-comments' ); ?></span>
		<?php
	}

	/** Render color scheme. */
	public function render_color_scheme() {
		$options = $this->get_settings();
		$value   = isset( $options['wpfc_option_data_colorscheme'] ) ? $options['wpfc_option_data_colorscheme'] : 'light';
		$this->render_radio_group(
			'wpfc_option_data_colorscheme',
			$value,
			array(
				'light' => __( 'Light', 'gs-facebook-comments' ),
				'dark'  => __( 'Dark', 'gs-facebook-comments' ),
			)
		);
	}

	/** Render background color. */
	public function render_background_color() {
		$options = $this->get_settings();
		$value   = isset( $options['wpfc_option_bg_color'] ) ? sanitize_hex_color( $options['wpfc_option_bg_color'] ) : '#ffffff';
		?>
		<input type="color" name="wpfc_settings[wpfc_option_bg_color]" value="<?php echo esc_attr( $value ? $value : '#ffffff' ); ?>">
		<?php
	}

	/** Render order. */
	public function render_order() {
		$options = $this->get_settings();
		$value   = isset( $options['wpfc_option_data_orderby'] ) ? $options['wpfc_option_data_orderby'] : 'social';
		$this->render_radio_group(
			'wpfc_option_data_orderby',
			$value,
			array(
				'social'       => __( 'Top', 'gs-facebook-comments' ),
				'time'         => __( 'Oldest first', 'gs-facebook-comments' ),
				'reverse_time' => __( 'Newest first', 'gs-facebook-comments' ),
			)
		);
	}

	/**
	 * Render a radio group.
	 *
	 * @param string               $key      Option key.
	 * @param string               $selected Selected value.
	 * @param array<string,string> $choices  Available choices.
	 */
	private function render_radio_group( $key, $selected, $choices ) {
		foreach ( $choices as $value => $label ) {
			?>
			<label>
				<input type="radio" name="wpfc_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $selected, $value ); ?>>
				<?php echo esc_html( $label ); ?>
			</label><br>
			<?php
		}
	}

	/** Render locale. */
	public function render_locale() {
		$options = $this->get_settings();
		$value   = isset( $options['wpfc_option_data_locale'] ) ? $options['wpfc_option_data_locale'] : 'en_US';
		?>
		<select name="wpfc_settings[wpfc_option_data_locale]">
			<?php foreach ( $this->locales as $locale => $label ) : ?>
				<option value="<?php echo esc_attr( $locale ); ?>" <?php selected( $value, $locale ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/** Render post type selection. */
	public function render_post_types() {
		$options  = $this->get_settings();
		$selected = isset( $options['wpfc_checkbox_post_types'] ) && is_array( $options['wpfc_checkbox_post_types'] ) ? $options['wpfc_checkbox_post_types'] : array();
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ) {
			?>
			<label>
				<input type="checkbox" name="wpfc_settings[wpfc_checkbox_post_types][<?php echo esc_attr( $post_type->name ); ?>]" value="1" <?php checked( isset( $selected[ $post_type->name ] ) ); ?>>
				<?php echo esc_html( $post_type->labels->singular_name ); ?>
			</label><br>
			<?php
		}
	}

	/** Render section help. */
	public function render_post_types_intro() {
		printf(
			'<p>%s <code>[gs-fb-comments]</code></p>',
			esc_html__( 'Enable automatic output below, or keep using the existing shortcode:', 'gs-facebook-comments' )
		);
	}

	/**
	 * Register the per-post control.
	 *
	 * @param string $post_type Current post type.
	 */
	public function add_meta_box( $post_type ) {
		$options = $this->get_settings();
		if ( isset( $options['wpfc_checkbox_post_types'][ $post_type ] ) ) {
			add_meta_box(
				'genialsouls-social-comments',
				esc_html__( 'Genial Comments', 'gs-facebook-comments' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'side'
			);
		}
	}

	/**
	 * Render the per-post AJAX control.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_meta_box( $post ) {
		$allowed = metadata_exists( 'post', $post->ID, '_fb_comments' ) ? (bool) get_post_meta( $post->ID, '_fb_comments', true ) : true;
		$nonce   = wp_create_nonce( 'genialsouls-social-comments-post-' . $post->ID );
		?>
		<p>
			<label><input type="checkbox" id="genialsouls-social-comments-enabled" <?php checked( $allowed ); ?>> <?php esc_html_e( 'Allow social comments', 'gs-facebook-comments' ); ?></label>
		</p>
		<p>
			<button type="button" class="button" id="genialsouls-social-comments-save" data-post-id="<?php echo esc_attr( $post->ID ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>"><?php esc_html_e( 'Save', 'gs-facebook-comments' ); ?></button>
			<span class="spinner" id="genialsouls-social-comments-spinner"></span>
			<span id="genialsouls-social-comments-result" aria-live="polite"></span>
		</p>
		<?php
	}

	/** Save the per-post switch after object-level authorization. */
	public function save_post_setting_ajax() {
		if ( ! isset( $_POST['post_id'], $_POST['nonce'], $_POST['enabled'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Required request data is missing.', 'gs-facebook-comments' ) ), 400 );
		}

		$post_id = absint( wp_unslash( $_POST['post_id'] ) );
		$nonce   = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );
		$enabled = sanitize_key( wp_unslash( $_POST['enabled'] ) );

		if ( ! $post_id || ! get_post( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'The target post does not exist.', 'gs-facebook-comments' ) ), 404 );
		}

		if ( ! wp_verify_nonce( $nonce, 'genialsouls-social-comments-post-' . $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'gs-facebook-comments' ) ), 403 );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to edit this post.', 'gs-facebook-comments' ) ), 403 );
		}

		$value = in_array( $enabled, array( '1', 'true', 'on', 'yes' ), true ) ? 1 : 0;
		update_post_meta( $post_id, '_fb_comments', $value );

		wp_send_json_success( array( 'enabled' => (bool) $value ) );
	}
}
