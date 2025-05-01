<?php
/**
 * Admin Support & help page
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Support & help Page
 *
 * Render the Support & help page
 *
 * @since 1.1.1
 * @return void
 */
function wpbd_render_support_page(){
    ?>
    <div class="wpbd-container">
        <div class="wpbd-wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="postbox-container-2" class="postbox-container">
                        <div class="support_well">
                            <div class="wpbd-support-features">
                                <div class="wpbd-support-features-card">
                                    <div class="wpbd-support-features-img">
                                        <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                                        <img class="wpbd-support-features-icon" src="<?php echo esc_url( WPBD_PLUGIN_URL.'assets/images/document.svg' ); ?>" alt="<?php esc_attr_e( 'Looking for Something?', 'wp-bulk-delete' ); ?>">
                                    </div>
                                    <div class="wpbd-support-features-text">
                                        <h3 class="wpbd-support-features-title"><?php esc_attr_e( 'Looking for Something?', 'wp-bulk-delete' ); ?></h3>
                                        <p><?php esc_attr_e( 'We have documentation of how to delete data in bulk.', 'wp-bulk-delete' ); ?></p>
                                        <a target="_blank" class="button button-primary" href="<?php echo esc_url( 'http://docs.xylusthemes.com/docs/wp-bulk-delete/' ); ?>"><?php esc_attr_e( 'Plugin Documentation', 'wp-bulk-delete' ); ?></a>
                                    </div>
                                </div>
                                <div class="wpbd-support-features-card">
                                    <div class="wpbd-support-features-img">
                                        <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                                        <img class="wpbd-support-features-icon" src="<?php echo esc_url( WPBD_PLUGIN_URL.'assets/images/call-center.svg' ); ?>" alt="<?php esc_attr_e( 'Need Any Assistance?', 'wp-bulk-delete' ); ?>">
                                    </div>
                                    <div class="wpbd-support-features-text">
                                        <h3 class="wpbd-support-features-title"><?php esc_attr_e( 'Need Any Assistance?', 'wp-bulk-delete' ); ?></h3>
                                        <p><?php esc_attr_e( 'Our EXPERT Support Team is always ready to help you out.', 'wp-bulk-delete' ); ?></p>
                                        <a target="_blank" class="button button-primary" href="<?php echo esc_url( 'https://xylusthemes.com/support/' ); ?>"><?php esc_attr_e( 'Contact Support', 'wp-bulk-delete' ); ?></a>
                                    </div>
                                </div>
                                <div class="wpbd-support-features-card">
                                    <div class="wpbd-support-features-img">
                                        <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                                        <img class="wpbd-support-features-icon"  src="<?php echo esc_url( WPBD_PLUGIN_URL.'assets/images/bug.svg' ); ?>" alt="<?php esc_attr_e( 'Found Any Bugs?', 'wp-bulk-delete' ); ?>" />
                                    </div>
                                    <div class="wpbd-support-features-text">
                                        <h3 class="wpbd-support-features-title"><?php esc_attr_e( 'Found Any Bugs?', 'wp-bulk-delete' ); ?></h3>
                                        <p><?php esc_attr_e( 'Report any Bug that you Discovered, and get Instant Solutions.', 'wp-bulk-delete' ); ?></p>
                                        <a target="_blank" class="button button-primary" href="<?php echo esc_url( 'https://github.com/xylusthemes/wp-bulk-delete' ); ?>"><?php esc_attr_e( 'Report to GitHub', 'wp-bulk-delete' ); ?></a>
                                    </div>
                                </div>
                                <div class="wpbd-support-features-card">
                                    <div class="wpbd-support-features-img">
                                        <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                                        <img class="wpbd-support-features-icon" src="<?php echo esc_url( WPBD_PLUGIN_URL.'assets/images/tools.svg' ); ?>" alt="<?php esc_attr_e( 'Require Customization?', 'wp-bulk-delete' ); ?>" />
                                    </div>
                                    <div class="wpbd-support-features-text">
                                        <h3 class="wpbd-support-features-title"><?php esc_attr_e( 'Require Customization?', 'wp-bulk-delete' ); ?></h3>
                                        <p><?php esc_attr_e( 'We would love to hear your Integration and Customization Ideas.', 'wp-bulk-delete' ); ?></p>
                                        <a target="_blank" class="button button-primary" href="<?php echo esc_url( 'https://xylusthemes.com/what-we-do/' ); ?>"><?php esc_attr_e( 'Connect Our Service', 'wp-bulk-delete' ); ?></a>
                                    </div>
                                </div>
                                <div class="wpbd-support-features-card">
                                    <div class="wpbd-support-features-img">
                                        <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                                        <img class="wpbd-support-features-icon" src="<?php echo esc_url( WPBD_PLUGIN_URL.'assets/images/like.svg' ); ?>" alt="<?php esc_attr_e( 'Like The Plugin?', 'wp-bulk-delete' ); ?>" />
                                    </div>
                                    <div class="wpbd-support-features-text">
                                        <h3 class="wpbd-support-features-title"><?php esc_attr_e( 'Like The Plugin?', 'wp-bulk-delete' ); ?></h3>
                                        <p><?php esc_attr_e( 'Your Review is very important to us as it helps us to grow more.', 'wp-bulk-delete' ); ?></p>
                                        <a target="_blank" class="button button-primary" href="<?php echo esc_url( 'https://wordpress.org/support/plugin/wp-bulk-delete/reviews/?rate=5#new-post' ); ?>"><?php esc_attr_e( 'Review Us on WP.org', 'wp-bulk-delete' ); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php 
                            $plugin_list = array();
                            $plugin_list = wpbulkdelete()->api->get_xyuls_themes_plugins();
                        ?>
                        <div class="" style="margin-top: 20px;">
                            <h3 class="setting_bar"><?php esc_html_e( 'Plugins you should try','wp-bulk-delete' ); ?></h3>
                            <div class="wpbd-about-us-plugins">
                                <!-- <div class="wpbd-row"> -->
                                <div class="wpbd-support-features2">
                                
                                    <?php 
                                        if( !empty( $plugin_list ) ){
                                            foreach ($plugin_list as $key => $plugin ) {

                                                $plugin_slug = ucwords( str_replace( '-', ' ', $key ) );
                                                $plugin_name =  $plugin['plugin_name'];
                                                $plugin_description =  $plugin['description'];
                                                if( $key == 'wp-event-aggregator' ){
                                                    $plugin_icon = 'https://ps.w.org/'.$key.'/assets/icon-256x256.jpg';
                                                } else {
                                                    $plugin_icon = 'https://ps.w.org/'.$key.'/assets/icon-256x256.png';
                                                }

                                                // Check if the plugin is installed
                                                $plugin_installed = false;
                                                $plugin_active = false;
                                                include_once(ABSPATH . 'wp-admin/includes/plugin.php');
                                                $all_plugins = get_plugins();
                                                $plugin_path = $key . '/' . $key . '.php';

                                                if (isset($all_plugins[$plugin_path])) {
                                                    $plugin_installed = true;
                                                    $plugin_active = is_plugin_active($plugin_path);
                                                }

                                                // Determine the status text
                                                $status_text = 'Not Installed';
                                                if ($plugin_installed) {
                                                    $status_text = $plugin_active ? 'Active' : 'Installed (Inactive)';
                                                }
                                                
                                                ?>
                                                <div class="wpbd-support-features-card2 wpbd-plugin">
                                                    <div class="wpbd-plugin-main">
                                                        <div>
                                                            <?php
                                                                // translators: %s: Plugin slug used in image alt text.
                                                                $alt_text = sprintf( esc_attr__( '%s Image', 'wp-bulk-delete' ), $plugin_slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                            ?>
                                                            <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                                                            <img alt="<?php echo $alt_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" src="<?php echo esc_url( $plugin_icon ); ?>">
                                                        </div>
                                                        <div>
                                                            <div class="wpbd-main-name"><?php echo esc_attr( $plugin_slug ); ?></div>
                                                            <div><?php echo esc_attr( $plugin_description ); ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="wpbd-plugin-footer">
                                                        <div class="wpbd-footer-status">
                                                            <div class="wpbd-footer-status-label"><?php esc_attr_e( 'Status : ', 'wp-bulk-delete' ); ?></div>
                                                            <div class="wpbd-footer-status wpbd-footer-status-<?php echo esc_attr( strtolower(str_replace(' ', '-', $status_text) ) ); ?>">
                                                                <span <?php echo ( $status_text == 'Active' ) ? 'style="color:green;"' : ''; ?>>
                                                                    <?php echo esc_attr( $status_text ); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="wpbd-footer-action">
                                                            <?php if (!$plugin_installed): ?>
                                                                <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=xylus&tab=search&type=term' ) ); ?>" type="button" class="button button-primary">Install Free Plugin</a>
                                                            <?php elseif (!$plugin_active): ?>
                                                                <?php 
                                                                    $activate_nonce = wp_create_nonce('activate_plugin_' . $plugin_slug); 
                                                                    $activation_url = add_query_arg(array( 'action' => 'activate_plugin', 'plugin_slug' => $plugin_slug, 'nonce' => $activate_nonce, ), admin_url('admin.php?page=delete_all_actions&tab=by_support_help'));
                                                                ?>
                                                                <a href="<?php echo esc_url( admin_url( 'plugins.php?s='. $plugin_name ) ); ?>" class="button button-primary">Activate Plugin</a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                    ?>
                                </div>
                            </div>
                            <div style="clear: both;">
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
    </div>
    <?php
}
