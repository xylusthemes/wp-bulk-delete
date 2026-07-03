(function( $ ) {
	'use strict';

	var styleInjected = false;

	function injectStyles() {
		if (styleInjected) return;
		styleInjected = true;

		var css = '' +
		'.wpbd-progress-notice {' +
			'border: none !important;' +
			'border-left: 4px solid #00a32a !important;' +
			'border-radius: 6px !important;' +
			'background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%) !important;' +
			'box-shadow: 0 2px 8px rgba(0,163,42,0.10), 0 1px 2px rgba(0,0,0,0.06) !important;' +
			'padding: 0 !important;' +
			'margin-top: 3% !important;' +
			'margin-bottom: 16px !important;' +
			'overflow: hidden;' +
		'}' +

		'.wpbd-progress-notice:hover {' +
			'box-shadow: 0 4px 16px rgba(0,163,42,0.15), 0 2px 4px rgba(0,0,0,0.08) !important;' +
		'}' +

		'.wpbd-progress-notice.wpbd-pn-error {' +
			'border-left-color: #d63638 !important;' +
			'background: linear-gradient(135deg, #fcf0f1 0%, #ffffff 100%) !important;' +
			'box-shadow: 0 2px 8px rgba(214,54,56,0.10), 0 1px 2px rgba(0,0,0,0.06) !important;' +
		'}' +

		'.wpbd-progress-notice.wpbd-pn-complete {' +
			'border-left-color: #00a32a !important;' +
			'background: linear-gradient(135deg, #e8fce8 0%, #f6fff6 100%) !important;' +
		'}' +

		'.wpbd-pn-inner {' +
			'padding: 18px 22px;' +
		'}' +

		'.wpbd-pn-header {' +
			'display: flex;' +
			'align-items: center;' +
			'gap: 10px;' +
			'margin-bottom: 14px;' +
		'}' +

		'.wpbd-pn-icon {' +
			'width: 36px;' +
			'height: 36px;' +
			'border-radius: 50%;' +
			'background: linear-gradient(135deg, #00a32a, #00c853);' +
			'display: flex;' +
			'align-items: center;' +
			'justify-content: center;' +
			'flex-shrink: 0;' +
			'box-shadow: 0 2px 6px rgba(0,163,42,0.25);' +
		'}' +

		'.wpbd-pn-icon svg {' +
			'width: 18px;' +
			'height: 18px;' +
			'fill: #fff;' +
		'}' +

		'.wpbd-pn-error .wpbd-pn-icon {' +
			'background: linear-gradient(135deg, #d63638, #f86b6b) !important;' +
			'box-shadow: 0 2px 6px rgba(214,54,56,0.25) !important;' +
		'}' +

		'.wpbd-cancel-btn {' +
			'background: #d63638;' +
			'color: #fff;' +
			'border: none;' +
			'border-radius: 4px;' +
			'padding: 6px 14px;' +
			'font-size: 11px;' +
			'font-weight: 600;' +
			'cursor: pointer;' +
			'transition: background 0.2s ease;' +
			'margin-left: auto;' +
			'line-height: 1.2;' +
		'}' +

		'.wpbd-cancel-btn:hover {' +
			'background: #b32124;' +
		'}' +

		'.wpbd-pn-title {' +
			'font-size: 14px;' +
			'font-weight: 600;' +
			'color: #1a1a1a;' +
			'line-height: 1.4;' +
		'}' +

		'.wpbd-pn-title span {' +
			'color: #00a32a;' +
			'font-weight: 700;' +
		'}' +

		'.wpbd-pn-error .wpbd-pn-title span {' +
			'color: #d63638 !important;' +
		'}' +

		'.wpbd-pn-bar-outer {' +
			'width: 100%;' +
			'height: 22px;' +
			'background: #e5e7eb;' +
			'border-radius: 11px;' +
			'overflow: hidden;' +
			'position: relative;' +
			'margin-bottom: 8px;' +
		'}' +

		'.wpbd-pn-bar-inner {' +
			'height: 100%;' +
			'border-radius: 11px;' +
			'transition: width 0.6s cubic-bezier(0.4,0,0.2,1);' +
			'position: relative;' +
			'background: linear-gradient(90deg, #00a32a 0%, #00c853 50%, #00a32a 100%);' +
			'background-size: 200% 100%;' +
			'animation: wpbd-pn-shimmer 2s ease-in-out infinite;' +
		'}' +

		'.wpbd-pn-bar-inner::after {' +
			'content: "";' +
			'position: absolute;' +
			'top: 0; left: 0; right: 0; bottom: 0;' +
			'background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.25) 50%, transparent 100%);' +
			'background-size: 200% 100%;' +
			'animation: wpbd-pn-shine 1.8s linear infinite;' +
			'border-radius: 11px;' +
		'}' +

		'.wpbd-pn-complete .wpbd-pn-bar-inner,' +
		'.wpbd-pn-error .wpbd-pn-bar-inner {' +
			'animation: none !important;' +
		'}' +

		'.wpbd-pn-complete .wpbd-pn-bar-inner::after,' +
		'.wpbd-pn-error .wpbd-pn-bar-inner::after {' +
			'animation: none !important;' +
			'background: none !important;' +
		'}' +

		'.wpbd-pn-error .wpbd-pn-bar-inner {' +
			'background: #d63638 !important;' +
		'}' +

		'.wpbd-pn-complete .wpbd-pn-bar-inner {' +
			'background: linear-gradient(90deg, #00a32a, #00c853) !important;' +
		'}' +

		'.wpbd-pn-bar-pct {' +
			'position: absolute;' +
			'top: 50%;' +
			'left: 50%;' +
			'transform: translate(-50%, -50%);' +
			'font-size: 11px;' +
			'font-weight: 700;' +
			'color: #fff;' +
			'text-shadow: 0 1px 2px rgba(0,0,0,0.2);' +
			'z-index: 2;' +
			'white-space: nowrap;' +
		'}' +

		'.wpbd-pn-stats {' +
			'display: flex;' +
			'justify-content: space-between;' +
			'align-items: center;' +
			'font-size: 12px;' +
			'color: #6b7280;' +
		'}' +

		'.wpbd-pn-stats strong {' +
			'color: #1a1a1a;' +
			'font-weight: 600;' +
		'}' +

		'@keyframes wpbd-pn-shimmer {' +
			'0% { background-position: 100% 0; }' +
			'100% { background-position: -100% 0; }' +
		'}' +

		'@keyframes wpbd-pn-shine {' +
			'0% { background-position: 200% 0; }' +
			'100% { background-position: -200% 0; }' +
		'}';

		$('head').append('<style id="wpbd-progress-css">' + css + '</style>');
	}

	function deleteIcon() {
		return '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>';
	}

	function checkIcon() {
		return '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-1 15l-5-5 1.4-1.4L11 14.2l7.6-7.6L20 8l-9 9z"/></svg>';
	}

	function errorIcon() {
		return '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>';
	}

	$(document).ready(function() {

		injectStyles();

		var activeXHR = null;
		var isCancelled = false;

		// Intercept the final form submission for any form with .wpbd-delete-form class
		$(document).on('submit', '.wpbd-delete-form', function(e) {

			// If it's a scheduled delete, don't intercept here (it's handled via admin-post.php)
			var isScheduled = $(this).find('input[name="delete_time"]:checked').val() === 'scheduled';
			if ( isScheduled ) {
				return;
			}

			e.preventDefault();

			var $form = $(this);
			var formData = $form.serialize();

			isCancelled = false;
			activeXHR = null;

			// Inject progress UI
			showProgressUI($form);

			// Start the batch deletion
			runBatchDelete($form, formData, 0);
		});

		function showProgressUI($form) {
			// Disable submit button
			$form.find('.wpbd_button, input[type="submit"]').prop('disabled', true);

			// Remove any existing progress UI
			$('.wpbd-progress-notice').remove();

			var progressHTML =
				'<div class="wpbd-progress-notice">' +
					'<div class="wpbd-pn-inner">' +
						'<div class="wpbd-pn-header">' +
							'<div class="wpbd-pn-icon">' + deleteIcon() + '</div>' +
							'<div class="wpbd-pn-title">Deleting items <span>&hellip;</span></div>' +
							'<button type="button" class="wpbd-cancel-btn">Cancel Deletion</button>' +
						'</div>' +
						'<div class="wpbd-pn-bar-outer">' +
							'<div class="wpbd-pn-bar-inner" style="width: 0%;"></div>' +
							'<div class="wpbd-pn-bar-pct">0%</div>' +
						'</div>' +
						'<div class="wpbd-pn-stats">' +
							'<span>Preparing to delete...</span>' +
							'<span></span>' +
						'</div>' +
					'</div>' +
				'</div>';

			// Find the best container for the progress bar:
			// 1. A sibling .delete_notice right before the form (cleanup-specific)
			// 2. The page-level .delete_notice
			// 3. Prepend inside the form
			var $noticeContainer = $form.prev('.delete_notice');
			if ($noticeContainer.length) {
				$noticeContainer.html(progressHTML);
			} else if ($('.delete_notice').length) {
				$('.delete_notice').first().html(progressHTML);
			} else {
				$form.prepend(progressHTML);
			}

			// Scroll to progress
			$('html, body').animate({
				scrollTop: $('.wpbd-progress-notice').offset().top - 80
			}, 500);
		}

		function updateProgressUI(deleted, offset, total) {
			var percentage = 0;
			if (total > 0) {
				percentage = Math.min(100, Math.round((offset / total) * 100));
			}

			$('.wpbd-pn-bar-inner').css('width', percentage + '%');
			$('.wpbd-pn-bar-pct').text(percentage + '%');
			$('.wpbd-pn-stats').html(
				'<span>Processed <strong>' + offset + '</strong> of <strong>' + total + '</strong> items</span>' +
				'<span><strong>' + (total - offset) + '</strong> remaining</span>'
			);
		}

		function finishProgressUI(deleted, total, isError, errorMessage) {
			$('.wpbd-delete-form').find('.wpbd_button, input[type="submit"]').prop('disabled', false);

			// Remove cancel button
			$('.wpbd-cancel-btn').remove();

			if (isError) {
				$('.wpbd-progress-notice').addClass('wpbd-pn-error');
				$('.wpbd-pn-icon').html(errorIcon());
				$('.wpbd-pn-title').html('Deletion encountered an error');
				$('.wpbd-pn-bar-pct').text('Error');
				$('.wpbd-pn-stats').html(
					'<span style="color:#d63638;font-weight:600;">' + errorMessage + '</span>' +
					'<span></span>'
				);
			} else {
				$('.wpbd-progress-notice').addClass('wpbd-pn-complete');
				$('.wpbd-pn-bar-inner').css('width', '100%');
				$('.wpbd-pn-bar-pct').text('100%');
				$('.wpbd-pn-icon').html(checkIcon());
				
				// Make the header relative and add the dismiss button
				$('.wpbd-pn-header').css('position', 'relative');
				$('.wpbd-pn-title').html('Deletion <span>completed!</span>');
				if (!$('.wpbd-progress-notice .wpbd-dismiss-btn').length) {
					$('.wpbd-pn-header').append('<button type="button" class="notice-dismiss wpbd-dismiss-btn" style="position:absolute; top:50%; right:0; transform:translateY(-50%); text-decoration:none;"><span class="screen-reader-text">Dismiss this notice.</span></button>');
				}

				$('.wpbd-pn-stats').html(
					'<span>All <strong>' + total + '</strong> items processed successfully</span>' +
					'<span style="color:#00a32a;font-weight:600;">&#10003; Done</span>'
				);
			}
		}

		function runBatchDelete($form, formData, offset) {
			if (isCancelled) {
				return;
			}

			var ajaxData = {
				action: 'wpbd_run_delete',
				nonce: wpbdProgressData.nonce,
				offset: offset,
				batch_size: 25,
				wpbd_args: formData
			};

			activeXHR = $.post(wpbdProgressData.ajaxurl, ajaxData, function(response) {
				if (isCancelled) {
					return;
				}
				if (response.success) {
					var data = response.data;
					
					if (offset === 0 && data.total === 0) {
						// Enable submit button
						$form.find('.wpbd_button, input[type="submit"]').prop('disabled', false);
						// Remove progress bar
						$('.wpbd-progress-notice').remove();
						// Show notice
						var noticeHTML = '<div class="notice wpbd-notice notice-success is-dismissible"><p><strong>Nothing to delete!!</strong></p></div>';
						if ($('.delete_notice').length) {
							$('.delete_notice').html(noticeHTML);
						} else {
							$form.prepend(noticeHTML);
						}
						return;
					}

					updateProgressUI(data.deleted, data.offset, data.total);

					if (data.done) {
						// Add a slight delay to allow the progress bar transition to be visible
						setTimeout(function() {
							finishProgressUI(data.deleted, data.total, false, '');
						}, 800);
					} else {
						// Process next batch
						runBatchDelete($form, formData, data.offset);
					}
				} else {
					var errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error occurred.';
					finishProgressUI(0, 0, true, errorMsg);
				}
			}).fail(function(jqXHR, textStatus, errorThrown) {
				if (isCancelled) {
					return;
				}
				finishProgressUI(0, 0, true, 'Server error: ' + textStatus);
			});
		}

		// Handle cancel button click
		$(document).on('click', '.wpbd-cancel-btn', function() {
			isCancelled = true;
			if (activeXHR) {
				activeXHR.abort();
			}

			// Show cancelled status
			$('.wpbd-progress-notice').addClass('wpbd-pn-error');
			$('.wpbd-pn-icon').html(errorIcon());
			$('.wpbd-pn-title').html('Deletion <span>cancelled</span>');
			$('.wpbd-pn-bar-pct').text('Cancelled');
			$('.wpbd-pn-stats').html(
				'<span style="color:#d63638;font-weight:600;">Process was manually cancelled.</span>' +
				'<span></span>'
			);

			// Remove cancel button
			$('.wpbd-cancel-btn').remove();

			// Enable submit button
			$('.wpbd-delete-form').find('.wpbd_button, input[type="submit"]').prop('disabled', false);

			// Add dismiss button
			if (!$('.wpbd-progress-notice .wpbd-dismiss-btn').length) {
				$('.wpbd-pn-header').append('<button type="button" class="notice-dismiss wpbd-dismiss-btn" style="position:absolute; top:50%; right:0; transform:translateY(-50%); text-decoration:none;"><span class="screen-reader-text">Dismiss this notice.</span></button>');
			}

			// Notify server to clear the transient
			var $form = $('.wpbd-delete-form');
			var formData = $form.serialize();
			$.post(wpbdProgressData.ajaxurl, {
				action: 'wpbd_cancel_delete',
				nonce: wpbdProgressData.nonce,
				wpbd_args: formData
			});
		});

		// Handle dismiss button click
		$(document).on('click', '.wpbd-progress-notice .wpbd-dismiss-btn', function() {
			$(this).closest('.wpbd-progress-notice').fadeOut(300, function() {
				$(this).remove();
			});
		});

	});

})( jQuery );
