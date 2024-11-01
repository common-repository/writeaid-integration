<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://writeaid.net
 * @since      1.0.0
 *
 * @package    Writeaid_Integration
 * @subpackage Writeaid_Integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Writeaid_Integration
 * @subpackage Writeaid_Integration/admin
 * @author     WriteAid <info@writeaid.net>
 */
class WriteAid_Integration_Admin {

	/**
	 * The plugin options.
	 *
	 * @since         1.0.0
	 * @access        private
	 * @var        array $options The plugin options.
	 */
	private $options;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * @var string
	 */
	protected $api_url = "https://my.writeaid.net/api/wp/";

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->set_options();

	}

	/**
	 *
	 */
	public function add_menu() {
		// add_menu_page('Write Aid', 'Write Aid', 'administrator', __FILE__, 'writeaid_settings' , plugins_url('writeaid_white_ico.png', __FILE__) );
		add_options_page( 'WriteAid', 'WriteAid', 'manage_options', $this->plugin_name, array( $this, 'display_plugin_setup_page' ) );

	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since 1.0.0
	 *
	 * @param $links
	 *
	 * @return array
	 */
	public function add_action_links( $links ) {
		$settings_link = array(
			'<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __( 'Settings', $this->plugin_name ) . '</a>',
		);

		return array_merge( $settings_link, $links );
	}

	/**
	 * Display plugin admin settings page
	 */
	public function display_plugin_setup_page() {

		// If settings were passed back from options.php then use them.
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true' ) { // Input var okay.
			$response = $this->get_integration_response( $this->build_integration_request( 'validate' ) );
		}

		include WRITEAID__PLUGIN_DIR . 'admin/partials/writeaid-integration-admin-display.php';

	}

	/**
	 * @return string
	 */
	private function get_server_address() {
		return $this->api_url . $this->options['apikey'];
	}

	/**
	 * @param null $action
	 *
	 * @return array
	 */
	private function build_integration_request( $action = null ) {
		global $wp;

		$domain = home_url( $wp->request );
		$domain = wp_parse_url( $domain )['host'];

		$request_params = array(
			'domain'     => $domain,
			'auth_token' => 'ez5Y42xmzi8ypZuCR521vngJrQLmzmVhV8cSGKkXn8meI94t8FcVHi48nJz',
			'title'      => get_bloginfo( 'name' ),
			'url'        => get_bloginfo( 'url' ),
		);

		if ( ! empty( $action ) ) {
			$request_params['action'] = $action;
			if ( in_array( $action, array( 'update_categories', 'validate' ), true ) ) {
				$cat_args                     = array(
					'hide_empty' => 0,
					'type'       => 'post',
					'orderby'    => 'name',
					'order'      => 'ASC',
				);
				$request_params['categories'] = get_categories( $cat_args );
				$request_params['authors']    = self::get_users_with_permission();
			}
		}

		$request = array(
			'timeout' => 45,
			'body'    => $request_params,
		);

		return $request;
	}

	/**
	 * Get users w/ permissions
	 */
	private function get_users_with_permission() {

		$roles__in = array();
		foreach ( wp_roles()->roles as $role_slug => $role ) {
			if ( ! empty( $role['capabilities']['publish_posts'] ) ) {
				$roles__in[] = $role_slug;
			}
		}

		$users = array();
		if ( count( $roles__in ) > 0 ) {
			$users = get_users(
				array(
					'roles__in' => $roles__in,
					'fields'    => array( 'ID', 'user_email', 'user_nicename', 'display_name' ),
				)
			);
		}

		$user_list = array();
		foreach ( $users as $user ) {
			if ( user_can( $user->ID, 'publish_posts' ) ) {
				$user_list[] = $user;
			}
		}

		return $user_list;

	}

	/**
	 * @param $request
	 *
	 * @return array|mixed
	 */
	private function get_integration_response( $request ) {

		$response = wp_remote_post( $this->get_server_address(), $request );

		if ( is_wp_error( $response ) ) {

			// @todo log/display error
			$error_message = $response->get_error_message();
			$data          = array(
				'result'  => false,
				'message' => $error_message,
			);
			update_option( $this->plugin_name . '-response', $data, false );

		} else {

			$data = wp_remote_retrieve_body( $response );
			update_option( $this->plugin_name . '-response', $data, false );
		}

		return json_decode( $data );
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Writeaid_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Writeaid_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/writeaid-integration-admin.css', [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Writeaid_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Writeaid_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/writeaid-integration-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Registers plugin settings
	 *
	 * @since        1.0.0
	 * @return        void
	 */
	public function register_settings() {
		register_setting( $this->plugin_name, $this->plugin_name, array( $this, 'settings_sanitize' ) );
		register_setting( $this->plugin_name, $this->plugin_name . '-response' );
	}


	/**
	 * Sets the class variable $options
	 */
	private function set_options() {
		$this->options = get_option( $this->plugin_name );
	}


	/**
	 * Settings - Validates saved options
	 *
	 * @since        1.0.0
	 *
	 * @param        array $input array of submitted plugin options.
	 *
	 * @return        array                        array of validated plugin options
	 */
	public function settings_sanitize( $input ) {

		// Initialize the new array that will hold the sanitize values.
		$new_input = array();

		if ( isset( $input ) ) {
			// Loop through the input and sanitize each of the values.
			foreach ( $input as $key => $val ) {

				if ( 'post-type' === $key ) { // dont sanitize array.
					$new_input[ $key ] = $val;
				} else {
					$new_input[ $key ] = sanitize_text_field( $val );
				}
			}
		}

		return $new_input;

	}

	/**
	 * Display license specific admin notices, namely:
	 *
	 * - License for the product isn't activated
	 * - External requests are blocked through WP_HTTP_BLOCK_EXTERNAL
	 */
	public function display_admin_notices() {


		// show API error.
		if ( empty( $this->options['apikey'] ) ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p class="description">
					<strong>To complete integration with WriteAid,
						<a href="<?php echo esc_html( admin_url( 'options-general.php?page=' . $this->plugin_name ) ); ?>">click here to enter your API key</a>.
					</strong>
				</p>
			</div>
			<?php

		} /*else {

			$api_response = json_decode( get_option( $this->plugin_name . '-response' ) );

			// show API error
			if ( isset( $api_response ) && ( $api_response->result == false ) ) {

				?>
				<div class="notice notice-error is-dismissible">
					<p class="description">
						<strong class="error-message"><?php echo esc_html( $api_response->message );
							?>
							<br/><a href="<?php echo admin_url( 'options-general.php?page=' . $this->plugin_name );
							?>">Click here to re-verify.</a></strong>
					</p>
				</div>
				<?php
			}

		}*/

	}

	/**
	 * Call function during create new category/taxonomy
	 *
	 * @param null $category_id Category ID.
	 */
	public function update_categories( $category_id = null ) {

		$api_response = json_decode( get_option( $this->plugin_name . '-response' ) );

		// only send if validated.
		if ( $api_response->result ) {

			$request  = $this->build_integration_request( 'update_categories' );
			$response = wp_remote_post( $this->get_server_address(), $request );

		}
	}

}
