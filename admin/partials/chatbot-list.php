<?php
/**
 * WordPress native chatbot list with enhanced functionality
 */

if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('AI Chatbots', 'hyperleap-chatbots'); ?>
        <span class="title-count theme-count"><?php echo count($chatbots); ?></span>
    </h1>
    
    <a href="<?php echo admin_url('admin.php?page=hyperleap-chatbots-install'); ?>" class="page-title-action">
        <?php _e('Quick Install', 'hyperleap-chatbots'); ?>
    </a>
    
    <a href="<?php echo admin_url('admin.php?page=hyperleap-chatbots&action=new'); ?>" class="page-title-action">
        <?php _e('Add New', 'hyperleap-chatbots'); ?>
    </a>
    
    <hr class="wp-header-end">

    <?php if (empty($chatbots)): ?>
        <div class="notice notice-info">
            <p><?php _e('No chatbots found. Get started by installing your first AI chatbot!', 'hyperleap-chatbots'); ?></p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=hyperleap-chatbots-install'); ?>" class="button button-primary">
                    <?php _e('Quick Install Chatbot', 'hyperleap-chatbots'); ?>
                </a>
                <a href="https://hyperleap.ai/docs" class="button button-secondary" target="_blank">
                    <?php _e('View Documentation', 'hyperleap-chatbots'); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <span class="displaying-num">
                    <?php
                    $active_count = count(array_filter($chatbots, function($bot) {
                        if (isset($bot['enabled'])) {
                            return $bot['enabled'];
                        } else {
                            return isset($bot['status']) && $bot['status'] === 'active';
                        }
                    }));
                    
                    printf(__('%s total, %s active', 'hyperleap-chatbots'), 
                        '<strong>' . count($chatbots) . '</strong>',
                        '<strong>' . $active_count . '</strong>'
                    );
                    ?>
                </span>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="column-name"><?php _e('Chatbot Name', 'hyperleap-chatbots'); ?></th>
                    <th class="column-chatbot-id"><?php _e('Chatbot ID', 'hyperleap-chatbots'); ?></th>
                    <th class="column-placement"><?php _e('Placement', 'hyperleap-chatbots'); ?></th>
                    <th class="column-status"><?php _e('Status', 'hyperleap-chatbots'); ?></th>
                    <th class="column-actions"><?php _e('Actions', 'hyperleap-chatbots'); ?></th>
                </tr>
            </thead>
            <tbody>
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
                        $updated_at = date('Y-m-d H:i:s');
                    }
                    
                    $edit_url = admin_url('admin.php?page=hyperleap-chatbots&action=edit&id=' . urlencode($chatbot['id']));
                ?>
                    <tr class="<?php echo $is_enabled ? 'active' : 'inactive'; ?>">
                        <td class="column-name">
                            <strong>
                                <a href="<?php echo esc_url($edit_url); ?>">
                                    <?php echo esc_html($chatbot_name); ?>
                                </a>
                            </strong>
                            <?php if ($updated_at): ?>
                                <div class="row-actions">
                                    <small class="description">
                                        <?php printf(__('Updated %s ago', 'hyperleap-chatbots'), 
                                            human_time_diff(strtotime($updated_at), current_time('timestamp'))); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </td>
                        
                        <td class="column-chatbot-id">
                            <code><?php echo esc_html($chatbot_id); ?></code>
                        </td>
                        
                        <td class="column-placement">
                            <?php
                            switch ($placement) {
                                case 'sitewide':
                                    echo '<span class="dashicons dashicons-admin-site-alt3"></span> ';
                                    _e('Site-wide', 'hyperleap-chatbots');
                                    break;
                                case 'specific':
                                    echo '<span class="dashicons dashicons-admin-page"></span> ';
                                    printf(_n('%d page', '%d pages', count($pages), 'hyperleap-chatbots'), count($pages));
                                    break;
                                case 'homepage':
                                    echo '<span class="dashicons dashicons-admin-home"></span> ';
                                    _e('Homepage only', 'hyperleap-chatbots');
                                    break;
                                default:
                                    echo esc_html($placement);
                            }
                            ?>
                        </td>
                        
                        <td class="column-status">
                            <label class="switch">
                                <input type="checkbox" 
                                       class="status-toggle hyperleap-toggle-chatbot" 
                                       data-id="<?php echo esc_attr($chatbot['id']); ?>"
                                       data-enabled="<?php echo $is_enabled ? 'true' : 'false'; ?>"
                                       <?php checked($is_enabled); ?>>
                                <span class="slider round"></span>
                            </label>
                            <span class="status-text">
                                <?php echo $is_enabled ? __('Active', 'hyperleap-chatbots') : __('Inactive', 'hyperleap-chatbots'); ?>
                            </span>
                        </td>
                        
                        <td class="column-actions">
                            <a href="<?php echo esc_url($edit_url); ?>" class="button button-small">
                                <?php _e('Edit', 'hyperleap-chatbots'); ?>
                            </a>
                            <button type="button" 
                                    class="button button-small button-link-delete hyperleap-delete-chatbot" 
                                    data-id="<?php echo esc_attr($chatbot['id']); ?>">
                                <?php _e('Delete', 'hyperleap-chatbots'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="tablenav bottom">
            <div class="alignleft actions">
                <span class="displaying-num">
                    <?php printf(__('%s items', 'hyperleap-chatbots'), count($chatbots)); ?>
                </span>
            </div>
        </div>
        
    <?php endif; ?>
</div>

<style>
/* Toggle Switch Styles */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    vertical-align: middle;
    margin-right: 8px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: #0073aa;
}

