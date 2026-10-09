<?php
/**
 * Central provider and installation-state resolution.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Resolves provider policy without rewriting stored settings. */
class GenialSouls_Social_Comments_Provider_Resolver {

	/** Installation-state option. */
	const INSTALLATION_STATE_OPTION = 'genialsouls_social_comments_installation_state';

	/** New installation state. */
	const STATE_NEW = 'new';

	/** Legacy installation state. */
	const STATE_LEGACY = 'legacy';

	/** Supported provider IDs. */
	const PROVIDERS = array( 'facebook', 'disqus' );

	/** Return the persisted installation state, conservatively defaulting to legacy. */
	public static function get_installation_state() {
		$state = get_option( self::INSTALLATION_STATE_OPTION, '' );
		return in_array( $state, array( self::STATE_NEW, self::STATE_LEGACY ), true ) ? $state : self::STATE_LEGACY;
	}

	/**
	 * Persist an installation state idempotently.
	 *
	 * @param string $state Installation state.
	 * @return bool Whether the option was updated.
	 */
	public static function set_installation_state( $state ) {
		if ( ! in_array( $state, array( self::STATE_NEW, self::STATE_LEGACY ), true ) ) {
			return false;
		}

		return update_option( self::INSTALLATION_STATE_OPTION, $state, false );
	}

	/** Return the default provider for a known installation state. */
	public static function get_default_provider() {
		return self::STATE_NEW === self::get_installation_state() ? 'disqus' : 'facebook';
	}

	/**
	 * Return an explicit provider ID, null for absent, or false for invalid.
	 *
	 * @param array<string,mixed> $options Stored settings.
	 * @return string|false|null Provider ID, invalid marker, or absent marker.
	 */
	public static function get_explicit_provider( array $options ) {
		if ( ! array_key_exists( 'genialsouls_provider', $options ) ) {
			return null;
		}

		$value = $options['genialsouls_provider'];
		if ( ! is_scalar( $value ) ) {
			return false;
		}

		$value = sanitize_key( (string) $value );
		return in_array( $value, self::PROVIDERS, true ) ? $value : false;
	}

	/**
	 * Resolve a provider ID, returning null for an invalid explicit value.
	 *
	 * @param array<string,mixed> $options Stored settings.
	 * @return string|null Provider ID or invalid marker.
	 */
	public static function resolve( array $options ) {
		$explicit = self::get_explicit_provider( $options );
		if ( null === $explicit ) {
			return self::get_default_provider();
		}

		return false === $explicit ? null : $explicit;
	}

	/**
	 * Determine whether settings contain an invalid explicit provider.
	 *
	 * @param array<string,mixed> $options Stored settings.
	 * @return bool Whether the provider is invalid.
	 */
	public static function has_invalid_provider( array $options ) {
		return false === self::get_explicit_provider( $options );
	}
}
