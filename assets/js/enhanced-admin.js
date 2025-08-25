/**
 * Enhanced Admin JavaScript for Hyperleap Chatbots Plugin
 * Modern interactions and smooth user experience
 */

(function($) {
    'use strict';

    const HyperleapAdmin = {
        
        init: function() {
            this.bindEvents();
            this.initializeComponents();
        },

        bindEvents: function() {
            // Quick install form validation
            $('#hyperleap-quick-install-form').on('submit', this.handleQuickInstall.bind(this));
            
            // Real-time validation
            $('.hyperleap-input').on('input', this.validateField.bind(this));
            
            // Smooth transitions
            this.initSmoothTransitions();
        },

        initializeComponents: function() {
            // Initialize tooltips
            this.initTooltips();
            
            // Initialize copy-to-clipboard functionality
            this.initCopyFeatures();
            
            // Initialize keyboard shortcuts
            this.initKeyboardShortcuts();
        },

        handleQuickInstall: function(e) {
            e.preventDefault();
            
            const form = $(e.target);
            const submitBtn = form.find('button[type="submit"]');
            
            if (!this.validateQuickInstallForm(form)) {
                return;
            }

            this.setButtonLoading(submitBtn, true);
            
            const formData = {
                action: 'hyperleap_quick_install',
                nonce: hyperleapChatbots.nonce,
                chatbot_id: $('#quick-chatbot-id').val().trim(),
                chatbot_seed: $('#quick-chatbot-seed').val().trim()
            };

            $.ajax({
                url: hyperleapChatbots.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        HyperleapAdmin.showSuccess(response.data.message);
                        
                        // Redirect after success
                        setTimeout(function() {
                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;
                            }
                        }, 2000);
                    } else {
                        HyperleapAdmin.showError(response.data || hyperleapChatbots.strings.error);
                    }
                },
                error: function() {
                    HyperleapAdmin.showError(hyperleapChatbots.strings.error);
                },
                complete: function() {
                    HyperleapAdmin.setButtonLoading(submitBtn, false);
                }
            });
        },

        validateQuickInstallForm: function(form) {
            let isValid = true;
            const requiredFields = form.find('[required]');
            
            requiredFields.each(function() {
                const field = $(this);
                const value = field.val().trim();
                
                if (!value) {
                    HyperleapAdmin.showFieldError(field, 'This field is required');
                    isValid = false;
                } else {
                    HyperleapAdmin.clearFieldError(field);
                }
            });

            return isValid;
        },

        validateField: function(e) {
            const field = $(e.target);
            const value = field.val().trim();
            
            // Clear previous errors
            this.clearFieldError(field);
            
            // Validate based on field type
            if (field.attr('required') && !value) {
                this.showFieldError(field, 'This field is required');
                return false;
            }
            
            if (field.attr('type') === 'email' && value && !this.isValidEmail(value)) {
                this.showFieldError(field, 'Please enter a valid email address');
                return false;
            }
            
            return true;
        },

        showFieldError: function(field, message) {
            field.addClass('hyperleap-field-error');
            
            let errorElement = field.siblings('.hyperleap-field-error-message');
            if (errorElement.length === 0) {
                errorElement = $('<div class="hyperleap-field-error-message"></div>');
                field.after(errorElement);
            }
            
            errorElement.text(message).show();
        },

        clearFieldError: function(field) {
            field.removeClass('hyperleap-field-error');
            field.siblings('.hyperleap-field-error-message').hide();
        },

        setButtonLoading: function(button, loading) {
            const textElement = button.find('.btn-text');
            const spinnerElement = button.find('.btn-spinner');
            
            if (loading) {
                button.prop('disabled', true).addClass('loading');
                textElement.hide();
                spinnerElement.show();
            } else {
                button.prop('disabled', false).removeClass('loading');
                textElement.show();
                spinnerElement.hide();
            }
        },

        showSuccess: function(message) {
            this.showNotification('success', message, 5000);
        },

        showError: function(message) {
            this.showNotification('error', message, 7000);
        },

        showNotification: function(type, message, duration = 5000) {
            const notification = $(`
                <div class="hyperleap-notification hyperleap-notification-${type}">
                    <div class="hyperleap-notification-content">
                        <div class="hyperleap-notification-icon">
                            ${this.getNotificationIcon(type)}
                        </div>
                        <div class="hyperleap-notification-message">${message}</div>
                        <button class="hyperleap-notification-close" aria-label="Close">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            `);
            
            // Add to page
            $('body').append(notification);
            
            // Animate in
            setTimeout(() => notification.addClass('show'), 10);
            
            // Auto-remove
            const autoRemove = setTimeout(() => this.removeNotification(notification), duration);
            
            // Manual close
            notification.find('.hyperleap-notification-close').on('click', () => {
                clearTimeout(autoRemove);
                this.removeNotification(notification);
            });
        },

        removeNotification: function(notification) {
            notification.removeClass('show');
            setTimeout(() => notification.remove(), 300);
        },

        getNotificationIcon: function(type) {
            const icons = {
                success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"></polyline></svg>',
                error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
                warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>'
            };
            return icons[type] || icons.success;
        },

        initTooltips: function() {
            // Simple tooltip implementation
            $('[data-tooltip]').on('mouseenter', function() {
                const text = $(this).data('tooltip');
                const tooltip = $(`<div class="hyperleap-tooltip">${text}</div>`);
                
                $('body').append(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.css({
                    top: rect.top - tooltip.outerHeight() - 8,
                    left: rect.left + (rect.width / 2) - (tooltip.outerWidth() / 2)
                }).addClass('show');
                
                $(this).data('tooltip-element', tooltip);
            }).on('mouseleave', function() {
                const tooltip = $(this).data('tooltip-element');
                if (tooltip) {
                    tooltip.remove();
                }
            });
        },

        initCopyFeatures: function() {
            // Add copy buttons to code blocks
            $('.hyperleap-code').each(function() {
                const codeBlock = $(this);
                const copyBtn = $(`
                    <button class="hyperleap-copy-btn" title="Copy to clipboard">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                    </button>
                `);
                
                codeBlock.css('position', 'relative').append(copyBtn);
                
                copyBtn.on('click', function() {
                    const text = codeBlock.find('code').text() || codeBlock.text();
                    
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(() => {
                            HyperleapAdmin.showSuccess('Copied to clipboard!');
                        }).catch(() => {
                            HyperleapAdmin.fallbackCopyToClipboard(text);
                        });
                    } else {
                        HyperleapAdmin.fallbackCopyToClipboard(text);
                    }
                });
            });
        },

        fallbackCopyToClipboard: function(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                this.showSuccess('Copied to clipboard!');
            } catch (err) {
                this.showError('Failed to copy to clipboard');
            }
            
            document.body.removeChild(textArea);
        },

        initKeyboardShortcuts: function() {
            $(document).on('keydown', function(e) {
                // Ctrl/Cmd + K for quick install
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    const quickInstallLink = $('a[href*="hyperleap-chatbots-install"]').first();
                    if (quickInstallLink.length) {
                        window.location.href = quickInstallLink.attr('href');
                    }
                }
                
                // Escape to close modals/notifications
                if (e.key === 'Escape') {
                    $('.hyperleap-notification').each(function() {
                        HyperleapAdmin.removeNotification($(this));
                    });
                }
            });
        },

        initSmoothTransitions: function() {
            // Add smooth page transitions
            $('a[href*="hyperleap-chatbots"]').on('click', function(e) {
                const link = $(this);
                
                // Skip external links and special cases
                if (link.attr('target') === '_blank' || 
                    link.hasClass('no-transition') ||
                    e.ctrlKey || e.metaKey) {
                    return;
                }
                
                // Add loading state to current page
                $('body').addClass('hyperleap-loading');
            });
        },

        isValidEmail: function(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        // Utility function for debouncing
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        HyperleapAdmin.init();
        
        // Add global loading states
        $(document).ajaxStart(function() {
            $('body').addClass('hyperleap-ajax-loading');
        }).ajaxStop(function() {
            $('body').removeClass('hyperleap-ajax-loading');
        });
    });

    // Expose to global scope for external use
    window.HyperleapAdmin = HyperleapAdmin;

})(jQuery);

