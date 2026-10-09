<?php
/**
 * Plugin activation handler.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin activation
 *
 * @link       https://profiles.wordpress.org/qsheeraz#content-plugins
 * @since      1.0.0
 *
 * @package    GenialSouls_Social_Comments
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    GenialSouls_Social_Comments
 * @author     Qamar Sheeraz <qsheeraz@yahoo.com>
 */
class GenialSouls_Social_Comments_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$state = get_option( GenialSouls_Social_Comments_Provider_Resolver::INSTALLATION_STATE_OPTION, '' );
		if ( '' !== $state ) {
			return;
		}

		$missing  = '__genialsouls_social_comments_missing__';
		$settings = get_option( 'wpfc_settings', $missing );
		$state    = $missing === $settings ? GenialSouls_Social_Comments_Provider_Resolver::STATE_NEW : GenialSouls_Social_Comments_Provider_Resolver::STATE_LEGACY;
		GenialSouls_Social_Comments_Provider_Resolver::set_installation_state( $state );
	}
}
