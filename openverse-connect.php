<?php
/**
 * Plugin Name: Openverse Connect
 * Plugin URI: https://wordpress.org/plugins/openverse-connect/
 * Description: OpenVerse Connect seamlessly integrates Openverse with your WordPress site, enabling you to effortlessly search, browse, and embed high-quality, copyright-free media content directly into your posts and pages. Whether you're adding images, audio, or video, OpenVerse Connect ensures that your site stays rich with diverse, free-to-use media.
 * Version: 1.0.0
 * Author: Rolly G. Bueno Jr.
 * Author URI: https://github.com/rolygbueno
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: openverse-connect
 *
 * @package Openverse_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'OPENVERSE_CONNECT_VERSION', '1.0.0' );
define( 'OPENVERSE_CONNECT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPENVERSE_CONNECT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files.
require_once OPENVERSE_CONNECT_PLUGIN_DIR . 'includes/class-openverse-connect-admin.php';

/**
 * Initialize the plugin.
 *
 * @return void
 */
function openverse_connect_init() {
	// Initialize admin.
	new Openverse_Connect_Admin();
}
add_action( 'plugins_loaded', 'openverse_connect_init' );

/**
 * Activation hook callback.
 *
 * Sets up the default options when the plugin is activated.
 *
 * @return void
 */
function openverse_connect_activate() {
	// Add default options.
	add_option( 'openverse_connect_client_id', '' );
	add_option( 'openverse_connect_client_secret', '' );
}
register_activation_hook( __FILE__, 'openverse_connect_activate' );

/**
 * Deactivation hook callback.
 *
 * Performs cleanup when the plugin is deactivated.
 *
 * @return void
 */
function openverse_connect_deactivate() {
	// Cleanup if needed.
}
register_deactivation_hook( __FILE__, 'openverse_connect_deactivate' ); 