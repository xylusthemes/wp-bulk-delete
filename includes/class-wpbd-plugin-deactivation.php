<?php
/**
 * Plugin Deactivation Class
 * Collects Feedback from user about deactivation
 *
 * @package     Xylus_Plugin_Deactivation
 * @copyright   Copyright (c) 2021, Xylus Themes
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @package     WP_Bulk_Delete
 * @subpackage  WP_Bulk_Delete/admin
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
if ( ! class_exists( 'WPBD_Plugin_Deactivation' ) ) {
    class WPBD_Plugin_Deactivation {

        private $prefix = 'wpbd_';
        private $slug = 'wp-bulk-delete';
        private $plugin_version = '1.0.0';
        private $api_url = 'https://api.xylusthemes.com/api/v1/';

        /**
         * Initialize the class and set its properties.
         *
         * @since    1.0.0
         */
        public function __construct() {
			if ( defined( 'WPBD_VERSION' ) ) {
				$this->plugin_version = WPBD_VERSION;
			}

            add_action( 'admin_footer', array( $this, 'deactivation_feedback_form') );
            add_action( 'wp_ajax_'.$this->prefix.'plugin_deactivation_feedback', array( $this, 'submit_plugin_deactivation_feedback') );
		}

		public function get_deactivation_reasons() {
			return array(
				'confusing' => esc_attr__('I couldn\'t understand how to make it work', 'wp-bulk-delete' ),
				'better_plugin' => esc_attr__('I found a better plugin', 'wp-bulk-delete' ),
				'feature_request' => esc_attr__('The plugin is great, but I need a specific feature that you don\'t support', 'wp-bulk-delete' ),
				'buggy' => esc_attr__('Plugin has bugs and it\'s not working', 'wp-bulk-delete' ),
				'wrong_plugin' => esc_attr__('It\'s not what I was looking for', 'wp-bulk-delete' ),
				'not_working' => esc_attr__('Plugin didn\'t work as expected', 'wp-bulk-delete' ),
				'temporary' => esc_attr__('It\'s temporary deactivation, for debugging an issue', 'wp-bulk-delete' ),
				'other' => esc_attr__('Other reasons', 'wp-bulk-delete' ),
			);
        }

        function generate_ticket(){
            $url = $this->api_url.'generateTicket';
            $user = wp_get_current_user();
            $headers = array( 'Content-Type' => 'application/json' );
            $args = array(
                'method' =>'POST',
                'body'    => json_encode(array('customer_email' => $user->user_email )),
                'blocking' => true,
                'headers' => $headers,
            );

		    $response = wp_remote_post( $url, $args );
            if ( is_wp_error( $response ) ) {
                return false;
            }

		    return wp_remote_retrieve_body($response);
        }

        function submit_plugin_deactivation_feedback(){
            if ( isset( $_REQUEST['nonce'] ) && !wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), $this->prefix.'plugin_deactivation_feedback')) {
                exit("nonce verification failed");
            }

            $url = $this->api_url.'feedback';
            $credentials = $this->generate_ticket();
            if(!$credentials){
                die();
			}

			$credentials = json_decode($credentials);
            $user = wp_get_current_user();
            $timestamp = $credentials->timestamp;
            $client_id = $credentials->client_id;
            $client_secret = $credentials->client_secret;
            $customer_email = $user->user_email;
            if(empty($customer_name)){
                $customer_name = $user->user_firstname. ' '.$user->user_lastname;
            }
            $deactivation_reason = isset( $_REQUEST['reason'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['reason'] ) ) : '';
            $deactivation_reason_message = $this->get_deactivation_reasons()[$deactivation_reason];
            $customer_query = isset( $_REQUEST['customerQuery'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['customerQuery'] ) ) : '';

            $data = array(
                "type" => "plugin_deactivation",
                "site_url" => get_site_url(),
                "customer_name" => $customer_name,
                "customer_email" => $customer_email,
                "plugin" => $this->slug,
                "plugin_name" => esc_attr__('WP Bulk Delete', 'wp-bulk-delete' ),
                "plugin_version" => $this->plugin_version,
                "plugin_version" => $this->plugin_version,
                "deactivation_reason" => $deactivation_reason,
                "deactivation_reason_message" => $deactivation_reason_message,
                "query" => $customer_query
            );

            $plain_string = $customer_email .  $timestamp . $client_secret;
            $sha512_hash  = hash("sha512", $plain_string);

            $body = json_encode($data);
            $headers = array( 'Content-Type' => 'application/json');
            $headers['Client-Id'] = $client_id;
            $headers['Timestamp'] = $timestamp;
            $headers['Authorization'] = $sha512_hash;
            $args = array(
                'method' =>'POST',
                'body' => $body,
                'blocking' => true,
                'headers' => $headers
            );
			$response = wp_remote_post( $url, $args );
            if ( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                echo esc_attr( "Something went wrong: $error_message" );
                exit();
            }

            die(true);
        }

        public function deactivation_feedback_form() {
            $wp_screen = get_current_screen();
            $page_id = $wp_screen->id;

            // Load only for WP admin plugins page
            if($page_id !== 'plugins'){
                return;
            }
            wp_enqueue_style( 'wp-jquery-ui-dialog');
            wp_enqueue_script( 'jquery-ui-dialog');

            $deactivate_reasons = $this->get_deactivation_reasons();
        	?>

            <script>
                jQuery(document).ready(function() {
					var dataReason = jQuery('input:radio[name="<?php echo esc_attr( $this->prefix  ); ?>deactivatation_reason_radio"]').val();
                    jQuery('a[aria-label="Deactivate WP Bulk Delete"]').on('click', function (e) {
                        e.preventDefault();
                        var pluginDeactivateURL = jQuery(this).attr('href');
                        jQuery('#<?php echo esc_attr( $this->prefix ); ?>-deactivate-dialog' ).dialog({
                            'dialogClass'   : '<?php echo esc_attr( $this->prefix ) . "-deactivate-dialog"; ?>',
                            'modal'         : true,
                            'closeOnEscape' : true,
                            width: 600,
                            'buttons'       : [
                                {
                                    text: "Submit & Deactivate",
                                    class: 'button button-primary <?php echo esc_attr( $this->prefix ) . "deactivate_button"; ?>',
                                    click: function() {
										var that = this;
										var dataQuery = jQuery('#<?php echo esc_attr( $this->prefix ); ?>customer_query').val();
										if(dataReason == 'other' && !dataQuery){
											jQuery('#<?php echo esc_attr( $this->prefix ); ?>customer_query').focus();
											return false;
										}
										jQuery('#<?php echo esc_attr( $this->prefix ); ?>deactivatation_form').hide();
										jQuery('.<?php echo esc_attr( $this->prefix ); ?>deactivatation_loading').show();
                                        jQuery('button.<?php echo esc_attr( $this->prefix ); ?>deactivate_button').prop('disabled', true);
                                        jQuery.ajax({
                                            type : "post",
                                            dataType : "json",
                                            url : "<?php echo admin_url('admin-ajax.php?action='.esc_attr( $this->prefix ).'plugin_deactivation_feedback&nonce='.wp_create_nonce(esc_attr( $this->prefix ).'plugin_deactivation_feedback') ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>",
                                            data : {
                                                action: "<?php echo esc_attr( $this->prefix ); ?>plugin_deactivation_feedback",
                                                reason: dataReason,
                                                customerQuery: dataQuery
                                            },
                                        }).always( function(){
											jQuery( that ).dialog( "close" );
											window.location.href=pluginDeactivateURL;
										});
                                    }
                                },
                                {
                                    text: "Skip & Deactivate",
                                    class: 'button',
                                    click: function() {
                                        jQuery( this ).dialog( "close" );
                                        window.location.href=pluginDeactivateURL;
                                    }
                                }
                            ]
                        });
                    });

                    jQuery('input:radio[name="<?php echo esc_attr( $this->prefix ); ?>deactivatation_reason_radio"]').click(function () {
                        var reason = jQuery(this).val();
						dataReason = jQuery(this).val();
                        var customerQuery = jQuery('#<?php echo esc_attr( $this->prefix ); ?>customer_query');
                        customerQuery.removeAttr('required');
                        if (reason === "confusing") {
                            customerQuery.attr("placeholder", "<?php esc_html_e('Finding it confusing? let us know so that we can improve the interface', 'wp-bulk-delete' ); ?>");

                        } else if (reason === "other") {
                            customerQuery.attr("placeholder", "<?php esc_html_e('Can you let us know the reason for deactivation (Required)', 'wp-bulk-delete' ); ?>");
                            customerQuery.prop('required', true);

                        } else if (reason === "buggy" || reason === 'not_working') {
                            customerQuery.attr("placeholder", "<?php esc_html_e('Can you please let us know about the bug/issue in detail?', 'wp-bulk-delete' ); ?>");

                        } else if (reason === "better_plugin") {
                            customerQuery.attr("placeholder", "<?php esc_html_e('Can you please let us know which plugin you found helpful', 'wp-bulk-delete' ); ?>");

                        } else if (reason === "feature_request") {
                            customerQuery.attr("placeholder", "<?php esc_html_e('Can you please let us know more about the feature you want', 'wp-bulk-delete' ); ?>");

                        }  else if (reason === "wrong_plugins") {
                            customerQuery.attr("placeholder", "<?php esc_html_e('Can you please let us know more about your requirement', 'wp-bulk-delete' ); ?>");

                        } else if (reason === "temporary") {
                            customerQuery.attr("placeholder", "<?php esc_html_e('Write your query here', 'wp-bulk-delete'); ?>");
                        }
                    });
                });
            </script>
			<style>
			<?php echo '.'.esc_attr( $this->prefix ); ?>-deactivate-dialog .ui-dialog-titlebar{
				display: none;
			}
            .ui-widget.<?php echo esc_attr( $this->prefix ); ?>-deactivate-dialog{
                font-family: inherit;
                font-size: 14px;
                font-weight: inherit;
                line-height: inherit;
            }
            .ui-widget.<?php echo esc_attr( $this->prefix ); ?>-deactivate-dialog textarea{
                font-family: inherit;
                font-size: 14px;
                width: 100%;
            }
            <?php echo '#'.esc_attr( $this->prefix ); ?>-deactivate-dialog {
                display : none;
            }
			</style>
            <div id="<?php echo esc_attr( $this->prefix ); ?>-deactivate-dialog">
                <div class="ui-dialog-headerbar"  >
                    <div>
                        <h2 style="margin:5px 0 15px 0;"><?php esc_html_e('Quick Feedback', 'wp-bulk-delete'); ?></h2>
                    </div>
                </div>               
                <div style="border-top: 1px solid #dcdcde;"></div>
                <h3 style="font-size: 16px;" ><?php esc_html_e('Could you please share why you are deactivating WP Bulk Delete plugin ?', 'wp-bulk-delete'); ?></h3>
                <form method="post" action="" id="<?php echo esc_attr( $this->prefix ); ?>deactivatation_form">
                    <div>
                    <?php
                        foreach ( $deactivate_reasons as $key => $deactivate_reason ) {
                            ?>
                            <div class="radio" style="padding:1px;margin-left:2%">
                                <label for="<?php echo esc_attr( $key ); ?>">
                                    <input type="radio" name="<?php echo esc_attr( $this->prefix ); ?>deactivatation_reason_radio" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>" required <?php if( $key === 'confusing') { echo "checked"; } ?>> <?php echo esc_attr( $deactivate_reason ); ?>
                                </label>
                            </div>
                        <?php } ?>
                        <br>
                        <textarea id="<?php echo esc_attr( $this->prefix ); ?>customer_query" name="<?php echo esc_attr( $this->prefix ); ?>customer_query" rows="4" placeholder="<?php esc_html_e('Write your query here', 'wp-bulk-delete'); ?>"></textarea>
                    </div>
                    <div style="text-align: center;">
                        <p style="font-size: 12px;margin: 2px 0 -10px 0;">
                            <?php echo esc_attr__( '* By submitting this form, you will also be sending us your email address &amp; website URL.', 'wp-bulk-delete' ); ?>
                        </p>
                    </div>
                </form>
				<div class="<?php echo esc_attr( $this->prefix ); ?>deactivatation_loading" style="width: 100%;text-align: center; display:none;">
                    <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
					<img src="<?php echo esc_url( admin_url('images/spinner.gif') ); ?>" />
				</div>
            </div>
            <?php
        }
    }
}

new WPBD_Plugin_Deactivation();