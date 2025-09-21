/**
 * SkyBug Ticket Management JavaScript
 * Handles quick actions and interactions for the issue admin list
 */
jQuery(document).ready(function($) {
    'use strict';

    // Initialize ticket management
    var TicketManagement = {
        init: function() {
            this.bindEvents();
            this.initTooltips();
        },

        bindEvents: function() {
            // Quick status change buttons
            $(document).on('click', '.skybug-quick-status', this.handleQuickStatus);
            
            // Send email buttons
            $(document).on('click', '.skybug-send-email', this.handleSendEmail);
            
            // Add loading states to buttons
            $(document).on('click', '.skybug-quick-status, .skybug-send-email', this.addLoadingState);
        },

        initTooltips: function() {
            // Simple tooltip functionality
            $('.skybug-quick-status, .skybug-send-email').each(function() {
                var $button = $(this);
                var title = $button.attr('title');
                
                if (title) {
                    $button.on('mouseenter', function(e) {
                        var tooltip = $('<div class="skybug-tooltip">' + title + '</div>');
                        $('body').append(tooltip);
                        
                        var offset = $button.offset();
                        tooltip.css({
                            position: 'absolute',
                            top: offset.top - 30,
                            left: offset.left + ($button.width() / 2) - (tooltip.width() / 2),
                            background: '#333',
                            color: 'white',
                            padding: '4px 8px',
                            borderRadius: '4px',
                            fontSize: '11px',
                            zIndex: 9999,
                            whiteSpace: 'nowrap'
                        });
                    }).on('mouseleave', function() {
                        $('.skybug-tooltip').remove();
                    });
                }
            });
        },

        addLoadingState: function(e) {
            var $button = $(e.currentTarget);
            $button.addClass('loading').prop('disabled', true);
        },

        removeLoadingState: function($button) {
            $button.removeClass('loading').prop('disabled', false);
        },

        handleQuickStatus: function(e) {
            e.preventDefault();
            var $button = $(this);
            var postId = $button.data('post-id');
            var newStatus = $button.data('status');

            if (!postId || !newStatus) {
                console.error('Missing post ID or status');
                TicketManagement.removeLoadingState($button);
                return;
            }

            // Confirm action for certain statuses
            var confirmMessages = {
                'skybug_closed': 'Er du sikker på at du vil lukke denne saken?',
                'skybug_resolved': 'Er du sikker på at denne saken er løst?'
            };

            if (confirmMessages[newStatus] && !confirm(confirmMessages[newStatus])) {
                TicketManagement.removeLoadingState($button);
                return;
            }

            // Send AJAX request
            $.ajax({
                url: skyBugTicket.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'skybug_quick_status',
                    post_id: postId,
                    status: newStatus,
                    nonce: skyBugTicket.nonce
                },
                success: function(response) {
                    try {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (result.success) {
                            // Show success message
                            TicketManagement.showNotification('success', result.message || skyBugTicket.strings.success);
                            
                            // Reload page to show updated status
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            throw new Error(result.message || 'Unknown error');
                        }
                    } catch (err) {
                        console.error('Status update error:', err);
                        TicketManagement.showNotification('error', skyBugTicket.strings.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    TicketManagement.showNotification('error', skyBugTicket.strings.error);
                },
                complete: function() {
                    TicketManagement.removeLoadingState($button);
                }
            });
        },

        handleSendEmail: function(e) {
            e.preventDefault();
            var $button = $(this);
            var postId = $button.data('post-id');
            var email = $button.data('email');

            if (!postId || !email) {
                console.error('Missing post ID or email');
                TicketManagement.removeLoadingState($button);
                return;
            }

            // Open email composer modal or redirect to compose email
            var subject = 'Ang. sak #' + postId;
            var mailtoUrl = 'mailto:' + email + '?subject=' + encodeURIComponent(subject);
            
            // Open email client
            window.location.href = mailtoUrl;
            
            TicketManagement.removeLoadingState($button);
            
            // Show notification
            TicketManagement.showNotification('info', 'E-post-klient åpnet for ' + email);
        },

        showNotification: function(type, message) {
            // Remove existing notifications
            $('.skybug-admin-notification').remove();
            
            // Create notification element
            var $notification = $('<div class="skybug-admin-notification skybug-notification-' + type + '">')
                .text(message)
                .css({
                    position: 'fixed',
                    top: '32px',
                    right: '20px',
                    background: type === 'success' ? '#46b450' : (type === 'error' ? '#dc3232' : '#00a0d2'),
                    color: 'white',
                    padding: '10px 15px',
                    borderRadius: '4px',
                    zIndex: 100000,
                    fontSize: '13px',
                    fontWeight: '500',
                    maxWidth: '300px',
                    boxShadow: '0 2px 8px rgba(0,0,0,0.2)'
                });

            // Add to page
            $('body').append($notification);

            // Auto remove after 4 seconds
            setTimeout(function() {
                $notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 4000);

            // Click to dismiss
            $notification.on('click', function() {
                $(this).fadeOut(300, function() {
                    $(this).remove();
                });
            });
        }
    };

    // Initialize when DOM is ready
    TicketManagement.init();

    // Handle page navigation (for keeping notifications on page reload)
    $(window).on('beforeunload', function() {
        $('.skybug-admin-notification').remove();
    });
});