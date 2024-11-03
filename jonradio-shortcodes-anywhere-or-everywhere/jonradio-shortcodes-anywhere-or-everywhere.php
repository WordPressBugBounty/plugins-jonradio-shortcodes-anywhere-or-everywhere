<?php
/**
 * Plugin Name
 *
 * @package           SHORTCODES_ANYWHERE
 * @author            InstallActivateGo.com
 * @copyright         Copyright (C) 2013-2024, InstallActivateGo.com
 *
 * @wordpress-plugin
 * Plugin Name:       Shortcodes Anywhere or Everywhere
 * Plugin URI:        https://installactivatego.com
 * Description:       Allows Shortcodes to be used nearly everywhere, not just in posts and pages.
 * Version:           1.4.3
 * Requires at least: 6.0
 * Requires PHP:      5.6
 * Author:            InstallActivateGo.com
 * Author URI:        https://installactivatego.com
 * Text Domain:       jonradio-shortcodes-anywhere-or-everywhere
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

/*	Exit if .php file accessed directly
*/
defined( 'ABSPATH' ) || exit;

global $jr_saoe_filters;
$jr_saoe_filters =
	array(
		array(
			'disabled'    => 'pages',
			'where'       => 'In Pages',
			'description' => '(standard WordPress behaviour)'
			),
		array(
			'disabled'    => 'posts',
			'where'       => 'In Posts',
			'description' => '(standard WordPress behaviour)'
			),
		array(
			'filter'      => 'wp_trim_excerpt',
			'functions'   => array(
								'jr_saoe_wp_trim_excerpt' => 2
								),
			'where'       => 'In Post Excerpts (automatic)',
			'description' => 'where Excerpts are automatically generated because Manual Excerpts do not exist'
			),
		array(
			'filter'      => 'get_the_excerpt',
			'where'       => 'In Post Excerpts (manual)',
			'description' => 'where Excerpts were typed into the (optional) Excerpt textbox of Add/Edit Post'
			),
		array(
			'filter'      => 'the_title',
			'where'       => 'In Titles',
			'description' => 'includes Page and Post titles everywhere except &lt;title&gt;'
			),
		array(
			'filter'      => 'single_post_title',
			'where'       => 'In Titles',
			'description' => 'Page and Post title in browser title bar via &lt;title&gt; - recommended method'
			),
			/*	'wp_title' also handles <title>,
				but 'single_post_title' also works with SEO Ultimate.
			*/
		array(
			'filter'      => 'wp_title',
			'where'       => 'In Titles',
			'description' => 'Page and Post title in browser title bar via &lt;title&gt; - alternate method'
			),
		array(
			'filter'      => 'widget_text',
			'functions'   => array(
								'shortcode_unautop' => 1,
								'do_shortcode'      => 1
								),
			'where'       => 'In Widgets',
			'description' => 'includes most Widgets, Sidebars and Footers'
			),
		array(
			'filter'      => 'widget_title',
			'where'       => 'In Widget Titles',
			'description' => 'includes Widget titles wherever they may be displayed'
			),
		array(
			'filter'      => 'bloginfo',
			'where'       => 'In Site Title/Description',
			'description' => 'all bloginfo options except "url", "directory" and "home"'
			),
		array(
			'filter'      => 'get_post_metadata',
			'functions'   => array(
								'jr_saoe_get_post_metadata' => 4
								),
			'where'       => 'In Post/Page Custom Fields',
			'description' => 'allows shortcodes in the Value, but not the Name, of a Custom Field'
			)
		);

global $jr_saoe_settings_default;
$jr_saoe_settings_default =
	array(
		'warn_nothing'  => TRUE
	);
foreach ( $jr_saoe_filters as $one_filter ) {
	if ( isset( $one_filter['filter'] ) ) {
		$jr_saoe_settings_default[ $one_filter['filter'] ] = FALSE;
		$jr_saoe_settings_default['priority'][ $one_filter['filter'] ] = 10;
	}
}
		
global $jr_saoe_path;
$jr_saoe_path = plugin_dir_path( __FILE__ );
/**
* Return Plugin's full directory path with trailing slash
* 
* Local XAMPP install might return:
*	C:\xampp\htdocs\wpbeta\wp-content\plugins\jonradio-shortcodes-anywhere-or-everywhere/
*
*/
function jr_saoe_path() {
	global $jr_saoe_path;
	return $jr_saoe_path;
}

if ( !function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
global $jr_saoe_plugin_data;
/*	Turn off Markup and Translation of Plugin Data
	to prevent call to _get_plugin_data_markup_translate()
*/
$jr_saoe_plugin_data = get_plugin_data( __FILE__, FALSE, FALSE );
$jr_saoe_plugin_data['slug'] = basename( dirname( __FILE__ ) );

global $jr_saoe_plugin_basename;
$jr_saoe_plugin_basename = plugin_basename( __FILE__ );
/**
* Return Plugin's Basename
* 
* For this plugin, it would be:
*	jonradio-shortcodes-anywhere-or-everywhere/jonradio-shortcodes-anywhere-or-everywhere.php
*
*/
function jr_saoe_plugin_basename() {
	global $jr_saoe_plugin_basename;
	return $jr_saoe_plugin_basename;
}
	
if ( function_exists( 'is_network_admin' ) && is_network_admin() ) {
} else {
	function jr_saoe_validate_wp_settings( $name, $defaults, $blog_id = 0 ) {
		switch ( $blog_id ) {
			case 0:
				$settings = get_option( $name );
				break;
			case -1:
				$settings = get_site_option( $name );
				break;
			default:
				$settings = get_blog_option( $blog_id, $name );
		}
		
		$updated = FALSE;
		if ( is_array( $settings ) ) {
			/*	Add any missing Settings, and set to Default values.
			*/
			foreach ( $defaults as $key => $value ) {
				if ( !isset( $settings[$key] ) ) {
					$settings[$key] = $value;
					$updated = TRUE;
				} else {
					if ( is_array( $value ) ) {
						foreach ( $value as $key2 => $value2 ) {
							if ( !isset( $settings[$key][$key2] ) ) {
								$settings[$key][$key2] = $value;
								$updated = TRUE;
							}
						}
					}
				}
			}
			/*	Remove any Settings not found in Defaults provided.
			*/
			foreach ( $settings as $key => $value ) {
				if ( !isset( $defaults[$key] ) ) {
					unset( $settings[$key] );
					$updated = TRUE;
				} else {
					if ( is_array( $value ) ) {
						foreach ( $value as $key2 => $value2 ) {
							if ( !isset( $defaults[$key][$key2] ) ) {
								$defaults[$key][$key2] = $value;
								$updated = TRUE;
							}
						}
					}
				}
			}
		} else {
			$settings = $defaults;
			$updated = TRUE;
		}
		
		if ( $updated ) {
			switch ( $blog_id ) {
				case 0:
					$return = update_option( $name, $settings );
					break;
				case -1:
					$return = update_site_option( $name, $settings );
					break;
				default:
					$return = update_blog_option( $blog_id, $name, $settings );
			}
		} else {
			$return = NULL;
		}
		return $return;
	}
	jr_saoe_validate_wp_settings( 'jr_saoe_settings', $jr_saoe_settings_default );
	if ( is_admin() ) {
		require_once( jr_saoe_path() . 'includes/admin.php' );
	} else {
		require_once( jr_saoe_path() . 'includes/public.php' );
	}
}

?>