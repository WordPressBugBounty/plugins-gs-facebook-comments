<?php
/**
 * Validated appearance preferences for theme-owned WordPress comments.
 *
 * @package GenialSouls_Social_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Normalizes optional native comment appearance settings. */
class GenialSouls_Social_Comments_Native_Appearance {

	/** Appearance setting key. */
	const PRESET_OPTION = 'genialsouls_native_appearance';

	/** Accent setting key. */
	const ACCENT_OPTION = 'genialsouls_native_accent';

	/** Supported appearance presets. */
	const PRESETS = array( 'default', 'modern', 'minimal' );

	/** Validated accent palette. */
	const ACCENTS = array(
		'blue'    => '#2271b1',
		'green'   => '#16834a',
		'purple'  => '#7653a6',
		'neutral' => '#646970',
	);

	/**
	 * Return a supported preset, safely defaulting to theme styling.
	 *
	 * @param mixed $value Stored value.
	 * @return string Supported preset.
	 */
	public static function normalize_preset( $value ) {
		$value = is_scalar( $value ) ? sanitize_key( (string) $value ) : '';
		return in_array( $value, self::PRESETS, true ) ? $value : 'default';
	}

	/**
	 * Return a supported accent identifier, safely defaulting to blue.
	 *
	 * @param mixed $value Stored value.
	 * @return string Supported palette key.
	 */
	public static function normalize_accent( $value ) {
		$value = is_scalar( $value ) ? sanitize_key( (string) $value ) : '';
		return array_key_exists( $value, self::ACCENTS ) ? $value : 'blue';
	}

	/**
	 * Resolve the validated accent color value.
	 *
	 * @param mixed $value Stored palette key.
	 * @return string Hex color from the fixed palette.
	 */
	public static function get_accent_color( $value ) {
		$accent = self::normalize_accent( $value );
		return self::ACCENTS[ $accent ];
	}
}
