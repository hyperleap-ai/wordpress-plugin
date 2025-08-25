<?php
/**
 * Enhanced chatbot list with modern UI
 */

if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap hyperleap-admin">
    <div class="hyperleap-header">
        <h1 class="hyperleap-title">
            <svg class="hyperleap-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
            </svg>
            <?php _e('AI Chatbots', 'hyperleap-chatbots'); ?>
        </h1>
        
        <div class="hyperleap-header-actions">
            <a href="<?php echo admin_url('admin.php?page=hyperleap-chatbots-install'); ?>" 
               class="hyperleap-btn hyperleap-btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14m-7-7h14"/>
                </svg>
                <?php _e('Quick Install', 'hyperleap-chatbots'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=hyperleap-chatbots&action=new'); ?>" 
               class="hyperleap-btn hyperleap-btn-secondary">
                <?php _e('Advanced Setup', 'hyperleap-chatbots'); ?>
            </a>
        </div>
    </div>

    <?php 
    // Debug: Check the data structure
    if (!empty($chatbots)) {
        error_log('Hyperleap Chatbots Debug - Data structure: ' . print_r(array_keys(reset($chatbots)), true));
    }
    
    if (empty($chatbots)): ?>
        <div class="hyperleap-empty-state">
            <div class="hyperleap-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <h2><?php _e('No chatbots yet', 'hyperleap-chatbots'); ?></h2>
            <p><?php _e('Get started by installing your first AI chatbot in under 60 seconds.', 'hyperleap-chatbots'); ?></p>
            <div class="hyperleap-empty-actions">
                <a href="<?php echo admin_url('admin.php?page=hyperleap-chatbots-install'); ?>" 
                   class="hyperleap-btn hyperleap-btn-primary hyperleap-btn-large">
                    <?php _e('Quick Install Chatbot', 'hyperleap-chatbots'); ?>
                </a>
                <a href="https://hyperleap.ai/docs" target="_blank" 
                   class="hyperleap-btn hyperleap-btn-ghost">
                    <?php _e('View Documentation', 'hyperleap-chatbots'); ?>
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="hyperleap-stats">
            <?php 
            $active_count = count(array_filter($chatbots, function($bot) { 
                // Handle both old and new data formats
                if (isset($bot['enabled'])) {
                    return $bot['enabled'];
                } else {
                    return isset($bot['status']) && $bot['status'] === 'active';
                }
            }));
            $total_count = count($chatbots);
            ?>
            <div class="hyperleap-stat">
                <div class="hyperleap-stat-value"><?php echo $active_count; ?></div>
                <div class="hyperleap-stat-label"><?php _e('Active', 'hyperleap-chatbots'); ?></div>
            </div>
            <div class="hyperleap-stat">
                <div class="hyperleap-stat-value"><?php echo $total_count; ?></div>
                <div class="hyperleap-stat-label"><?php _e('Total', 'hyperleap-chatbots'); ?></div>
            </div>
        </div>

        <div class="hyperleap-chatbots-grid">
            <?php foreach ($chatbots as $chatbot): 
                // Handle both old and new data formats
                $is_enabled = false;
                $chatbot_id = '';
                $chatbot_name = '';
                $placement = 'sitewide';
                $pages = array();
                $updated_at = '';
                
                // New format
                if (isset($chatbot['enabled'])) {
                    $is_enabled = $chatbot['enabled'];
                    $chatbot_id = $chatbot['chatbot_id'] ?? '';
                    $chatbot_name = $chatbot['name'] ?? 'Unnamed Chatbot';
                    $placement = $chatbot['placement'] ?? 'sitewide';
                    $pages = $chatbot['pages'] ?? array();
                    $updated_at = $chatbot['updated_at'] ?? '';
                } 
                // Old format fallback
                else {
                    $is_enabled = isset($chatbot['status']) && $chatbot['status'] === 'active';
                    $chatbot_id = $chatbot['chatbot_id'] ?? '';
                    $chatbot_name = $chatbot['chatbot_name'] ?? 'Unnamed Chatbot';
                    $placement = $chatbot['location'] === 'specific' ? 'specific' : 'sitewide';
                    $pages = $chatbot['pages'] ?? array();
                    $updated_at = date('Y-m-d H:i:s'); // Fallback to current time
                }
            ?>
                <div class="hyperleap-chatbot-card <?php echo $is_enabled ? 'active' : 'inactive'; ?>" 
                     data-chatbot-id="<?php echo esc_attr($chatbot['id']); ?>">
                    
                    <div class="hyperleap-chatbot-header">
                        <div class="hyperleap-chatbot-status">
                            <div class="hyperleap-status-indicator <?php echo $is_enabled ? 'active' : 'inactive'; ?>"></div>
                            <span class="hyperleap-status-text">
                                <?php echo $is_enabled ? __('Active', 'hyperleap-chatbots') : __('Inactive', 'hyperleap-chatbots'); ?>
                            </span>
                        </div>
                        
                        <div class="hyperleap-chatbot-actions">
                            <button class="hyperleap-btn-icon hyperleap-toggle-chatbot" 
                                    data-id="<?php echo esc_attr($chatbot['id']); ?>"
                                    data-enabled="<?php echo $is_enabled ? 'true' : 'false'; ?>"
                                    title="<?php echo $is_enabled ? __('Disable', 'hyperleap-chatbots') : __('Enable', 'hyperleap-chatbots'); ?>">
                                <?php if ($is_enabled): ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="6" y="4" width="4" height="16"/>
                                        <rect x="14" y="4" width="4" height="16"/>
                                    </svg>
                                <?php else: ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="5,3 19,12 5,21"/>
                                    </svg>
                                <?php endif; ?>
                            </button>
                            
                            <a href="<?php echo admin_url('admin.php?page=hyperleap-chatbots&action=edit&id=' . urlencode($chatbot['id'])); ?>" 
                               class="hyperleap-btn-icon" title="<?php _e('Edit', 'hyperleap-chatbots'); ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>
                            </a>
                            
                            <button class="hyperleap-btn-icon hyperleap-delete-chatbot" 
                                    data-id="<?php echo esc_attr($chatbot['id']); ?>"
                                    title="<?php _e('Delete', 'hyperleap-chatbots'); ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3,6 5,6 21,6"/>
                                    <path d="M19,6V20a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/>
                                    <line x1="10" y1="11" x2="10" y2="17"/>
                                    <line x1="14" y1="11" x2="14" y2="17"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="hyperleap-chatbot-content">
                        <h3 class="hyperleap-chatbot-name"><?php echo esc_html($chatbot_name); ?></h3>
                        <div class="hyperleap-chatbot-id"><?php echo esc_html($chatbot_id); ?></div>
                        
                        <div class="hyperleap-chatbot-placement">
                            <span class="hyperleap-placement-badge hyperleap-placement-<?php echo esc_attr($placement); ?>">
                                <?php
                                switch ($placement) {
                                    case 'sitewide':
                                        _e('Site-wide', 'hyperleap-chatbots');
                                        break;
                                    case 'specific':
                                        printf(_n('%d page', '%d pages', count($pages), 'hyperleap-chatbots'), count($pages));
                                        break;
                                    case 'homepage':
                                        _e('Homepage only', 'hyperleap-chatbots');
                                        break;
                                    default:
                                        echo esc_html($placement);
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="hyperleap-chatbot-footer">
                        <div class="hyperleap-chatbot-dates">
                            <small><?php 
                            if ($updated_at) {
                                printf(__('Updated %s', 'hyperleap-chatbots'), 
                                    human_time_diff(strtotime($updated_at), current_time('timestamp')) . ' ' . __('ago', 'hyperleap-chatbots'));
                            } else {
                                _e('Recently created', 'hyperleap-chatbots');
                            }
                            ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    
    // Debug: Check if hyperleapChatbots is loaded
    if (typeof hyperleapChatbots === 'undefined') {
        console.error('hyperleapChatbots object not found. AJAX will not work.');
        return;
    }
    
    console.log('Hyperleap Admin Script Loaded', hyperleapChatbots);
    
    $('.hyperleap-toggle-chatbot').on('click', function() {
        const button = $(this);
        const chatbotId = button.data('id');
        const currentEnabled = button.data('enabled') === 'true';
        const newEnabled = !currentEnabled;
        
        button.prop('disabled', true);
        
        $.ajax({
            url: hyperleapChatbots.ajaxurl,
            type: 'POST',
            data: {
                action: 'hyperleap_toggle_chatbot',
                nonce: hyperleapChatbots.nonce,
                id: chatbotId,
                enabled: newEnabled
            },
            success: function(response) {
                if (response.success) {
                    // Update UI
                    const card = button.closest('.hyperleap-chatbot-card');
                    const statusIndicator = card.find('.hyperleap-status-indicator');
                    const statusText = card.find('.hyperleap-status-text');
                    
                    if (newEnabled) {
                        card.removeClass('inactive').addClass('active');
                        statusIndicator.removeClass('inactive').addClass('active');
                        statusText.text('<?php _e('Active', 'hyperleap-chatbots'); ?>');
                        button.attr('title', '<?php _e('Disable', 'hyperleap-chatbots'); ?>');
                        button.html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>');
                    } else {
                        card.removeClass('active').addClass('inactive');
                        statusIndicator.removeClass('active').addClass('inactive');
                        statusText.text('<?php _e('Inactive', 'hyperleap-chatbots'); ?>');
                        button.attr('title', '<?php _e('Enable', 'hyperleap-chatbots'); ?>');
                        button.html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5,3 19,12 5,21"/></svg>');
                    }
                    
                    button.data('enabled', newEnabled);
                    
                    // Show success message
                    showNotice('success', response.data.message);
                } else {
                    showNotice('error', response.data);
                }
            },
            error: function() {
                showNotice('error', '<?php _e('Failed to update chatbot status', 'hyperleap-chatbots'); ?>');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });
    
    $('.hyperleap-delete-chatbot').on('click', function() {
        const button = $(this);
        const chatbotId = button.data('id');
        const card = button.closest('.hyperleap-chatbot-card');
        const chatbotName = card.find('.hyperleap-chatbot-name').text();
        
        if (!confirm('<?php _e('Are you sure you want to delete', 'hyperleap-chatbots'); ?> "' + chatbotName + '"?')) {
            return;
        }
        
        button.prop('disabled', true);
        
        $.ajax({
            url: hyperleapChatbots.ajaxurl,
            type: 'POST',
            data: {
                action: 'hyperleap_delete_chatbot',
                nonce: hyperleapChatbots.nonce,
                id: chatbotId
            },
            success: function(response) {
                if (response.success) {
                    card.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if no chatbots left
                        if ($('.hyperleap-chatbot-card').length === 0) {
                            location.reload();
                        }
                    });
                    
                    showNotice('success', response.data);
                } else {
                    showNotice('error', response.data);
                }
            },
            error: function() {
                showNotice('error', '<?php _e('Failed to delete chatbot', 'hyperleap-chatbots'); ?>');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });
    
    function showNotice(type, message) {
        const notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.hyperleap-header').after(notice);
        
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
});
</script>