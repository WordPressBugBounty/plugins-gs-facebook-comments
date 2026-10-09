<?php
/**
 * Small registry for comments providers.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Stores provider implementations and permits future registration. */
class GenialSouls_Social_Comments_Provider_Registry {

	/**
	 * Registered providers.
	 *
	 * @var array<string,GenialSouls_Social_Comments_Provider_Interface>
	 */
	private $providers = array();

	/**
	 * Register a provider.
	 *
	 * @param GenialSouls_Social_Comments_Provider_Interface $provider Provider.
	 */
	public function register( GenialSouls_Social_Comments_Provider_Interface $provider ) {
		$this->providers[ $provider->get_id() ] = $provider;
	}

	/**
	 * Get a provider by ID.
	 *
	 * @param string $id Provider ID.
	 * @return GenialSouls_Social_Comments_Provider_Interface|null
	 */
	public function get( $id ) {
		return isset( $this->providers[ $id ] ) ? $this->providers[ $id ] : null;
	}

	/**
	 * Return all providers after the extension filter.
	 *
	 * @return array<string,GenialSouls_Social_Comments_Provider_Interface>
	 */
	public function all() {
		return apply_filters( 'genialsouls_social_comments_providers', $this->providers );
	}
}
