<?php
/**
 * Handles the admin functionality of the Opensource Media Connect plugin.
 *
 * @package Opensource_Media_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Opensource Media Connect Admin Class.
 *
 * Handles all administrative functionality for the Opensource Media Connect plugin.
 * This includes settings pages, API key management, and admin-specific features.
 *
 * @since 1.0.0
 */
class Opensource_Media_Connect_Admin {

	/**
	 * Constructor.
	 *
	 * Sets up the admin hooks and initializes the admin functionality.
	 * Registers menu items, settings, and admin notices.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_opensource_media_connect_oauth', array( $this, 'handle_oauth_redirect' ) );
		add_action( 'admin_post_opensource_media_register_app', array( $this, 'handle_app_registration' ) );
		add_action( 'admin_post_opensource_media_connect_token', array( $this, 'handle_token_request' ) );
		add_action( 'admin_notices', array( $this, 'display_oauth_notices' ) );
	}

	/**
	 * Add menu item to WordPress admin.
	 *
	 * Creates the settings page menu item under Settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Opensource Media Connect Settings', 'opensource-media-connect' ),
			__( 'Open Source Media', 'opensource-media-connect' ),
			'manage_options',
			'opensource-media-connect',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * Registers the settings fields and sections for the plugin.
	 * Includes client ID, client secret, and access token settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings() {
		// Check and refresh access token if needed.
		$access_token = get_transient( 'opensource_media_connect_access_token' );
		if ( false === $access_token && get_option( 'opensource_media_connect_client_id' ) ) {
			$new_token = $this->get_client_credentials_token();
			if ( ! is_wp_error( $new_token ) ) {
				update_option( 'opensource_media_connect_access_token', $new_token );
			}
		}

		// Register client ID setting.
		register_setting(
			'opensource_media_connect_settings',
			'opensource_media_connect_client_id',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		// Register client secret setting.
		register_setting(
			'opensource_media_connect_settings',
			'opensource_media_connect_client_secret',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		// Register access token setting.
		register_setting(
			'opensource_media_connect_settings',
			'opensource_media_connect_access_token',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);
	}

	/**
	 * Render the settings page.
	 *
	 * Displays the main settings page for the plugin.
	 * Shows connection status, registration form, and credentials.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings_page() {
		$client_id               = get_option( 'opensource_media_connect_client_id' );
		$client_secret           = get_option( 'opensource_media_connect_client_secret' );
		$access_token            = get_option( 'opensource_media_connect_access_token' );
		$is_connected            = ! empty( $access_token );
		$has_credentials         = ! empty( $client_id ) && ! empty( $client_secret );
		$show_manual_credentials = isset( $_GET['error'] ) && 'email_already_registered' === $_GET['error'];
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Open Source Media Settings', 'opensource-media-connect' ); ?></h1>

			<?php if ( ! $is_connected ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'You need to connect to Openverse to use this plugin.', 'opensource-media-connect' ); ?></p>
					<?php if ( $has_credentials ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="opensource_media_connect_token">
						<?php wp_nonce_field( 'opensource_media_connect_token' ); ?>
						<p>
							<button type="submit" class="button button-primary">
								<?php esc_html_e( 'Connect to Openverse', 'opensource-media-connect' ); ?>
							</button>
						</p>
					</form>
				<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="opensource-media-connect-oauth">
				<h2><?php esc_html_e( 'Connection Status', 'opensource-media-connect' ); ?></h2>
				<?php if ( $is_connected ) : ?>
					<p class="opensource-media-connect-status connected">
						<span class="dashicons dashicons-yes-alt"></span>
						<?php esc_html_e( 'Connected to Openverse', 'opensource-media-connect' ); ?>
					</p>
				<?php elseif ( $has_credentials ) : ?>
					<p class="opensource-media-connect-status not-connected">
						<span class="dashicons dashicons-warning"></span>
						<?php esc_html_e( 'Not connected to Openverse', 'opensource-media-connect' ); ?>
					</p>
				<?php else : ?>
					<p class="opensource-media-connect-status not-registered">
						<span class="dashicons dashicons-admin-plugins"></span>
						<?php esc_html_e( 'Register your WordPress site with Openverse', 'opensource-media-connect' ); ?>
					</p>
					<p class="description">
						<?php esc_html_e( 'This will create a new application registration with Openverse automatically. You can only have one application registration per email address.', 'opensource-media-connect' ); ?>
					</p>
					<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="opensource_media_register_app">
						<?php wp_nonce_field( 'opensource_media_register_app' ); ?>

						<p class="description">
							<input type="email" 
								name="email" 
								id="opensource-media-connect-email" 
								value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" 
								placeholder="<?php esc_attr_e( 'Your email address', 'opensource-media-connect' ); ?>"
								required
								class="regular-text"
							>
						</p>

						<?php if ( $show_manual_credentials ) : ?>
							<div class="manual-credentials">
								<p class="description">
									<?php esc_html_e( 'An application is already registered with this email. If you already have credentials, you can enter them below:', 'opensource-media-connect' ); ?>
								</p>
								<p>
									<label for="opensource-media-connect-client-id"><?php esc_html_e( 'Client ID', 'opensource-media-connect' ); ?></label>
									<input type="text"
										name="client_id"
										id="opensource-media-connect-client-id"
										class="regular-text"
										placeholder="<?php esc_attr_e( 'Enter your existing Client ID', 'opensource-media-connect' ); ?>"
									>
								</p>
								<p>
									<label for="opensource-media-connect-client-secret"><?php esc_html_e( 'Client Secret', 'opensource-media-connect' ); ?></label>
									<input type="password"
										name="client_secret"
										id="opensource-media-connect-client-secret"
										class="regular-text"
										placeholder="<?php esc_attr_e( 'Enter your existing Client Secret', 'opensource-media-connect' ); ?>"
									>
								</p>
							</div>
						<?php endif; ?>

						<p>
							<button type="submit" class="button button-primary">
								<?php echo $show_manual_credentials ? esc_html__( 'Save Credentials', 'opensource-media-connect' ) : esc_html__( 'Register with Openverse', 'opensource-media-connect' ); ?>
							</button>
						</p>
					</form>
				<?php endif; ?>

				<?php if ( $has_credentials ) : ?>
					<hr>
					<h3><?php esc_html_e( 'Application Credentials', 'opensource-media-connect' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Client ID', 'opensource-media-connect' ); ?></th>
							<td><code><?php echo esc_html( $client_id ); ?></code></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Client Secret', 'opensource-media-connect' ); ?></th>
							<td>
								<code>••••••••</code>
								<button type="button" class="button-link" onclick="this.previousElementSibling.textContent = '<?php echo esc_js( $client_secret ); ?>'">
									<?php esc_html_e( 'Show', 'opensource-media-connect' ); ?>
								</button>
							</td>
						</tr>
					</table>
				<?php endif; ?>
			</div>
		</div>

		<style>
			.opensource-media-connect-oauth {
				margin-top: 20px;
				padding: 20px;
				background: #fff;
				border: 1px solid #ccd0d4;
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
			}
			.opensource-media-connect-status {
				display: flex;
				align-items: center;
				gap: 8px;
				margin: 15px 0;
				font-size: 14px;
			}
			.opensource-media-connect-status .dashicons {
				font-size: 20px;
				width: 20px;
				height: 20px;
			}
			.opensource-media-connect-status.connected .dashicons {
				color: #46b450;
			}
			.opensource-media-connect-status.not-connected .dashicons {
				color: #dc3232;
			}
			.opensource-media-connect-status.not-registered .dashicons {
				color: #007cba;
			}
			.description {
				color: #646970;
				font-style: italic;
				margin: 5px 0 15px;
			}
			.manual-credentials {
				margin: 15px 0;
				padding: 15px;
				background: #f0f0f1;
				border-left: 4px solid #007cba;
			}
			.manual-credentials label {
				display: block;
				margin-bottom: 5px;
				font-weight: 600;
			}
		</style>
		<?php
	}

	/**
	 * Handle application registration with Openverse.
	 *
	 * Processes the registration form submission and creates a new application
	 * registration with the Openverse API.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_app_registration() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'opensource-media-connect' ) );
		}

		check_admin_referer( 'opensource_media_register_app' );

		$email = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';
		if ( empty( $email ) ) {
			wp_die( esc_html__( 'Email address is required.', 'opensource-media-connect' ) );
		}

		// If manual credentials are provided, save them
		if ( isset( $_GET['client_id'], $_GET['client_secret'] ) ) {
			$client_id     = sanitize_text_field( wp_unslash( $_GET['client_id'] ) );
			$client_secret = sanitize_text_field( wp_unslash( $_GET['client_secret'] ) );

			if ( empty( $client_id ) || empty( $client_secret ) ) {
				wp_die( esc_html__( 'Both Client ID and Client Secret are required.', 'opensource-media-connect' ) );
			}

			update_option( 'opensource_media_connect_client_id', $client_id );
			update_option( 'opensource_media_connect_client_secret', $client_secret );

			wp_safe_redirect( admin_url( 'options-general.php?page=opensource-media-connect' ) );
			exit;
		}

		$site_name = get_bloginfo( 'name' );
		$site_url  = get_bloginfo( 'url' );

		// Register application with Openverse
		$response = wp_remote_post(
			'https://api.openverse.engineering/v1/auth_tokens/register/',
			array(
				'body' => array(
					'name'        => $site_name,
					'description' => sprintf(
						/* translators: %s: Site URL */
						__( 'WordPress integration for %s', 'opensource-media-connect' ),
						$site_url
					),
					'email'       => $email,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_die(
				sprintf(
					/* translators: %s: Error message */
					esc_html__( 'Error registering application: %s', 'opensource-media-connect' ),
					esc_html( $response->get_error_message() )
				)
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $body['client_id'], $body['client_secret'] ) ) {
			if ( isset( $body['email'] ) && is_array( $body['email'] ) ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'page'  => 'opensource-media-connect',
							'error' => 'email_already_registered',
						),
						admin_url( 'options-general.php' )
					)
				);
				exit;
			}

			wp_die( esc_html__( 'Invalid response from Openverse API.', 'opensource-media-connect' ) );
		}

		// Save credentials
		update_option( 'opensource_media_connect_client_id', $body['client_id'] );
		update_option( 'opensource_media_connect_client_secret', $body['client_secret'] );

		wp_safe_redirect( admin_url( 'options-general.php?page=opensource-media-connect' ) );
		exit;
	}

	/**
	 * Get client credentials token from Openverse API.
	 *
	 * Retrieves an access token using the client credentials flow.
	 * This token is used for making API requests to Openverse.
	 *
	 * @since 1.0.0
	 * @return string|WP_Error Access token on success, WP_Error on failure.
	 */
	public function get_client_credentials_token() {
		$client_id     = get_option( 'opensource_media_connect_client_id' );
		$client_secret = get_option( 'opensource_media_connect_client_secret' );

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			return new WP_Error(
				'missing_credentials',
				__( 'Client ID and Client Secret are required.', 'opensource-media-connect' )
			);
		}

		$response = wp_remote_post(
			'https://api.openverse.engineering/v1/auth_tokens/token/',
			array(
				'body' => array(
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
					'grant_type'    => 'client_credentials',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $body['access_token'] ) ) {
			return new WP_Error(
				'invalid_response',
				__( 'Invalid response from Openverse API.', 'opensource-media-connect' )
			);
		}

		$access_token = $body['access_token'];
		set_transient( 'opensource_media_connect_access_token', $access_token, HOUR_IN_SECONDS );

		return $access_token;
	}

	/**
	 * Get OAuth authorization URL.
	 *
	 * Generates the URL for initiating the OAuth flow with Openverse.
	 *
	 * @since 1.0.0
	 * @return string OAuth authorization URL.
	 */
	private function get_oauth_url() {
		$client_id = get_option( 'opensource_media_connect_client_id' );
		$state     = wp_create_nonce( 'opensource_media_connect_oauth' );

		return add_query_arg(
			array(
				'client_id'     => $client_id,
				'response_type' => 'code',
				'state'         => $state,
				'redirect_uri'  => admin_url( 'admin-post.php?action=opensource_media_connect_oauth' ),
			),
			'https://api.openverse.engineering/v1/auth/oauth/authorize/'
		);
	}

	/**
	 * Display OAuth-related admin notices.
	 *
	 * Shows success or error messages related to the OAuth process.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function display_oauth_notices() {
		$screen = get_current_screen();
		if ( ! $screen || 'settings_page_opensource-media-connect' !== $screen->id ) {
			return;
		}

		if ( isset( $_GET['oauth_success'] ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Successfully connected to Openverse!', 'opensource-media-connect' ); ?></p>
			</div>
			<?php
		}

		if ( isset( $_GET['oauth_error'] ) ) {
			$error = sanitize_text_field( wp_unslash( $_GET['oauth_error'] ) );
			?>
			<div class="notice notice-error is-dismissible">
				<p>
					<?php
					printf(
						/* translators: %s: Error message */
						esc_html__( 'Error connecting to Openverse: %s', 'opensource-media-connect' ),
						esc_html( $error )
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Handle token request.
	 *
	 * Processes the token request when the connect button is clicked.
	 * Gets a new access token using client credentials and saves it.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_token_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'opensource-media-connect' ) );
		}

		check_admin_referer( 'opensource_media_connect_token' );

		$access_token = $this->get_client_credentials_token();

		if ( is_wp_error( $access_token ) ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'        => 'opensource-media-connect',
						'oauth_error' => urlencode( $access_token->get_error_message() ),
					),
					admin_url( 'options-general.php' )
				)
			);
			exit;
		}

		update_option( 'opensource_media_connect_access_token', $access_token );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'          => 'opensource-media-connect',
					'oauth_success' => '1',
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}
}

