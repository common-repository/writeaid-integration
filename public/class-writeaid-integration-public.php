<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://writeaid.net
 * @since      1.0.0
 *
 * @package    Writeaid_Integration
 * @subpackage Writeaid_Integration/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Writeaid_Integration
 * @subpackage Writeaid_Integration/public
 * @author     WriteAid <info@writeaid.net>
 */
class WriteAid_Integration_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->set_options();
	}

	/**
	 * Create custom API endpoint
	 *
	 * @since    1.0.0
	 */
	public function writeaid_api_endpoint() {

		$api_key = $this->options['apikey'];

		register_rest_route( 'writeaid_api', "/writeaid/{$api_key}", array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'writeaid_api_callback' ),
			'args'     => array(
				'api_key',
			),
		) );

	}

	/**
	 * @since    1.0.0
	 *
	 * @param WP_REST_Request $request_data
	 */
	function writeaid_api_callback( WP_REST_Request $request_data ) {

		// don't sanitize, strips html
		//$request_data = $this->settings_sanitize($request_data->get_params());

		$writeaid_apikey = $this->options['apikey'];
		$api_key         = $request_data['api_key'];

		if ( empty( $api_key ) ) {
			die( wp_json_encode( array( 'error' => 'Missing API Key' ) ) );
		}

		if ( $writeaid_apikey !== $api_key ) {
			die( wp_json_encode( array( 'error' => 'Invalid API Key' ) ) );
		}

		unset( $request_data['api_key'] );

		if ( true === $request_data['validate'] ) {
			die( wp_json_encode( array( 'success' => true ) ) );
		}

		switch ( $request_data['action'] ) {
			default:
				die( wp_json_encode( array( 'error' => 'API Call Not Found' ) ) );

			case 'validate':
				$response = array(
					'success'    => true,
					'title'      => get_bloginfo( 'name' ),
					'url'        => get_bloginfo( 'url' ),
					'categories' => get_categories( array(
						'hide_empty' => 0,
						'type'       => 'post',
						'orderby'    => 'name',
						'order'      => 'ASC',
					) ),
					'authors'    => self::get_users_with_permission(),
				);
				die( wp_json_encode( $response ) );

			case 'new':
				$title   = sanitize_text_field( $request_data['title'] );
				$content = ( $request_data['content'] );
				$status  = ( 'publish' === $request_data['status'] ? 'publish' : 'draft' );
				$author  = ( ! empty( $request_data['post_author'] ) ? $request_data['post_author'] : 1 );

				if ( '' !== $title && '' !== $content ) {

					$post_args = array(
						'post_title'   => wp_strip_all_tags( $title ),
						'post_content' => $content,
						'post_status'  => $status,
						'post_author'  => $author,
						'filter'       => true,
					);

					// set post category.
					if ( ! empty( $request_data['category_id'] ) ) {
						$post_args['post_category'] = $request_data['category_id'];
					}

					$success['post_id'] = wp_insert_post( $post_args );

					if ( empty( $success['post_id'] ) || ! is_numeric( $success['post_id'] ) ) {
						die( wp_json_encode(
							[
								'error'       => 'Error creating Wordpress post',
								'wp_response' => $success['post_id'],
							]
						) );
					}

					if ( ! empty( $request_data['featured_img'] ) ) {
						$feat_img = self::generate_featured_image( $request_data['featured_img'], $success['post_id'], $title );
					}

					$success['post_url'] = get_permalink( $success['post_id'] );
					die( wp_json_encode( $success ) );

				} else {
					die( wp_json_encode( array( 'error' => 'Missing Content' ) ) );
				}
				break;
		}


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
					'fields'    => array( 'ID', 'user_email', 'user_nicename', 'display_name', 'user_status' ),
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
	 * Downloads an image from the specified URL and attaches it to a post as a post thumbnail.
	 *
	 * @param string $file    The URL of the image to download.
	 * @param int    $post_id The post ID the post thumbnail is to be associated with.
	 * @param string $desc    Optional. Description of the image.
	 *
	 * @return string|WP_Error Attachment ID, WP_Error object otherwise.
	 */
	public function generate_featured_image( $file, $post_id, $desc ) {

		require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
		require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
		require_once( ABSPATH . "wp-admin" . '/includes/media.php' );

		if ( ! function_exists( 'wp_check_filetype_and_ext' ) ) {
			require_once ABSPATH . WPINC . '/functions.php';
		}



		// Set variables for storage, fix file filename for query strings.
		/*preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
		if ( ! $matches ) {
			return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL' ) );
		}*/

		$file_array         = array();
		$file_array['name'] = basename( $file );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $file );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) ) {
			return $file_array['tmp_name'];
		}

		// Do the validation and storage stuff.
		$id = media_handle_sideload( $file_array, $post_id, $desc );

		// If error storing permanently, unlink.
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );

			return $id;
		}

		return set_post_thumbnail( $post_id, $id );

	}

	/**
	 * Sets the class variable $options
	 */
	private function set_options() {
		$this->options = get_option( $this->plugin_name );
	} // set_options()


	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/writeaid-integration-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/writeaid-integration-public.js', array( 'jquery' ), $this->version, false );

	}


	/**
	 * Settings - Validates saved options
	 *
	 * @param array $input input array.
	 *
	 * @return array
	 */
	public function settings_sanitize( $input ) {

		// Initialize the new array that will hold the sanitize values.
		$new_input = array();

		if ( isset( $input ) ) {

			// Loop through the input and sanitize each of the values.
			foreach ( $input as $key => $val ) {

				if ( 'post-type' === $key ) {
					$new_input[ $key ] = $val;
				} else {
					$new_input[ $key ] = sanitize_text_field( $val );
				}
			}
		}

		return $new_input;

	}

}
