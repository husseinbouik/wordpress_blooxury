<?php
/**
 * Style functions for blocks.
 *
 * @since 2.0
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 */
namespace EDD\Blocks\Styles;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action( 'enqueue_block_assets', __NAMESPACE__ . '\add_to_global_styles' );
/**
 * Adds our custom EDD button colors to the global stylesheet.
 *
 * @since 2.0
 * @return void
 */
function add_to_global_styles() {
	$styles = array(
		'--edd-blocks-light-grey:#eee;',
	);
	$rules  = array();
	$colors = edd_get_option( 'button_colors' );
	if ( ! empty( $colors ) ) {
		foreach ( $colors as $setting => $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			$styles[] = "--edd-blocks-button-{$setting}:{$value };";
			if ( 'text' === $setting ) {
				$rules[] = '.edd-submit,.has-edd-button-text-color{color: var(--edd-blocks-button-text) !important;}';
			} elseif ( 'background' === $setting ) {
				$rules[] = '.edd-submit,.has-edd-button-background-color{background-color: var(--edd-blocks-button-background) !important;}';
				$rules[] = '.has-edd-button-background-text-color{color: var(--edd-blocks-button-background) !important;}';
			}
		}
	}
	if ( empty( $styles ) ) {
		return;
	}
	$inline_style = 'body{' . implode( ' ', $styles ) . '}';
	if ( ! empty( $rules ) ) {
		$inline_style .= implode( ' ', $rules );
	}
	$stylesheet = wp_style_is( 'edd-styles', 'registered' ) ? 'edd-styles' : 'global-styles';
	wp_add_inline_style( $stylesheet, $inline_style );
}

add_filter( 'edd_button_color_class', __NAMESPACE__ . '\update_button_color_class' );
/**
 * Update the EDD button color class from the new color settings.
 *
 * @since 2.0
 * @param string $class
 * @return string
 */
function update_button_color_class( $class ) {
	$classes       = array();
	$color_options = edd_get_option( 'button_colors' );
	if ( ! empty( $color_options['background'] ) ) {
		$classes[] = 'has-edd-button-background-color';
	}
	if ( ! empty( $color_options['text'] ) ) {
		$classes[] = 'has-edd-button-text-color';
	}

	return ! empty( $classes ) ? implode( ' ', $classes ) : $class;
}