input:focus + .slider {
    box-shadow: 0 0 1px #0073aa;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.slider.round {
    border-radius: 24px;
}

.slider.round:before {
    border-radius: 50%;
}

.status-text {
    font-size: 12px;
    color: #646970;
    vertical-align: middle;
}

tr.active .status-text {
    color: #00a32a;
    font-weight: 600;
}

.column-placement .dashicons {
    vertical-align: middle;
    margin-right: 4px;
    color: #646970;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    
    // Simple fallback AJAX configuration
    const ajaxConfig = {
        url: ajaxurl,
        nonce: '<?php echo wp_create_nonce('hyperleap_chatbots_nonce'); ?>'
    };
    
    // Toggle chatbot status
    $('.hyperleap-toggle-chatbot').on('change', function() {
        const checkbox = $(this);
        const chatbotId = checkbox.data('id');
        const newEnabled = checkbox.is(':checked');
        const row = checkbox.closest('tr');
        const statusText = row.find('.status-text');
        
        // Disable all checkboxes during update
        $('.hyperleap-toggle-chatbot').prop('disabled', true);
        
        $.ajax({
            url: ajaxConfig.url,
            type: 'POST',
            data: {
                action: 'hyperleap_toggle_chatbot',
                nonce: ajaxConfig.nonce,
                id: chatbotId,
                enabled: newEnabled
            },
            success: function(response) {
                if (response.success) {
                    // Update UI
                    if (newEnabled) {
                        row.removeClass('inactive').addClass('active');
                        statusText.text('<?php _e('Active', 'hyperleap-chatbots'); ?>');
                    } else {
                        row.removeClass('active').addClass('inactive');
                        statusText.text('<?php _e('Inactive', 'hyperleap-chatbots'); ?>');
                    }
                    
                    checkbox.data('enabled', newEnabled);
                    
                    // Show success message
                    showAdminNotice('success', response.data.message || '<?php _e('Status updated successfully', 'hyperleap-chatbots'); ?>');
                } else {
                    // Revert checkbox state
                    checkbox.prop('checked', !newEnabled);
                    showAdminNotice('error', response.data || '<?php _e('Failed to update status', 'hyperleap-chatbots'); ?>');
                }
            },
            error: function() {
                // Revert checkbox state
                checkbox.prop('checked', !newEnabled);
                showAdminNotice('error', '<?php _e('Connection error. Please try again.', 'hyperleap-chatbots'); ?>');
            },
            complete: function() {
                // Re-enable all checkboxes
                $('.hyperleap-toggle-chatbot').prop('disabled', false);
            }
        });
    });
    
    // Delete chatbot
    $('.hyperleap-delete-chatbot').on('click', function() {
        const button = $(this);
        const chatbotId = button.data('id');
        const row = button.closest('tr');
        const chatbotName = row.find('.column-name strong a').text().trim();
        
        if (!confirm('<?php _e('Are you sure you want to delete', 'hyperleap-chatbots'); ?> "' + chatbotName + '"?\n\n<?php _e('This action cannot be undone.', 'hyperleap-chatbots'); ?>')) {
            return;
        }
        
        button.prop('disabled', true).text('<?php _e('Deleting...', 'hyperleap-chatbots'); ?>');
        
        $.ajax({
            url: ajaxConfig.url,
            type: 'POST',
            data: {
                action: 'hyperleap_delete_chatbot',
                nonce: ajaxConfig.nonce,
                id: chatbotId
            },
            success: function(response) {
                if (response.success) {
                    row.fadeOut(400, function() {
                        $(this).remove();
                        
                        // Reload if no chatbots left
                        if ($('tbody tr').length === 0) {
                            location.reload();
                        } else {
                            updateItemCount();
                        }
                    });
                    
                    showAdminNotice('success', '<?php _e('Chatbot deleted successfully.', 'hyperleap-chatbots'); ?>');
                } else {
                    showAdminNotice('error', response.data || '<?php _e('Failed to delete chatbot.', 'hyperleap-chatbots'); ?>');
                }
            },
            error: function() {
                showAdminNotice('error', '<?php _e('Connection error. Please try again.', 'hyperleap-chatbots'); ?>');
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Delete', 'hyperleap-chatbots'); ?>');
            }
        });
    });
    
    function showAdminNotice(type, message) {
        const notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');
        $('.wrap h1').after(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
        
        // Handle manual dismiss
        notice.on('click', '.notice-dismiss', function() {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        });
    }
    
    function updateItemCount() {
        const totalCount = $('tbody tr').length;
        const activeCount = $('tbody tr.active').length;
        
        $('.displaying-num').html(
            '<?php printf(__('%s total, %s active', 'hyperleap-chatbots'), 
                "<strong>' + totalCount + '</strong>',
                '<strong>' + activeCount + '</strong>'); ?>'
        );
        
        $('.title-count').text(totalCount);
    }
});
</script>