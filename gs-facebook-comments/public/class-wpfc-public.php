<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://profiles.wordpress.org/qsheeraz#content-plugins
 * @since      1.0.0
 *
 * @package    Wpfc
 * @subpackage Wpfc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wpfc
 * @subpackage Wpfc/public
 * @author     Qamar Sheeraz <qsheeraz@yahoo.com>
 */
class Wpfc_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpfc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpfc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpfc-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpfc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpfc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpfc-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * add meta property function.
	 *
	 * @access public
	 * @return void
	 */
	public function wpfc_add_meta_property() {
		$options = get_option( 'wpfc_settings' );
		if ( isset ( $options['wpfc_text_fb_appid'] ) and $options['wpfc_text_fb_appid'] != '' )
			echo '<meta property="fb:app_id" content="'.sanitize_text_field($options['wpfc_text_fb_appid']).'" />';
	}

	/**
	 * Facebook_js_sdk function.
	 *
	 * @access public
	 * @return void
	 */
	public function wpfc_add_facebook_js_sdk() {
		
		$options = get_option( 'wpfc_settings' );
		if ( !isset ( $options['wpfc_option_data_locale'] ) )
			$options['wpfc_option_data_locale'] = 'en_US';

		if ( !isset ( $options['wpfc_text_fb_appid'] ) or  $options['wpfc_text_fb_appid'] == '' )
			$fb_appid = '';
		else
			$fb_appid = '&appId=' . $options['wpfc_text_fb_appid'];

	  	?>

	  	<div id="fb-root"></div>
		<script 
			async defer crossorigin="anonymous" 
			src="https://connect.facebook.net/<?php echo esc_attr($options['wpfc_option_data_locale']) ?>/sdk.js#xfbml=1&version=v12.0<?php echo esc_attr($fb_appid); ?>&autoLogAppEvents=1">
		</script>

	  	<?php
	}

	/**
	 * show Facebook comments function.
	 *
	 * @access public
	 * @return $content
	 */
	public function wpfc_show_facebook_comments($content) {

		$post_id     = get_the_ID();
		$options     = get_option( 'wpfc_settings' );

		$fb_comments = metadata_exists('post', $post_id, '_fb_comments') ? filter_var(get_post_meta( $post_id, '_fb_comments', true ), FILTER_VALIDATE_BOOLEAN) : true;
		if( $fb_comments && isset($options['wpfc_checkbox_post_types'][get_post_type( $post_id )]) != '' ){

			if ( isset ( $options['wpfc_checkbox_eu_notice'] ) and $options['wpfc_checkbox_eu_notice'] == 1 ){
				$ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
				if( $this->wpfc_user_required_login($ip) ){
					$fb_notice_div = '<div id = "aler" class="alert warning"> 
		  							  <strong>'.__( 'Facebook Notice for EU!', 'gs-facebook-comments').'</strong> 
		  							  '.__( 'You need to login to view and post FB Comments!', 'gs-facebook-comments').'
									  </div>';
				} else {
					$fb_notice_div = '';
				}
			} else {
				$fb_notice_div = '';
			}
	
			if ( !isset ( $options['wpfc_text_comments_count'] ) or !intval( $options['wpfc_text_comments_count'] ) )
				$options['wpfc_text_comments_count'] = 10;
			$data_numposts = (int)$options['wpfc_text_comments_count'];

			if ( !isset ( $options['wpfc_text_data_width'] ) or $options['wpfc_text_data_width'] == '' )
				$data_width = $options['wpfc_text_data_width'] = '100%';
			else
				$data_width = (int)$options['wpfc_text_data_width'] . 'px';

			if ( !isset ( $options['wpfc_option_data_colorscheme'] ) )
				$options['wpfc_option_data_colorscheme'] = 'light';
			$data_colorscheme = $options['wpfc_option_data_colorscheme'];

			if ( !isset ( $options['wpfc_option_bg_color'] ) )
				$options['wpfc_option_bg_color'] = '';
			$bg_color = $options['wpfc_option_bg_color'];

			if ( !isset ( $options['wpfc_option_data_orderby'] ) )
				$options['wpfc_option_data_orderby'] = 'social';
			$data_orderby = $options['wpfc_option_data_orderby'];

			if ( !isset ( $options['wpfc_text_fb_appid'] ) or  $options['wpfc_text_fb_appid'] == '' )
				$fb_appid = '';
			else
				$fb_appid = '&appId=' . $options['wpfc_text_fb_appid'];

			$socio_link = get_permalink( $post_id );
			
			if ( isset ( $options['wpfc_checkbox_ssl_comments'] ) and $options['wpfc_checkbox_ssl_comments'] == 1 )
				$socio_link = str_replace( 'https://', 'http://', $socio_link );
			
			if ( isset ( $options['wpfc_checkbox_fb_cache'] ) and $options['wpfc_checkbox_fb_cache'] == 1 ){
				wp_remote_post("https://developers.facebook.com/tools/debug/?q=".get_permalink($post_id)."&scrape=true");
			}

		    $comments_div = '<div class="fb-background-color">
			  <div 
			  	class = "fb-comments" 
			  	data-href = "' . esc_url($socio_link) . '"
			  	data-numposts = "' . (int)$data_numposts . '"
			  	data-lazy = "true"
				data-colorscheme = "' . sanitize_text_field($data_colorscheme) . '"
				data-order-by = "' . sanitize_text_field($data_orderby) . '"
				data-mobile=true>
			  </div></div>
		  <style>
		    .fb-background-color {
				background: '.$bg_color.' !important;
			}
			.fb_iframe_widget_fluid_desktop iframe {
			    width: '.$data_width.' !important;
			}
		  </style>
		  ';

			$comments_div.=$fb_notice_div;
			return $content.=$comments_div;	
		}
		else 
			return $content;
	}

	/**
	 * show shortcode comments function.
	 *
	 * @access public
	 * @return $content
	 */
	public function wpfc_show_facebook_comments_shortcode($content) {
		$post_id = get_the_ID();
		$options = get_option( 'wpfc_settings' );
		
		if ( isset ( $options['wpfc_checkbox_eu_notice'] ) and $options['wpfc_checkbox_eu_notice'] == 1 ){
			$ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
			if( $this->wpfc_user_required_login($ip) ){
				$fb_notice_div = '<div id = "aler" class="alert warning"> 
	  							  <strong>'.__( 'Facebook Notice for EU!', 'gs-facebook-comments').'</strong> 
	  							  '.__( 'You need to login to view and post FB Comments!', 'gs-facebook-comments').'
								  </div>';
			} else {
					$fb_notice_div = '';
			}
		} else {
			$fb_notice_div = '';
		}

		if ( !isset ( $options['wpfc_text_comments_count'] ) or !intval( $options['wpfc_text_comments_count'] ) )
			$options['wpfc_text_comments_count'] = 10;
		$data_numposts = (int)$options['wpfc_text_comments_count'];

		if ( !isset ( $options['wpfc_text_data_width'] ) or $options['wpfc_text_data_width'] == '' )
			$data_width = $options['wpfc_text_data_width'] = '100%';
		else
			$data_width = (int)$options['wpfc_text_data_width'] . 'px';

		if ( !isset ( $options['wpfc_option_data_colorscheme'] ) )
			$options['wpfc_option_data_colorscheme'] = 'light';
		$data_colorscheme = $options['wpfc_option_data_colorscheme'];

		if ( !isset ( $options['wpfc_option_bg_color'] ) )
			$options['wpfc_option_bg_color'] = '';
		$bg_color = $options['wpfc_option_bg_color'];

		if ( !isset ( $options['wpfc_option_data_orderby'] ) )
			$options['wpfc_option_data_orderby'] = 'social';
		$data_orderby = $options['wpfc_option_data_orderby'];

		if ( !isset ( $options['wpfc_text_fb_appid'] ) or  $options['wpfc_text_fb_appid'] == '' )
			$fb_appid = '';
		else
			$fb_appid = '&appId=' . $options['wpfc_text_fb_appid'];

		$socio_link = get_permalink( $post_id );
		if ( isset ( $options['wpfc_checkbox_ssl_comments'] ) and $options['wpfc_checkbox_ssl_comments'] == 1 )
				$socio_link = str_replace( 'https://', 'http://', $socio_link );
		
		if ( isset ( $options['wpfc_checkbox_fb_cache'] ) and $options['wpfc_checkbox_fb_cache'] == 1 ){
			wp_remote_post("https://developers.facebook.com/tools/debug/?q=".get_permalink($post_id)."&scrape=true");
		}

	    $comments_div = '<div class="fb-background-color">
		  <div 
		  	class = "fb-comments" 
		  	data-href = "' . esc_url($socio_link). '"
		  	data-numposts = "' . (int)$data_numposts . '"
			data-lazy = "true"
			data-colorscheme = "' . sanitize_text_field($data_colorscheme) . '"
			data-order-by = "' . sanitize_text_field($data_orderby) . '"
			data-mobile=true>
		  </div></div>
		  <style>
		  	.fb-background-color {
				background: '.$bg_color.' !important;
			}
			.fb_iframe_widget_fluid_desktop iframe {
			    width: '.$data_width.' !important;
			}
		  </style>
		  ';
		$comments_div.=$fb_notice_div;
		return $comments_div;
	}

	/**
	 * check if user required Facebook login.
	 *
	 * @access Private
	 * @return boolean
	 */
	private function wpfc_user_required_login( $ip ){

		$response = wp_remote_get("http://ip-api.com/json/".$ip."?fields=3205407");
		$query = (array)json_decode(wp_remote_retrieve_body( $response ));

		if($query && $query['status'] == 'success') {
		  $countryCodes = array("MQ", "YT", "GP", "GF", "RE", "MF", "PT", "ES");
		  if ( $query['continentCode'] == 'EU' or in_array($query['countryCode'], $countryCodes) ) {
		  return true;
		  } else {
		  	return false;
		  }
		} else {
		  return true;
		}
	}
}