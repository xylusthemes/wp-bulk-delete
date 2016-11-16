(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	jQuery(document).ready(function(){
		jQuery('.delete_all_datepicker').datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd'
		});
	});

	// Delete posts form handle.
	jQuery(document).ready(function() {
	    jQuery('#delete_posts_submit').on( 'click', function() {
	        var deleteform = jQuery("#delete_posts_form").serialize();
	        var data = {
	            'action': 'delete_posts_count',
	            'form': deleteform
	        };
	        jQuery(".spinner").addClass("is-active");
	        jQuery.post(ajaxurl, data, function(response) {
	            if( response != '' ){
	                var response = jQuery.parseJSON( response );
	                if( response.status == 0 ){
	                    jQuery(".delete_notice").html('<div class="notice notice-error"><p><strong>' + response.messages + '</strong></p></div>');
	                } else if( response.status == 2 ){
	                    jQuery(".delete_notice").html('<div class="notice notice-success"><p><strong>' + response.messages + '</strong></p></div>');
	                    jQuery("html, body").animate({ scrollTop: 0 }, "slow");
	                } else if( response.status == 1 ){
	                    if ( confirm(  response.post_count + ' posts will be delete. Would you like to proceed further?'  ) ){
	                        jQuery("#delete_posts_form").submit();    
	                    } 
	                }
	            }
	            jQuery(".spinner").removeClass("is-active");
	        });    
	    });                    
	});

	// Render Dynamic taxomony.
	jQuery(document).ready(function() {
	    jQuery('#delete_post_type').on( 'change', function() {
	    	var post_type = jQuery(this).val();
	        var data = {
	            'action': 'render_taxonomy_by_posttype',
	            'post_type': post_type
	        };

	        var taxomony_space = jQuery('.post_taxonomy');
	        jQuery('.taxo_terms_title').html('');
	        jQuery('.post_taxo_terms').html('');
	        taxomony_space.html('<span class="spinner is-active" style="float: none;"></span>');
	        // send ajax request.
	        jQuery.post(ajaxurl, data, function(response) {
	            if( response != '' ){
	            	taxomony_space.html( response );
	            }else{
	            	taxomony_space.html( '' );
	            }	            
	        });    
	    });                    
	});

	// Render Dynamic Terms.
	jQuery(document).ready(function() {
	    jQuery('.post_taxonomy_radio').live( 'change', function() {

	    	var post_taxomony = jQuery(this).val();
	    	var xt_taxonomy_title = jQuery(this).attr( 'title' );
			jQuery('.taxo_terms_title').html( xt_taxonomy_title + ':');
	        var data = {
	            'action': 'render_terms_by_taxonomy',
	            'post_taxomony': post_taxomony
	        };

	        var terms_space = jQuery('.post_taxo_terms');
	        terms_space.html('<span class="spinner is-active" style="float: none;"></span>');
	        // send ajax request.
	        jQuery.post(ajaxurl, data, function(response) {
	            if( response != '' ){
	            	terms_space.html( response );
	            }else{
	            	terms_space.html( '' );
	            }	            
	        });    
	    });                    
	});


	// Delete users form handle.
	jQuery(document).ready(function() {
	    jQuery('#delete_users_submit').on( 'click', function() {
	        var deleteuserform = jQuery("#delete_users_form").serialize();
	        var data = {
	            'action': 'delete_users_count',
	            'form': deleteuserform
	        };
	        jQuery(".spinner").addClass("is-active");
	        jQuery.post(ajaxurl, data, function(response) {
	            if( response != '' ){
	                var response = jQuery.parseJSON( response );
	                if( response.status == 0 ){
	                    jQuery(".delete_notice").html('<div class="notice notice-error"><p><strong>' + response.messages + '</strong></p></div>');
	                } else if( response.status == 2 ){
	                    jQuery(".delete_notice").html('<div class="notice notice-success"><p><strong>' + response.messages + '</strong></p></div>');
	                    jQuery("html, body").animate({ scrollTop: 0 }, "slow");
	                } else if( response.status == 1 ){
	                    if ( confirm(  response.post_count + ' users will be delete. Would you like to proceed further?'  ) ){
	                        jQuery("#delete_users_form").submit();    
	                    } 
	                }
	            }
	            jQuery(".spinner").removeClass("is-active");
	        });    
	    });                    
	});

	// Delete comments form handle.
	jQuery(document).ready(function() {
	    jQuery('#delete_comments_submit').on( 'click', function() {
	        var deletecommentform = jQuery("#delete_comments_form").serialize();
	        var data = {
	            'action': 'delete_comments_count',
	            'form': deletecommentform
	        };
	        jQuery(".spinner").addClass("is-active");
	        jQuery.post(ajaxurl, data, function(response) {
	            if( response != '' ){
	                var response = jQuery.parseJSON( response );
	                if( response.status == 0 ){
	                    jQuery(".delete_notice").html('<div class="notice notice-error"><p><strong>' + response.messages + '</strong></p></div>');
	                } else if( response.status == 2 ){
	                    jQuery(".delete_notice").html('<div class="notice notice-success"><p><strong>' + response.messages + '</strong></p></div>');
	                    jQuery("html, body").animate({ scrollTop: 0 }, "slow");
	                } else if( response.status == 1 ){
	                    if ( confirm(  response.post_count + ' comments will be delete. Would you like to proceed further?'  ) ){
	                        jQuery("#delete_comments_form").submit();    
	                    } 
	                }
	            }
	            jQuery(".spinner").removeClass("is-active");
	        });    
	    });                    
	});

	// Delete meta form handle.
	jQuery(document).ready(function() {
	    jQuery('#delete_meta_submit').on( 'click', function() {
	        var metaform = jQuery("#delete_meta_form").serialize();
	        var data = {
	            'action': 'delete_meta_count',
	            'form': metaform
	        };
	        jQuery(".spinner").addClass("is-active");
	        jQuery.post(ajaxurl, data, function(response) {
	            if( response != '' ){
	                var response = jQuery.parseJSON( response );
	                if( response.status == 0 ){
	                    jQuery(".delete_notice").html('<div class="notice notice-error"><p><strong>' + response.messages + '</strong></p></div>');
	                } else if( response.status == 2 ){
	                    jQuery(".delete_notice").html('<div class="notice notice-success"><p><strong>' + response.messages + '</strong></p></div>');
	                    jQuery("html, body").animate({ scrollTop: 0 }, "slow");
	                } else if( response.status == 1 ){
	                    if ( confirm(  response.post_count + ' meta will be delete. Would you like to proceed further?'  ) ){
	                        jQuery("#delete_meta_form").submit();    
	                    } 
	                }
	            }
	            jQuery(".spinner").removeClass("is-active");
	        });    
	    });                    
	});


	// Delete meta form handle.
	jQuery(document).ready(function() {
	    jQuery('#delete_terms_submit').on( 'click', function() {
	        var termform = jQuery("#delete_terms_form").serialize();
	        var data = {
	            'action': 'delete_terms_count',
	            'form': termform
	        };
	        jQuery(".spinner").addClass("is-active");
	        jQuery.post(ajaxurl, data, function(response) {
	            if( response != '' ){
	                var response = jQuery.parseJSON( response );
	                if( response.status == 0 ){
	                    jQuery(".delete_notice").html('<div class="notice notice-error"><p><strong>' + response.messages + '</strong></p></div>');
	                } else if( response.status == 2 ){
	                    jQuery(".delete_notice").html('<div class="notice notice-success"><p><strong>' + response.messages + '</strong></p></div>');
	                    jQuery("html, body").animate({ scrollTop: 0 }, "slow");
	                } else if( response.status == 1 ){
	                    if ( confirm(  response.post_count + ' Terms will be delete. Would you like to proceed further?'  ) ){
	                        jQuery("#delete_terms_form").submit();    
	                    } 
	                }
	            }
	            jQuery(".spinner").removeClass("is-active");
	        });    
	    });                    
	});

})( jQuery );
