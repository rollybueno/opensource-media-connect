<?php
/**
 * Main plugin class.
 *
 * @package Opensource_Media_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main plugin class.
 */
class Opensource_Media_Connect {

	/**
	 * Plugin instance.
	 *
	 * @var Opensource_Media_Connect
	 */
	private static $instance;

	/**
	 * Admin instance.
	 *
	 * @var Opensource_Media_Connect_Admin
	 */
	public $admin;

	/**
	 * API instance.
	 *
	 * @var Opensource_Media_Connect_API
	 */
	public $api;

	/**
	 * Get plugin instance.
	 *
	 * @return Opensource_Media_Connect
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once OPENSOURCE_MEDIA_CONNECT_PLUGIN_DIR . 'includes/class-opensource-media-connect-admin.php';
		require_once OPENSOURCE_MEDIA_CONNECT_PLUGIN_DIR . 'includes/class-opensource-media-connect-api.php';
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'admin_enqueue_scripts', 'enqueue_admin_assets' );
	}

	/**
	 * Initialize plugin.
	 */
	public function init() {
		$this->admin = new Opensource_Media_Connect_Admin();
		$this->api = new Opensource_Media_Connect_API();
	}

	/**
	 * Register blocks.
	 *
	 * This method is no longer used as blocks are registered in the main plugin file.
	 */
	public function register_blocks() {
		// This method is no longer used.
	}

	/**
	 * Enqueue block editor assets.
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_style(
			'opensource-media-connect-editor',
			OPENSOURCE_MEDIA_CONNECT_PLUGIN_URL . 'assets/css/editor.css',
			array(),
			OPENSOURCE_MEDIA_CONNECT_VERSION
		);
	}

	/**
	 * Enqueue admin assets
	 * 
	 * Make sure this only loads on intended admin pages to avoid cluttering
	 */
	public function enqueue_admin_assets() {
		wp_enqueue_style( 'opensource-media-connect-amind-style', OPENSOURCE_MEDIA_CONNECT_PLUGIN_URL . 'assets/css/admin.css', array(), OPENSOURCE_MEDIA_CONNECT_VERSION );
	}

	/**
	 * Plugin activation.
	 */
	public static function activate() {
		// Activation tasks if needed.
	}
}

