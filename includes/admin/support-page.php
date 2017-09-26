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
    $open_source_support_url = 'https://wordpress.org/support/plugin/wp-bulk-delete/';
    $support_url = 'https://xylusthemes.com/support/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin';

    $review_url = 'https://wordpress.org/support/plugin/wp-bulk-delete/reviews/?rate=5#new-post';
    $facebook_url = 'https://www.facebook.com/xylusinfo/';
    $twitter_url = 'https://twitter.com/XylusThemes/';
    ?>
    <div class="wrap">
        <h2><?php esc_html_e('Support & Help','wp-bulk-delete'); ?></h2>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">

                <div id="postbox-container-1" class="postbox-container">
                    <?php do_action('wpbd_admin_sidebar'); ?>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    
                    <div class="wpbd_well">
                        <h3><?php esc_attr_e( 'Getting Support', 'wp-bulk-delete' ); ?></h3>
                        <p><?php _e( 'Thanks you for using Import Facebook Events, We are sincerely appreciate your support and we’re excited to see you using our plugins.','wp-bulk-delete' ); ?> </p>
                        <p><?php _e( 'Our support team is always around to help you.','wp-bulk-delete' ); ?></p>
                            
                        <p><strong><?php _e( 'Looking for free support?','wp-bulk-delete' ); ?></strong></p>
                        <a class="button button-secondary" href="<?php echo $open_source_support_url; ?>" target="_blank" >
                            <?php _e( 'Open-source forum on WordPress.org','wp-bulk-delete' ); ?>
                        </a>

                        <p><strong><?php _e( 'Looking for more immediate support?','wp-bulk-delete' ); ?></strong></p>
                        <p><?php _e( 'We offer premium support on our website with the purchase of our premium plugins.','wp-bulk-delete' ); ?>
                        </p>
                        
                        <a class="button button-primary" href="<?php echo $support_url; ?>" target="_blank" >
                            <?php _e( 'Contact us directly (Premium Support)','wp-bulk-delete' ); ?>
                        </a>

                        <p><strong><?php _e( 'Enjoying Import Facebook Events or have feedback?','wp-bulk-delete' ); ?></strong></p>
                        <a class="button button-secondary" href="<?php echo $review_url; ?>" target="_blank" >Leave us a review</a> 
                        <a class="button button-secondary" href="<?php echo $twitter_url; ?>" target="_blank" >Follow us on Twitter</a> 
                        <a class="button button-secondary" href="<?php echo $facebook_url; ?>" target="_blank" >Like us on Facebook</a>
                    </div>

                    <?php 
                    $plugins = array();
                    $plugin_list = wpbulkdelete()->api->get_xyuls_themes_plugins();
                    if( !empty( $plugin_list ) ){
                        foreach ($plugin_list as $key => $value) {
                            $plugins[] = wpbulkdelete()->api->get_wporg_plugin( $key );
                        }
                    }
                    ?>
                    <div class="" style="margin-top: 20px;">
                        <h3 class="setting_bar"><?php _e( 'Plugins you should try','import-facebook-events' ); ?></h3>
                        <?php 
                        if( !empty( $plugins ) ){
                            foreach ($plugins as $plugin ) {
                                ?>
                                <div class="plugin_box">
                                    <?php if( $plugin->banners['low'] != '' ){ ?>
                                        <img src="<?php echo $plugin->banners['low']; ?>" class="plugin_img" title="<?php echo $plugin->name; ?>">
                                    <?php } ?>                    
                                    <div class="plugin_content">
                                        <h3><?php echo $plugin->name; ?></h3>

                                        <?php wp_star_rating( array(
                                        'rating' => $plugin->rating,
                                        'type'   => 'percent',
                                        'number' => $plugin->num_ratings,
                                        ) );?>

                                        <?php if( $plugin->version != '' ){ ?>
                                            <p><strong><?php _e( 'Version:','import-facebook-events' ); ?> </strong><?php echo $plugin->version; ?></p>
                                        <?php } ?>

                                        <?php if( $plugin->requires != '' ){ ?>
                                            <p><strong><?php _e( 'Requires:','import-facebook-events' ); ?> </strong> <?php _e( 'WordPress ','import-facebook-events' ); echo $plugin->requires; ?>+</p>
                                        <?php } ?>

                                        <?php if( $plugin->active_installs != '' ){ ?>
                                            <p><strong><?php _e( 'Active Installs:','import-facebook-events' ); ?> </strong><?php echo $plugin->active_installs; ?>+</p>
                                        <?php } ?>

                                        <a class="button button-secondary" href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin='. $plugin->slug.'&TB_iframe=1&width=772&height=600'); ?>" target="_blank">
                                            <?php _e( 'Install Now','import-facebook-events' ); ?>
                                        </a>
                                        <a class="button button-primary" href="<?php echo $plugin->homepage . '?utm_source=crosssell&utm_medium=web&utm_content=supportpage&utm_campaign=freeplugin'; ?>" target="_blank">
                                            <?php _e( 'Buy Now','import-facebook-events' ); ?>
                                        </a>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                        <div style="clear: both;">
                    </div>

                </div>
            </div>
            <br class="clear">
        </div>

    </div><!-- /.wrap -->
    <?php
}
