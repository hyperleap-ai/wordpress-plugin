<?php
/**
 * Quick Install page for smooth chatbot setup
 */

if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap hyperleap-admin">
    <div class="hyperleap-header">
        <h1 class="hyperleap-title">
            <svg class="hyperleap-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2L2 7v10c0 5.55 3.84 9.739 9 11 5.16-1.261 9-5.45 9-11V7l-10-5z"/>
                <path d="M12 8v8m-4-4h8"/>
            </svg>
            <?php _e('Quick Install Chatbot', 'hyperleap-chatbots'); ?>
        </h1>
        <p class="hyperleap-subtitle">
            <?php _e('Get your AI chatbot running in under 60 seconds', 'hyperleap-chatbots'); ?>
        </p>
    </div>

    <div class="hyperleap-container">
        <div class="hyperleap-card hyperleap-quick-install">
            <div class="hyperleap-card-header">
                <h2><?php _e('Install Your Chatbot', 'hyperleap-chatbots'); ?></h2>
                <p><?php _e('Enter your chatbot credentials from your Hyperleap dashboard', 'hyperleap-chatbots'); ?></p>
            </div>
            
            <form id="hyperleap-quick-install-form" class="hyperleap-form">
                <div class="hyperleap-form-row">
                    <div class="hyperleap-form-group">
                        <label for="quick-chatbot-id" class="hyperleap-label">
                            <?php _e('Chatbot ID', 'hyperleap-chatbots'); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="quick-chatbot-id" 
                               name="chatbot_id" 
                               class="hyperleap-input" 
                               placeholder="<?php esc_attr_e('Enter your chatbot ID', 'hyperleap-chatbots'); ?>"
                               required>
                        <div class="hyperleap-field-hint">
                            <?php _e('Found in your Hyperleap dashboard under chatbot settings', 'hyperleap-chatbots'); ?>
                        </div>
                    </div>
                </div>

                <div class="hyperleap-form-row">
                    <div class="hyperleap-form-group">
                        <label for="quick-chatbot-seed" class="hyperleap-label">
                            <?php _e('Private Key (Seed)', 'hyperleap-chatbots'); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="password" 
                               id="quick-chatbot-seed" 
                               name="chatbot_seed" 
                               class="hyperleap-input" 
                               placeholder="<?php esc_attr_e('Enter your private key', 'hyperleap-chatbots'); ?>"
                               required>
                        <div class="hyperleap-field-hint">
                            <?php _e('Your chatbot\'s private key for secure authentication', 'hyperleap-chatbots'); ?>
                        </div>
                    </div>
                </div>

                <div class="hyperleap-form-actions">
                    <button type="button" id="validate-credentials" class="hyperleap-btn hyperleap-btn-secondary">
                        <span class="btn-text"><?php _e('Validate Credentials', 'hyperleap-chatbots'); ?></span>
                        <span class="btn-spinner" style="display: none;">
                            <svg class="hyperleap-spinner" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </button>
                    
                    <button type="submit" class="hyperleap-btn hyperleap-btn-primary" disabled>
                        <span class="btn-text"><?php _e('Install Chatbot', 'hyperleap-chatbots'); ?></span>
                        <span class="btn-spinner" style="display: none;">
                            <svg class="hyperleap-spinner" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </button>
                </div>

                <div id="validation-result" class="hyperleap-alert" style="display: none;"></div>
            </form>
        </div>

        <div class="hyperleap-card hyperleap-help-card">
            <div class="hyperleap-card-header">
                <h3><?php _e('Need Help?', 'hyperleap-chatbots'); ?></h3>
            </div>
            
            <div class="hyperleap-help-content">
                <div class="hyperleap-help-item">
                    <h4><?php _e('Where do I find my credentials?', 'hyperleap-chatbots'); ?></h4>
                    <p><?php _e('Log into your Hyperleap dashboard, navigate to your chatbot, and click "Website Integration" to find your Chatbot ID and Private Key.', 'hyperleap-chatbots'); ?></p>
                </div>
                
                <div class="hyperleap-help-item">
                    <h4><?php _e('Is my data secure?', 'hyperleap-chatbots'); ?></h4>
                    <p><?php _e('Yes! Your private key is securely stored and only used for authenticated communication with Hyperleap servers.', 'hyperleap-chatbots'); ?></p>
                </div>
                
                <div class="hyperleap-help-item">
                    <h4><?php _e('What happens after installation?', 'hyperleap-chatbots'); ?></h4>
                    <p><?php _e('Your chatbot will be automatically enabled site-wide. You can customize placement and settings from the main chatbots page.', 'hyperleap-chatbots'); ?></p>
                </div>
            </div>

            <div class="hyperleap-help-actions">
                <a href="https://hyperleap.ai/docs" target="_blank" class="hyperleap-btn hyperleap-btn-ghost">
                    <?php _e('View Documentation', 'hyperleap-chatbots'); ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
                
                <a href="mailto:support@hyperleap.ai" class="hyperleap-btn hyperleap-btn-ghost">
                    <?php _e('Contact Support', 'hyperleap-chatbots'); ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    const form = $('#hyperleap-quick-install-form');
    const validateBtn = $('#validate-credentials');
    const installBtn = form.find('button[type="submit"]');
    const resultDiv = $('#validation-result');
    
    let validationPassed = false;

    validateBtn.on('click', function() {
        const chatbotId = $('#quick-chatbot-id').val().trim();
        const chatbotSeed = $('#quick-chatbot-seed').val().trim();
        
        if (!chatbotId || !chatbotSeed) {
            showResult('error', '<?php _e('Please fill in both Chatbot ID and Private Key', 'hyperleap-chatbots'); ?>');
            return;
        }

        setButtonLoading(validateBtn, true);
        
        $.ajax({
            url: hyperleapChatbots.ajaxurl,
            type: 'POST',
            data: {
                action: 'hyperleap_validate_chatbot',
                nonce: hyperleapChatbots.nonce,
                chatbot_id: chatbotId,
                chatbot_seed: chatbotSeed
            },
            success: function(response) {
                if (response.success) {
                    validationPassed = true;
                    installBtn.prop('disabled', false);
                    showResult('success', response.data.message + (response.data.chatbot_info ? ` (${response.data.chatbot_info.name})` : ''));
                } else {
                    validationPassed = false;
                    installBtn.prop('disabled', true);
                    showResult('error', response.data);
                }
            },
            error: function() {
                validationPassed = false;
                installBtn.prop('disabled', true);
                showResult('error', '<?php _e('Connection error. Please try again.', 'hyperleap-chatbots'); ?>');
            },
            complete: function() {
                setButtonLoading(validateBtn, false);
            }
        });
    });

    form.on('submit', function(e) {
        e.preventDefault();
        
        if (!validationPassed) {
            showResult('error', '<?php _e('Please validate your credentials first', 'hyperleap-chatbots'); ?>');
            return;
        }

        const chatbotId = $('#quick-chatbot-id').val().trim();
        const chatbotSeed = $('#quick-chatbot-seed').val().trim();
        
        setButtonLoading(installBtn, true);
        
        $.ajax({
            url: hyperleapChatbots.ajaxurl,
            type: 'POST',
            data: {
                action: 'hyperleap_quick_install',
                nonce: hyperleapChatbots.nonce,
                chatbot_id: chatbotId,
                chatbot_seed: chatbotSeed
            },
            success: function(response) {
                if (response.success) {
                    showResult('success', response.data.message);
                    
                    setTimeout(function() {
                        if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        }
                    }, 2000);
                } else {
                    showResult('error', response.data);
                }
            },
            error: function() {
                showResult('error', '<?php _e('Installation failed. Please try again.', 'hyperleap-chatbots'); ?>');
            },
            complete: function() {
                setButtonLoading(installBtn, false);
            }
        });
    });

    // Reset validation when inputs change
    $('#quick-chatbot-id, #quick-chatbot-seed').on('input', function() {
        validationPassed = false;
        installBtn.prop('disabled', true);
        resultDiv.hide();
    });

    function setButtonLoading(button, loading) {
        const text = button.find('.btn-text');
        const spinner = button.find('.btn-spinner');
        
        if (loading) {
            button.prop('disabled', true);
            text.hide();
            spinner.show();
        } else {
            button.prop('disabled', false);
            text.show();
            spinner.hide();
        }
    }

    function showResult(type, message) {
        resultDiv
            .removeClass('hyperleap-alert-success hyperleap-alert-error')
            .addClass('hyperleap-alert-' + type)
            .html(message)
            .fadeIn();
    }
});
</script>