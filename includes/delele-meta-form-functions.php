<?php
/**
 * Form Process
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Form Process
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render meta cleanup options
 *
 * @since 1.1.0
 * @return void
 */
function wpbd_render_meta_cleanup(){
    ?>


    <div class="wpbd-card" style="margin-top:20px;">
        <div class="content"  aria-expanded="true" style=" ">
            <div class="wpbd-inner-main-section">
                <div class="wpbd-inner-section-1" >
                    <span class="wpbd-title-text" ><?php esc_html_e('Cleanup Meta ','wp-bulk-delete'); ?></span>
                </div>
                <div class="wpbd-inner-section-2">
                    <div class="cleanups_section" >
                        <input name="cleanup_post_type[]" class="cleanup_post_type" id="all_orphan_duplicate" type="checkbox" value="all_orphan_duplicate" >
                        <label for="all_orphan_duplicate">                    
                            <?php esc_html_e( 'Clear All Metadata Cleanup', 'wp-bulk-delete' ); ?>
                        </label>
                        <span class="wpbd-tooltip" >
                            <div>
                                <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                                </svg>
                                <span class="wpbd-popper">
                                    <?php
                                        $text         = esc_html__('Check all metadata to delete cleanup including options are given below.', 'wp-bulk-delete');
                                        $html_part    = '<br><strong>Orphan post meta, <br> Duplicate post meta, <br> Orphan comment meta, <br> Duplicate comment meta, <br> Orphan user meta, <br> Duplicate user meta, <br> Orphan term meta, <br> Duplicate term meta</strong>.';
                                        $allowed_html = array( 'br' => array(), 'strong' => array(), );
                                        // translators: %s: meta text.
                                        echo sprintf( esc_attr__( ' %s', 'wp-bulk-delete' ), $text ). ' ' . wp_kses($html_part, $allowed_html); // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings, WordPress.Security.EscapeOutput.OutputNotEscaped
                                    ?>
                                    <div class="wpbd-popper__arrow"></div>
                                </span>
                            </div>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}