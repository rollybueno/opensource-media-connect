<?php
/**
 * Handles the API functionality of the Openverse Connect plugin.
 *
 * @package Openverse_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Openverse Connect API Class.
 *
 * Handles all API interactions with the Openverse service.
 * This includes authentication, search requests, and media retrieval.
 *
 * @since 1.0.0
 */
class Openverse_Connect_API extends Opensource_Media_Connect_Admin {

	/**
	 * Constructor.
	 *
	 * Initializes the API class and sets up necessary hooks.
	 * Registers REST API endpoints for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * Registers the custom REST API endpoints for the plugin.
	 * Sets up the search endpoint with proper parameters and callbacks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			'openverse-connect/v1',
			'/search',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_search_request' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'q'          => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'page'       => array(
						'default'           => 1,
						'sanitize_callback' => 'absint',
					),
					'media_type' => array(
						'default'           => 'image',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'license'    => array(
						'default'           => 'all',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Check if user has permission to use the API.
	 *
	 * Verifies that the current user has the necessary permissions
	 * to access the Openverse API endpoints.
	 *
	 * @since 1.0.0
	 * @return bool Whether the user has permission.
	 */
	public function check_permissions() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Handle search request to Openverse API.
	 *
	 * Processes search requests and returns media results.
	 * Handles pagination, media type filtering, and license filtering.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function handle_search_request( $request ) {
		$query      = $request->get_param( 'q' );
		$page       = $request->get_param( 'page' );
		$media_type = $request->get_param( 'media_type' );
		$license    = $request->get_param( 'license' );

		$access_token = get_transient( 'openverse_connect_access_token' );
		if ( false === $access_token ) {
			// Get access token from Openverse Connect admin class.
			$access_token = $this->get_client_credentials_token();
		}

		$api_url = 'https://api.openverse.org/v1/' . $media_type . 's/';
		$api_url = add_query_arg(
			array(
				'q'        => esc_html( $query ),
				'page'     => $page,
				'per_page' => 20,
			),
			$api_url
		);

		// Add license filter if not "all"
		if ( 'all' !== $license ) {
			$api_url = add_query_arg( array( 'license' => $license ), $api_url );
		}

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'api_error',
				$response->get_error_message(),
				array( 'status' => 500 )
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $body['results'] ) ) {
			return new WP_Error(
				'invalid_response',
				__( 'Invalid response from Openverse API', 'openverse-connect' ),
				array( 'status' => 500 )
			);
		}

		// Process results to standardize format
		$results = array();
		foreach ( $body['results'] as $item ) {
			$result = array(
				'id'        => $item['id'],
				'title'     => $item['title'],
				'creator'   => isset( $item['creator'] ) ? $item['creator'] : '',
				'license'   => isset( $item['license'] ) ? $item['license'] : '',
				'url'       => isset( $item['url'] ) ? $item['url'] : '',
				'thumbnail' => '',
			);

			// Handle different media types
			if ( 'image' === $media_type ) {
				$result['thumbnail'] = isset( $item['thumbnail'] ) ? $item['thumbnail'] : $item['url'];
			}

			$results[] = $result;
		}

		return rest_ensure_response(
			array(
				'results'    => $results,
				'page'       => $page,
				'page_count' => isset( $body['page_count'] ) ? $body['page_count'] : 1,
			)
		);
	}
} 