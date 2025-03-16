<?php
if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline">AI Chatbots</h1>
    <a href="<?php echo admin_url('admin.php?page=website-chatbots-new'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th class="column-name">Chatbot Name</th>
                <th class="column-chatbot-id">Chatbot ID</th>
                <th class="column-private-key">Private Key</th>
                <th class="column-location">Location</th>
                <th class="column-note">Note</th>
                <th class="column-actions">Actions</th>
                <th class="column-status">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $chatbots = get_option('website_chatbots_data', array());
            if (empty($chatbots)) {
                echo '<tr><td colspan="7">No chatbots found. <a href="' . admin_url('admin.php?page=website-chatbots-new') . '">Add your first chatbot</a>.</td></tr>';
            } else {
                foreach ($chatbots as $chatbot) {
                    $edit_url = add_query_arg(
                        array(
                            'page' => 'website-chatbots',
                            'action' => 'edit',
                            'id' => $chatbot['id']
                        ),
                        admin_url('admin.php')
                    );
                    $is_active = isset($chatbot['status']) && $chatbot['status'] === 'active';
                    ?>
                    <tr>
                        <td class="column-name"><?php echo esc_html(isset($chatbot['chatbot_name']) ? $chatbot['chatbot_name'] : 'N/A'); ?></td>
                        <td class="column-chatbot-id"><?php echo esc_html($chatbot['chatbot_id']); ?></td>
                        <td class="column-private-key"><?php echo esc_html(substr($chatbot['private_key'], 0, 10) . '...'); ?></td>
                        <td class="column-location"><?php 
                            echo esc_html($chatbot['location'] === 'sitewide' ? 'Sitewide' : 'Specific Pages');
                            if ($chatbot['location'] === 'specific' && !empty($chatbot['pages'])) {
                                $page_titles = array();
                                foreach ($chatbot['pages'] as $page_id) {
                                    $page_titles[] = get_the_title($page_id);
                                }
                                echo '<br><small>' . esc_html(implode(', ', $page_titles)) . '</small>';
                            }
                        ?></td>
                        <td class="column-note"><?php echo esc_html($chatbot['note']); ?></td>
                        <td class="column-actions">
                            <a href="<?php echo esc_url($edit_url); ?>" class="button button-small">Edit</a>
                            <button type="button" class="button button-small button-link-delete delete-chatbot" data-id="<?php echo esc_attr($chatbot['id']); ?>">Delete</button>
                        </td>
                        <td class="column-status">
                            <label class="switch">
                                <input type="checkbox" 
                                       class="status-toggle" 
                                       data-id="<?php echo esc_attr($chatbot['id']); ?>"
                                       <?php checked($is_active); ?>>
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<style>
/* Toggle Switch Styles */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
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
    background-color: #2271b1;
}

input:focus + .slider {
    box-shadow: 0 0 1px #2271b1;
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

/* Table column width adjustments */
.wp-list-table th:first-child,
.wp-list-table td:first-child {
    width: 80px;
    text-align: center;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.delete-chatbot').on('click', function() {
        if (!confirm('Are you sure you want to delete this chatbot?')) {
            return;
        }
        
        var button = $(this);
        var id = button.data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_chatbot',
                id: id,
                nonce: websiteChatbotsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    button.closest('tr').fadeOut(400, function() {
                        $(this).remove();
                        if ($('tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert('Error deleting chatbot');
                }
            }
        });
    });

    // Status toggle handler
    $('.status-toggle').on('change', function() {
        var checkbox = $(this);
        var chatbotId = checkbox.data('id');

        // Disable all checkboxes during the update
        $('.status-toggle').prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_chatbot_status',
                id: chatbotId,
                status: checkbox.prop('checked') ? 'active' : 'inactive',
                nonce: websiteChatbotsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Uncheck all other checkboxes
                    $('.status-toggle').not(checkbox).prop('checked', false);
                } else {
                    // Revert the change if there was an error
                    checkbox.prop('checked', !checkbox.prop('checked'));
                    alert('Error updating chatbot status');
                }
            },
            error: function() {
                // Revert the change on error
                checkbox.prop('checked', !checkbox.prop('checked'));
                alert('Error updating chatbot status');
            },
            complete: function() {
                // Re-enable all checkboxes
                $('.status-toggle').prop('disabled', false);
            }
        });
    });
});
</script>