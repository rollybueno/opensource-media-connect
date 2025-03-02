<?php
/**
 * Handles the admin functionality of the Openverse Connect plugin.
 *
 * @package Openverse_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class to handle all admin functionality.
 */
class Openverse_Connect_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_openverse_connect_oauth', array( $this, 'handle_oauth_redirect' ) );
		add_action( 'admin_post_openverse_register_app', array( $this, 'handle_app_registration' ) );
		add_action( 'admin_notices', array( $this, 'display_oauth_notices' ) );
	}

	/**
	 * Add menu item to WordPress admin.
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Openverse Connect Settings', 'openverse-connect' ),
			__( 'Openverse Connect', 'openverse-connect' ),
			'manage_options',
			'openverse-connect',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting(
			'openverse_connect_settings',
			'openverse_connect_client_id',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'openverse_connect_settings',
			'openverse_connect_client_secret',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'openverse_connect_settings',
			'openverse_connect_access_token',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		$client_id       = get_option( 'openverse_connect_client_id' );
		$client_secret   = get_option( 'openverse_connect_client_secret' );
		$access_token    = get_option( 'openverse_connect_access_token' );
		$is_connected    = ! empty( $access_token );
		$has_credentials = ! empty( $client_id ) && ! empty( $client_secret );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php if ( ! $is_connected ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'You need to connect to Openverse to use this plugin.', 'openverse-connect' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="openverse-connect-oauth">
				<h2><?php esc_html_e( 'Connection Status', 'openverse-connect' ); ?></h2>
				<?php if ( $is_connected ) : ?>
					<p class="openverse-connect-status connected">
						<span class="dashicons dashicons-yes-alt"></span>
						<?php esc_html_e( 'Connected to Openverse', 'openverse-connect' ); ?>
					</p>
					<p>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=openverse_connect_oauth&disconnect=1' ), 'openverse_connect_disconnect' ) ); ?>" class="button">
							<?php esc_html_e( 'Disconnect', 'openverse-connect' ); ?>
						</a>
					</p>
				<?php elseif ( $has_credentials ) : ?>
					<p class="openverse-connect-status not-connected">
						<span class="dashicons dashicons-warning"></span>
						<?php esc_html_e( 'Not connected to Openverse', 'openverse-connect' ); ?>
					</p>
					<p>
						<a href="<?php echo esc_url( $this->get_oauth_url() ); ?>" class="button button-primary">
							<?php esc_html_e( 'Connect to Openverse', 'openverse-connect' ); ?>
						</a>
					</p>
				<?php else : ?>
					<p class="openverse-connect-status not-registered">
						<span class="dashicons dashicons-admin-plugins"></span>
						<?php esc_html_e( 'Register your WordPress site with Openverse', 'openverse-connect' ); ?>
					</p>
					<p class="description">
						<?php esc_html_e( 'This will create a new application registration with Openverse automatically. You can only have one application registration per email address.', 'openverse-connect' ); ?>
					</p>
					<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="openverse_register_app">
						<?php wp_nonce_field( 'openverse_register_app' ); ?>
						
						<p class="description">
							<input type="email" 
								name="email" 
								id="openverse-connect-email" 
								value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" 
								placeholder="<?php esc_attr_e( 'Your email address', 'openverse-connect' ); ?>"
								required
								class="regular-text"
							>
						</p>
						<p>
							<button type="submit" class="button button-primary">
								<?php esc_html_e( 'Register with Openverse', 'openverse-connect' ); ?>
							</button>
						</p>
					</form>
				<?php endif; ?>

				<?php if ( $has_credentials ) : ?>
					<hr>
					<h3><?php esc_html_e( 'Application Credentials', 'openverse-connect' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Client ID', 'openverse-connect' ); ?></th>
							<td><code><?php echo esc_html( $client_id ); ?></code></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Client Secret', 'openverse-connect' ); ?></th>
							<td>
								<code>••••••••</code>
								<button type="button" class="button-link" onclick="this.previousElementSibling.textContent = '<?php echo esc_js( $client_secret ); ?>'">
									<?php esc_html_e( 'Show', 'openverse-connect' ); ?>
								</button>
							</td>
						</tr>
					</table>
				<?php endif; ?>
			</div>
		</div>

		<style>
			.openverse-connect-oauth {
				margin-top: 20px;
				padding: 20px;
				background: #fff;
				border: 1px solid #ccd0d4;
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
			}
			.openverse-connect-status {
				display: flex;
				align-items: center;
				gap: 8px;
				margin: 15px 0;
				font-size: 14px;
			}
			.openverse-connect-status .dashicons {
				font-size: 20px;
				width: 20px;
				height: 20px;
			}
			.openverse-connect-status.connected .dashicons {
				color: #46b450;
			}
			.openverse-connect-status.not-connected .dashicons {
				color: #dc3232;
			}
			.openverse-connect-status.not-registered .dashicons {
				color: #007cba;
			}
			.description {
				color: #646970;
				font-style: italic;
				margin: 5px 0 15px;
			}
		</style>
		<?php
	}

	/**
	 * Handle application registration with Openverse.
	 */
	public function handle_app_registration() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'openverse-connect' ) );
		}

		check_admin_referer( 'openverse_register_app' );

		if ( ! isset( $_GET['email'] ) ) {
			wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&error=missing_email' ) );
			exit;
		}

		$site_name = get_bloginfo( 'name' );
		$site_url  = get_site_url();
		$email     = sanitize_email( wp_unslash( $_GET['email'] ) );

		if ( ! is_email( $email ) ) {
			wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&error=invalid_email' ) );
			exit;
		}

		$response = wp_remote_post(
			'https://api.openverse.engineering/v1/auth_tokens/register/',
			array(
				'body' => array(
					'name'         => sprintf( '%s WordPress Plugin', $site_name ),
					'description'  => sprintf(
						'Openverse integration for WordPress site: %s (%s)',
						$site_name,
						$site_url
					),
					'email'        => $email,
					'redirect_uri' => admin_url( 'admin-post.php?action=openverse_connect_oauth' ),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&error=registration_failed' ) );
			exit;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['client_id'] ) || empty( $body['client_secret'] ) ) {
			if( isset( $body['name'][0] ) ) {
				if( $body['name'][0] === 'o auth2 registration with this name already exists.' ) { 
					wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&error=email_already_registered' ) );
					exit;
				}
			}
			wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&error=invalid_registration_response' ) );
			exit;
		}

		update_option( 'openverse_connect_client_id', $body['client_id'] );
		update_option( 'openverse_connect_client_secret', $body['client_secret'] );

		// After successful registration, get a client credentials token.
		$token = $this->get_client_credentials_token();
		if ( is_wp_error( $token ) ) {
			wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&error=token_error' ) );
			exit;
		}

		update_option( 'openverse_connect_access_token', $token );
		wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&registered=1&connected=1' ) );
		exit;
	}

	/**
	 * Get access token using client credentials grant.
	 *
	 * @return string|WP_Error Access token or error.
	 */
	private function get_client_credentials_token() {
		$client_id     = get_option( 'openverse_connect_client_id' );
		$client_secret = get_option( 'openverse_connect_client_secret' );

		$response = wp_remote_post(
			'https://api.openverse.engineering/v1/auth/oauth/token',
			array(
				'body' => array(
					'grant_type'    => 'client_credentials',
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['access_token'] ) ) {
			return new WP_Error( 'invalid_token', __( 'Invalid response from Openverse', 'openverse-connect' ) );
		}

		return $body['access_token'];
	}

	/**
	 * Get the OAuth URL for connecting to Openverse.
	 *
	 * @return string The OAuth URL.
	 */
	private function get_oauth_url() {
		$client_id    = get_option( 'openverse_connect_client_id' );
		$redirect_uri = admin_url( 'admin-post.php?action=openverse_connect_oauth' );
		$state        = wp_create_nonce( 'openverse_connect_oauth' );

		return add_query_arg(
			array(
				'client_id'     => $client_id,
				'response_type' => 'code',
				'state'         => $state,
				'redirect_uri'  => urlencode( $redirect_uri ),
			),
			'https://api.openverse.engineering/v1/auth/oauth/authorize'
		);
	}

	/**
	 * Handle the OAuth redirect from Openverse.
	 */
	public function handle_oauth_redirect() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'openverse-connect' ) );
		}

		// Handle disconnect request.
		if ( isset( $_GET['disconnect'] ) && check_admin_referer( 'openverse_connect_disconnect' ) ) {
			delete_option( 'openverse_connect_access_token' );
			wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&disconnected=1' ) );
			exit;
		}

		// Handle OAuth response.
		if ( ! isset( $_GET['code'] ) || ! isset( $_GET['state'] ) ) {
			wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&error=invalid_response' ) );
			exit;
		}

		$state = sanitize_text_field( wp_unslash( $_GET['state'] ) );
		if ( ! wp_verify_nonce( $state, 'openverse_connect_oauth' ) ) {
			wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&error=invalid_state' ) );
			exit;
		}

		$code  = sanitize_text_field( wp_unslash( $_GET['code'] ) );
		$token = $this->get_access_token( $code );
		if ( is_wp_error( $token ) ) {
			wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&error=token_error' ) );
			exit;
		}

		update_option( 'openverse_connect_access_token', $token );
		wp_safe_redirect( admin_url( 'options-general.php?page=openverse-connect&connected=1' ) );
		exit;
	}

	/**
	 * Get access token from authorization code.
	 *
	 * @param string $code Authorization code.
	 * @return string|WP_Error Access token or error.
	 */
	private function get_access_token( $code ) {
		$client_id     = get_option( 'openverse_connect_client_id' );
		$client_secret = get_option( 'openverse_connect_client_secret' );
		$redirect_uri  = admin_url( 'admin-post.php?action=openverse_connect_oauth' );

		$response = wp_remote_post(
			'https://api.openverse.engineering/v1/auth/oauth/token',
			array(
				'body' => array(
					'grant_type'    => 'authorization_code',
					'code'          => $code,
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
					'redirect_uri'  => $redirect_uri,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['access_token'] ) ) {
			return new WP_Error( 'invalid_token', __( 'Invalid response from Openverse', 'openverse-connect' ) );
		}

		return $body['access_token'];
	}

	/**
	 * Display OAuth-related admin notices.
	 */
	public function display_oauth_notices() {
		$screen = get_current_screen();
		if ( 'settings_page_openverse-connect' !== $screen->id ) {
			return;
		}

		if ( isset( $_GET['registered'] ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Successfully registered with Openverse! You can now connect your site.', 'openverse-connect' ); ?></p>
			</div>
			<?php
		}

		if ( isset( $_GET['connected'] ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Successfully connected to Openverse!', 'openverse-connect' ); ?></p>
			</div>
			<?php
		}

		if ( isset( $_GET['disconnected'] ) ) {
			?>
			<div class="notice notice-info is-dismissible">
				<p><?php esc_html_e( 'Successfully disconnected from Openverse.', 'openverse-connect' ); ?></p>
			</div>
			<?php
		}

		if ( isset( $_GET['error'] ) ) {
			$error_message = '';
			switch ( $_GET['error'] ) {
				case 'missing_email':
					$error_message = __( 'Email address is required.', 'openverse-connect' );
					break;
				case 'invalid_email':
					$error_message = __( 'Please enter a valid email address.', 'openverse-connect' );
					break;
				case 'registration_failed':
					$error_message = __( 'Failed to register with Openverse. Please try again.', 'openverse-connect' );
					break;
				case 'invalid_registration_response':
					$error_message = __( 'Invalid response from Openverse registration.', 'openverse-connect' );
					break;
				case 'invalid_response':
					$error_message = __( 'Invalid response from Openverse.', 'openverse-connect' );
					break;
				case 'invalid_state':
					$error_message = __( 'Invalid state parameter.', 'openverse-connect' );
					break;
				case 'token_error':
					$error_message = __( 'Error obtaining access token.', 'openverse-connect' );
					break;
				case 'email_already_registered':
					$error_message = __( 'An application registration with this email address already exists.', 'openverse-connect' );
					break;
			}
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo esc_html( $error_message ); ?></p>
			</div>
			<?php
		}
	}
}
