<?php
/**
 * Plugin Name:       Perennial Animated Widgets
 * Description:       Shortcodes for the animated Perennial marketing widgets coded from the Figma motion designs.
 * Version:           1.0.0
 * Author:            Perennial
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 *
 * Usage:  [perennial_widget id="scheduling"]
 *         [perennial_widget id="crew" loop="true" loop_delay="6000"]
 *
 * Valid ids: scheduling, crew, equipment, business
 *
 * The widget markup lives in widgets/*.html and is generated from the source files in the
 * repo by build-plugin.sh. Don't edit those copies by hand.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PERENNIAL_WIDGETS_VERSION = '1.0.0';
const PERENNIAL_WIDGETS_FONT_URL = 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap';

/**
 * Shortcode id => file in widgets/.
 *
 * @return array<string, string>
 */
function perennial_widgets_map() {
	return array(
		'scheduling' => 'scheduling-widget-responsive.html',
		'crew'       => 'crew-widget-responsive.html',
		'equipment'  => 'equipment-widget-responsive.html',
		'business'   => 'business-widget-responsive.html',
	);
}

/**
 * Register Plus Jakarta Sans once per page. Disable with:
 *   add_filter( 'perennial_widgets_load_font', '__return_false' );
 * if the theme already loads the family.
 */
function perennial_widgets_enqueue_font() {
	if ( ! apply_filters( 'perennial_widgets_load_font', true ) ) {
		return;
	}
	if ( wp_style_is( 'perennial-widgets-font', 'enqueued' ) ) {
		return;
	}
	wp_enqueue_style( 'perennial-widgets-font', PERENNIAL_WIDGETS_FONT_URL, array(), null );
}

/**
 * Add preconnect hints for the font host.
 *
 * @param array  $urls          URLs to print.
 * @param string $relation_type Hint type.
 * @return array
 */
function perennial_widgets_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type && apply_filters( 'perennial_widgets_load_font', true ) ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com' );
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'perennial_widgets_resource_hints', 10, 2 );

/**
 * Render a widget.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function perennial_widgets_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'id'         => '',
			'loop'       => 'false',
			'loop_delay' => '',
		),
		$atts,
		'perennial_widget'
	);

	$map = perennial_widgets_map();
	$id  = sanitize_key( $atts['id'] );

	if ( ! isset( $map[ $id ] ) ) {
		// Only hint at the problem for users who can fix it.
		return current_user_can( 'edit_posts' )
			? '<!-- perennial_widget: unknown id "' . esc_html( $id ) . '". Valid ids: ' . esc_html( implode( ', ', array_keys( $map ) ) ) . ' -->'
			: '';
	}

	$file = plugin_dir_path( __FILE__ ) . 'widgets/' . $map[ $id ];
	if ( ! is_readable( $file ) ) {
		return current_user_can( 'edit_posts' )
			? '<!-- perennial_widget: missing file ' . esc_html( $map[ $id ] ) . ' -->'
			: '';
	}

	$html = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- local plugin file, not a remote request.
	if ( false === $html ) {
		return '';
	}

	// The plugin enqueues the font, so drop the snippet's own <link> tags.
	$html = preg_replace( '#<link\b[^>]*>#i', '', $html );
	perennial_widgets_enqueue_font();

	if ( 'true' === $atts['loop'] ) {
		$replacement = 'data-loop="true"';
		$delay       = absint( $atts['loop_delay'] );
		if ( $delay > 0 ) {
			$replacement .= ' data-loop-delay="' . $delay . '"';
		}
		$html = str_replace( 'data-loop="false"', $replacement, $html );
	}

	return $html;
}
add_shortcode( 'perennial_widget', 'perennial_widgets_shortcode' );
