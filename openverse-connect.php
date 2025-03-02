<?php
/**
 * Plugin Name: Openverse Connect
 * Description: Connect WordPress with Openverse to search and use openly licensed media.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: openverse-connect
 * Domain Path: /languages
 * License: GPL-2.0+
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

// Include the main plugin class.
require_once OPENVERSE_CONNECT_PLUGIN_DIR . 'includes/class-openverse-connect.php';

/**
 * Main function to initialize the plugin.
 */
function openverse_connect_init() {
	return Openverse_Connect::get_instance();
}

// Initialize the plugin.
$GLOBALS['openverse_connect'] = openverse_connect_init();

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

/**
 * Register block scripts.
 */
function openverse_connect_register_block() {
	// Check if build directory exists
	$build_dir = OPENVERSE_CONNECT_PLUGIN_DIR . 'build/openverse-search';
	
	if (file_exists($build_dir)) {
		// Register block from build directory
		register_block_type($build_dir);
	} else {
		// Fallback to manual registration for development
		wp_register_script(
			'openverse-connect-block',
			OPENVERSE_CONNECT_PLUGIN_URL . 'blocks/openverse-search/index.js',
			array( 
				'wp-blocks', 
				'wp-element', 
				'wp-editor', 
				'wp-components', 
				'wp-i18n', 
				'wp-api-fetch' 
			),
			filemtime( OPENVERSE_CONNECT_PLUGIN_DIR . 'blocks/openverse-search/index.js' ),
			true
		);

		wp_register_style(
			'openverse-connect-block-editor',
			OPENVERSE_CONNECT_PLUGIN_URL . 'blocks/openverse-search/editor.css',
			array( 'wp-edit-blocks' ),
			filemtime( OPENVERSE_CONNECT_PLUGIN_DIR . 'blocks/openverse-search/editor.css' )
		);

		wp_register_style(
			'openverse-connect-block',
			OPENVERSE_CONNECT_PLUGIN_URL . 'blocks/openverse-search/style.css',
			array(),
			filemtime( OPENVERSE_CONNECT_PLUGIN_DIR . 'blocks/openverse-search/style.css' )
		);

		register_block_type( 'openverse-connect/search', array(
			'editor_script' => 'openverse-connect-block',
			'editor_style'  => 'openverse-connect-block-editor',
			'style'         => 'openverse-connect-block',
		) );
	}
}
add_action( 'init', 'openverse_connect_register_block' );
