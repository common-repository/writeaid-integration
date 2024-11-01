<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://writeaid.com
 * @since      1.0.0
 *
 * @package    WriteAid_Integration
 * @subpackage WriteAid_Integration/admin/partials
 */

?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php

	//settings_errors();

	// Grab all options.
	if ( empty( $this->options ) ) {
		$this->set_options( $this->plugin_name );
	}

	$api_response = json_decode( get_option( $this->plugin_name . '-response' ) );
	$api_key      = $this->options['apikey'];

	?>

	<form method="post" name="<?php echo esc_html( $this->plugin_name ); ?>_options" action="options.php">
		<?php
		settings_fields( $this->plugin_name );
		do_settings_sections( $this->plugin_name );
		?>
		<div id="poststuff">
			<div id="post-body">

				<div class="notice notice-info">
					<p>To obtain your API key, go into your WriteAid account settings by visiting <a
								href="https://my.writeaid.net/login?amember_redirect_url=/settings/sites" target="_blank">my.writeaid.net</a>.
						<br/><br/>
						After adding your website, we'll provide you with an API key that you will enter below.</p>
				</div>

				<div class="postbox">
					<h3 class="hndle"><label for="title">API Integration</label></h3>
					<div class="inside">
						<p><?php esc_attr_e( 'To activate your integration, please enter your API Key generated from WriteAid.', $this->plugin_name ); ?></p>

						<table class="form-table">
							<tbody>
							<tr valign="top">
								<th scope="row">
									<label for="<?php echo $this->plugin_name; ?>-apikey">API Key:</label>
								</th>
								<td>
									<input type="text" id="<?php echo $this->plugin_name; ?>-apikey" name="<?php echo $this->plugin_name; ?>[apikey]"
									       placeholder="Insert your API Key" value="<?php echo $api_key; ?>" class="regular-text code" required/>

									<?php

									if ( isset( $api_response ) ) {
										if ( $api_response->result ) {
											// success!
											?>
											<p class="description">
												<strong style="color: green;"><?php echo esc_html( $api_response->message ); ?></strong>
											</p>
											<?php

										} else {
											// error!
											?>
											<p class="description">
												<strong class="error-message"><?php echo esc_html( $api_response->message ); ?></strong>
											</p>
											<?php
										}
									}

									?>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>
									<?php submit_button( 'Save &amp; Validate', 'primary', 'submit', true ); ?>
								</td>
							</tr>
							</tbody>
						</table>

					</div>
				</div>

			</div>
		</div>

	</form>

</div>
