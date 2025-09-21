/**
 * SkyBug Issue Edit JavaScript
 * Handles functionality for the issue edit page
 */
jQuery(document).ready(function($) {
    'use strict';

    // Initialize issue edit functionality
    var IssueEdit = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Send email to reporter
            $('#send_email_to_reporter').on('click', this.sendEmailToReporter);
            
            // Check IMAP emails - NYTT
            $('#check-imap-emails').on('click', this.checkImapEmails);
        },

        sendEmailToReporter: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var subject = $('#email_subject').val();
            var message = $('#email_message').val();
            var postId = $('#post_ID').val();

            // Validate required fields
            if (!subject || !message) {
                alert(skyBugIssue.strings.required);
                return;
            }

            // Disable button and show loading state
            $button.prop('disabled', true).text(skyBugIssue.strings.sending);

            // Send AJAX request
            $.ajax({
                url: skyBugIssue.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'skybug_send_reporter_email',
                    post_id: postId,
                    subject: subject,
                    message: message,
                    nonce: skyBugIssue.nonce
                },
                success: function(response) {
                    try {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (result.success) {
                            // Show success message
                            IssueEdit.showNotification('success', result.message);
                            
                            // Clear the message field
                            $('#email_message').val('');
                            
                            // Re-enable button
                            $button.prop('disabled', false).text('Send E-post');
                            
                            // Refresh page after 2 seconds to show updated internal comments
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                            
                        } else {
                            throw new Error(result.message || 'Unknown error');
                        }
                    } catch (err) {
                        console.error('Email send error:', err);
                        IssueEdit.showNotification('error', err.message || skyBugIssue.strings.error);
                        $button.prop('disabled', false).text('Send E-post');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    IssueEdit.showNotification('error', skyBugIssue.strings.error);
                    $button.prop('disabled', false).text('Send E-post');
                }
            });
        },

        showNotification: function(type, message) {
            // Remove existing notifications
            $('.skybug-admin-notification').remove();
            
            // Create notification element
            var $notification = $('<div class="skybug-admin-notification skybug-notification-' + type + '">')
                .html(message)
                .css({
                    position: 'fixed',
                    top: '32px',
                    right: '20px',
                    background: type === 'success' ? '#46b450' : '#dc3232',
                    color: 'white',
                    padding: '12px 20px',
                    borderRadius: '4px',
                    zIndex: 100000,
                    fontSize: '14px',
                    fontWeight: '500',
                    maxWidth: '400px',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                    border: '1px solid ' + (type === 'success' ? '#42a948' : '#d83e3e')
                });

            // Add to page
            $('body').append($notification);

            // Auto remove after 5 seconds
            setTimeout(function() {
                $notification.fadeOut(400, function() {
                    $(this).remove();
                });
            }, 5000);

            // Click to dismiss
            $notification.on('click', function() {
                $(this).fadeOut(300, function() {
                    $(this).remove();
                });
            });

            // Add close button
            var $closeBtn = $('<span style="margin-left: 10px; cursor: pointer; font-weight: bold;">×</span>');
            $closeBtn.on('click', function(e) {
                e.stopPropagation();
                $notification.fadeOut(200, function() {
                    $(this).remove();
                });
            });
            $notification.append($closeBtn);
        },

        // IMAP email check function - NYTT
        checkImapEmails: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var postId = $button.data('post-id');
            var $result = $('#imap-check-result');

            // Disable button and show loading state
            $button.prop('disabled', true).text('Henter...');
            $result.html('<div style="color: #666; font-style: italic;">Sjekker IMAP for nye e-poster...</div>');

            // Send AJAX request
            $.ajax({
                url: skyBugIssue.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'skybug_check_imap_emails',
                    post_id: postId,
                    nonce: skyBugIssue.imapNonce || skyBugIssue.nonce
                },
                success: function(response) {
                    try {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (result.success) {
                            $result.html('<div style="color: #28a745; font-weight: bold;">✅ ' + result.message + '</div>');
                            
                            // If emails were found, show them
                            if (result.emails && result.emails.length > 0) {
                                var emailsHtml = '<div style="margin-top: 10px;"><h5>Nye e-poster:</h5>';
                                result.emails.forEach(function(email) {
                                    var date = new Date(email.date * 1000).toLocaleString('no-NO');
                                    emailsHtml += '<div style="background: #e3f2fd; padding: 8px; margin: 4px 0; border-radius: 4px; font-size: 12px;">';
                                    emailsHtml += '<strong>Fra:</strong> ' + email.from + '<br>';
                                    emailsHtml += '<strong>Emne:</strong> ' + email.subject + '<br>';
                                    emailsHtml += '<strong>Dato:</strong> ' + date;
                                    emailsHtml += '</div>';
                                });
                                emailsHtml += '</div>';
                                $result.append(emailsHtml);
                            }
                            
                            // Auto-refresh page after 2 seconds to show updated email list
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $result.html('<div style="color: #dc3545; font-weight: bold;">❌ ' + result.message + '</div>');
                        }
                    } catch (error) {
                        $result.html('<div style="color: #dc3545;">Feil ved parsing av respons</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $result.html('<div style="color: #dc3545;">AJAX feil: ' + error + '</div>');
                },
                complete: function() {
                    // Re-enable button
                    $button.prop('disabled', false).text('Hent nye e-poster');
                }
            });
        }
    };

    // Initialize when DOM is ready
    IssueEdit.init();

    // Enhanced meta box styling
    $('.skybug-ticket-info select, .skybug-ticket-info input').css({
        'border': '1px solid #ddd',
        'border-radius': '4px',
        'padding': '6px 8px'
    });

    // Style internal comments
    $('.skybug-internal-comments textarea').css({
        'border': '1px solid #ddd',
        'border-radius': '4px',
        'padding': '10px',
        'font-family': 'inherit',
        'resize': 'vertical'
    });

    // Style communication form
    $('.skybug-communication input, .skybug-communication textarea').css({
        'border': '1px solid #ddd',
        'border-radius': '4px',
        'padding': '8px 10px'
    });

    $('.skybug-communication textarea').css({
        'min-height': '120px',
        'resize': 'vertical',
        'font-family': 'inherit'
    });

    // Add hover effects to buttons
    $('#send_email_to_reporter').hover(
        function() {
            $(this).css('background-color', '#005177');
        },
        function() {
            $(this).css('background-color', '');
        }
    );
});