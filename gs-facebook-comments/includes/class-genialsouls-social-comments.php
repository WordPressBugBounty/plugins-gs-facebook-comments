<?php
/**
 * Core plugin orchestration.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://profiles.wordpress.org/qsheeraz#content-plugins
 * @since      1.0.0
 *
 * @package    GenialSouls_Social_Comments
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    GenialSouls_Social_Comments
 * @author     Qamar Sheeraz <qsheeraz@yahoo.com>
 */
class GenialSouls_Social_Comments {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      GenialSouls_Social_Comments_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'GENIALSOULS_SOCIAL_COMMENTS_VERSION' ) ) {
			$this->version = GENIALSOULS_SOCIAL_COMMENTS_VERSION;
		} else {
			$this->version = '1.8.2';
		}
		$this->plugin_name = 'genialsouls-social-comments';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->loader->add_action( 'init', $this, 'ensure_installation_state' );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - GenialSouls_Social_Comments_Loader. Orchestrates plugin hooks.
	 * - GenialSouls_Social_Comments_Admin. Defines admin hooks.
	 * - GenialSouls_Social_Comments_Public. Defines public hooks.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once __DIR__ . '/class-genialsouls-social-comments-loader.php';
		require_once __DIR__ . '/class-genialsouls-social-comments-provider-resolver.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once dirname( __DIR__ ) . '/admin/class-genialsouls-social-comments-admin.php';

		require_once __DIR__ . '/providers/interface.php';
		require_once __DIR__ . '/class-genialsouls-social-comments-provider-registry.php';
		require_once __DIR__ . '/providers/class-genialsouls-social-comments-facebook.php';
		require_once __DIR__ . '/providers/class-genialsouls-social-comments-disqus.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once dirname( __DIR__ ) . '/public/class-genialsouls-social-comments-public.php';

		$this->loader = new GenialSouls_Social_Comments_Loader();
	}

	/** Backfill an unmarked active installation conservatively as legacy. */
	public function ensure_installation_state() {
		if ( '' === get_option( GenialSouls_Social_Comments_Provider_Resolver::INSTALLATION_STATE_OPTION, '' ) ) {
			GenialSouls_Social_Comments_Provider_Resolver::set_installation_state( GenialSouls_Social_Comments_Provider_Resolver::STATE_LEGACY );
		}
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new GenialSouls_Social_Comments_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		/**
		 * Adding admin menu in main WordPress menu.
		 */
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_admin_menu' );

		/**
		 * Registering all plugin settings and options.
		 */
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

		/**
		 * Registering metabox for comments.
		 */
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_box', 10, 1 );

		/**
		 * Doing action on metabox for comments.
		 */
		$this->loader->add_action( 'wp_ajax_genialsouls_social_comments_save_post_setting', $plugin_admin, 'save_post_setting_ajax' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new GenialSouls_Social_Comments_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_service_sdk' );

		/**
		 * Loading meta property in wp_head.
		 */
		$this->loader->add_action( 'wp_head', $plugin_public, 'add_app_id_meta' );

		/**
		 * Loading Facebook JS SDK in wp_footer.
		 */
		$this->loader->add_action( 'wp_footer', $plugin_public, 'render_service_root' );

		/**
		 * Showing comments in the content.
		 */
		$this->loader->add_filter( 'the_content', $plugin_public, 'append_comments' );

		/**
		 * Showing comments in the content with shortcode.
		 */
		$this->loader->add_shortcode( 'gs-fb-comments', $plugin_public, 'render_shortcode' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    GenialSouls_Social_Comments_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