// Additional CSS for notifications and enhanced UX
const additionalStyles = `
<style>
/* Field validation styles */
.hyperleap-field-error {
    border-color: var(--hyperleap-error) !important;
    box-shadow: 0 0 0 3px rgb(239 68 68 / 0.1) !important;
}

.hyperleap-field-error-message {
    color: var(--hyperleap-error);
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: block;
}

/* Notification styles */
.hyperleap-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    max-width: 400px;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hyperleap-notification.show {
    opacity: 1;
    transform: translateX(0);
}

.hyperleap-notification-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: var(--hyperleap-surface);
    border: 1px solid var(--hyperleap-border);
    border-radius: var(--hyperleap-radius);
    padding: 1rem;
    box-shadow: var(--hyperleap-shadow-lg);
}

.hyperleap-notification-success {
    border-left: 4px solid var(--hyperleap-success);
}

.hyperleap-notification-error {
    border-left: 4px solid var(--hyperleap-error);
}

.hyperleap-notification-warning {
    border-left: 4px solid var(--hyperleap-warning);
}

.hyperleap-notification-icon {
    color: var(--hyperleap-primary);
    flex-shrink: 0;
}

.hyperleap-notification-success .hyperleap-notification-icon {
    color: var(--hyperleap-success);
}

.hyperleap-notification-error .hyperleap-notification-icon {
    color: var(--hyperleap-error);
}

.hyperleap-notification-message {
    flex: 1;
    font-size: 0.875rem;
    color: var(--hyperleap-text);
}

.hyperleap-notification-close {
    background: none;
    border: none;
    color: var(--hyperleap-text-secondary);
    cursor: pointer;
    padding: 0;
    margin: -2px 0 0 0;
    transition: var(--hyperleap-transition);
}

.hyperleap-notification-close:hover {
    color: var(--hyperleap-text);
}

/* Tooltip styles */
.hyperleap-tooltip {
    position: absolute;
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    z-index: 1000;
    opacity: 0;
    transform: translateY(4px);
    transition: all 0.2s;
}

.hyperleap-tooltip.show {
    opacity: 1;
    transform: translateY(0);
}

/* Copy button styles */
.hyperleap-copy-btn {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 0.25rem;
    padding: 0.5rem;
    cursor: pointer;
    opacity: 0;
    transition: var(--hyperleap-transition);
}

.hyperleap-code:hover .hyperleap-copy-btn {
    opacity: 1;
}

.hyperleap-copy-btn:hover {
    background: white;
}

/* Loading states */
.hyperleap-loading {
    cursor: wait;
}

.hyperleap-ajax-loading .hyperleap-btn {
    pointer-events: none;
    opacity: 0.7;
}

/* Button loading animation */
.hyperleap-btn.loading {
    cursor: wait;
    opacity: 0.8;
}

/* Smooth transitions */
.hyperleap-card,
.hyperleap-btn,
.hyperleap-input {
    transition: var(--hyperleap-transition);
}
</style>
`;

// Inject additional styles
document.head.insertAdjacentHTML('beforeend', additionalStyles);