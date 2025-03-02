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
 * Class to handle all API functionality.
 */
class Openverse_Connect_API {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Register REST API routes.
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
	 * @return bool Whether the user has permission.
	 */
	public function check_permissions() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Handle search request to Openverse API.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function handle_search_request( $request ) {
		$query      = $request->get_param( 'q' );
		$page       = $request->get_param( 'page' );
		$media_type = $request->get_param( 'media_type' );
		$license    = $request->get_param( 'license' );

		$access_token = get_option( 'openverse_connect_access_token' );
		if ( empty( $access_token ) ) {
			return new WP_Error(
				'not_connected',
				__( 'Not connected to Openverse. Please connect in the plugin settings.', 'openverse-connect' ),
				array( 'status' => 403 )
			);
		}

		$api_url = 'https://api.openverse.org/v1/' . $media_type . 's/';
		$api_url = add_query_arg(
			array(
				'q'        => $query,
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
			} elseif ( 'audio' === $media_type ) {
				$result['thumbnail'] = ''; // Audio doesn't have thumbnails
				$result['url'] = isset( $item['audio_url'] ) ? $item['audio_url'] : '';
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