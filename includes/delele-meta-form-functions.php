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
    // Counts
    $orphan_postmeta_count        = wpbulkdelete()->api->get_post_count('orphan_postmeta');
    $duplicated_postmeta_count    = wpbulkdelete()->api->get_post_count('duplicated_postmeta');
    $orphan_commentmeta_count     = wpbulkdelete()->api->get_post_count('orphan_commentmeta');
    $duplicated_commentmeta_count = wpbulkdelete()->api->get_post_count('duplicated_commentmeta');
    $orphan_usermeta_count        = wpbulkdelete()->api->get_post_count('orphan_usermeta');
    $duplicated_usermeta_count    = wpbulkdelete()->api->get_post_count('duplicated_usermeta');
    $orphan_termmeta_count        = wpbulkdelete()->api->get_post_count('orphan_termmeta');
    $duplicated_termmeta_count    = wpbulkdelete()->api->get_post_count('duplicated_termmeta');
    ?>


    <div class="wpbd-card" style="margin-top:20px;">
        <div class="content"  aria-expanded="true" style=" ">
            <div class="wpbd-inner-main-section">
                <div class="wpbd-inner-section-1" >
                    <span class="wpbd-title-text" ><?php _e('Cleanup Metas ','wp-bulk-delete'); ?></span>
                </div>
                <div class="wpbd-inner-section-2">
                    <fieldset>
                        <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_orphan_postmeta" type="checkbox" value="orphan_postmeta" >
                        <label for="cleanup_orphan_postmeta">                    
                            <?php printf( __( 'Orphaned Post Meta (%d Post Meta)', 'wp-bulk-delete' ), $orphan_postmeta_count ); ?>
                        </label>
                    </fieldset>

                    <fieldset>
                        <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_duplicated_postmeta" type="checkbox" value="duplicated_postmeta" >
                        <label for="cleanup_duplicated_postmeta">                    
                            <?php printf( __( 'Duplicated Post Meta (%d Post Meta)', 'wp-bulk-delete' ), $duplicated_postmeta_count ); ?>
                        </label>
                    </fieldset>

                    <fieldset>
                        <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_orphan_commentmeta" type="checkbox" value="orphan_commentmeta" >
                        <label for="cleanup_orphan_commentmeta">                    
                            <?php printf( __( 'Orphaned Comment Meta (%d Comment Meta)', 'wp-bulk-delete' ), $orphan_commentmeta_count ); ?>
                        </label>
                    </fieldset>

                    <fieldset>
                        <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_duplicated_commentmeta" type="checkbox" value="duplicated_commentmeta" >
                        <label for="cleanup_duplicated_commentmeta">                    
                            <?php printf( __( 'Duplicated Comment Meta (%d Comment Meta)', 'wp-bulk-delete' ), $duplicated_commentmeta_count ); ?>
                        </label>
                    </fieldset>

                    <fieldset>
                        <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_orphan_usermeta" type="checkbox" value="orphan_usermeta" >
                        <label for="cleanup_orphan_usermeta">                    
                            <?php printf( __( 'Orphaned User Meta (%d User Meta)', 'wp-bulk-delete' ), $orphan_usermeta_count ); ?>
                        </label>
                    </fieldset>

                    <fieldset>
                        <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_duplicated_usermeta" type="checkbox" value="duplicated_usermeta" >
                        <label for="cleanup_duplicated_usermeta">                    
                            <?php printf( __( 'Duplicated User Meta (%d User Meta)', 'wp-bulk-delete' ), $duplicated_usermeta_count ); ?>
                        </label>
                    </fieldset>

                    <fieldset>
                        <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_orphan_termmeta" type="checkbox" value="orphan_termmeta" >
                        <label for="cleanup_orphan_termmeta">                    
                            <?php printf( __( 'Orphaned Term Meta (%d Term Meta)', 'wp-bulk-delete' ), $orphan_commentmeta_count ); ?>
                        </label>
                    </fieldset>

                    <fieldset>
                        <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_duplicated_termmeta" type="checkbox" value="duplicated_termmeta" >
                        <label for="cleanup_duplicated_termmeta">                    
                            <?php printf( __( 'Duplicated Term Meta (%d Term Meta)', 'wp-bulk-delete' ), $duplicated_termmeta_count ); ?>
                        </label>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
    <?php
}