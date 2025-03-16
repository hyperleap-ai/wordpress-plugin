<?php
/**
 * Chatbot Edit Page
 * 
 */

if (!defined('WPINC')) {
    die;
}

if (!isset($chatbot) || !isset($is_new)) {
    wp_die('Invalid access');
}

$page_title = $is_new ? 'Add New Chatbot' : 'Edit Chatbot';
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html($page_title); ?></h1>
    <hr class="wp-header-end">

    <form id="chatbot-form" class="chatbot-form" method="post" action="">
        <?php wp_nonce_field('website_chatbots_nonce', 'website_chatbots_nonce'); ?>
        <input type="hidden" name="id" value="<?php echo esc_attr($chatbot['id']); ?>">
        <input type="hidden" name="is_new" value="<?php echo $is_new ? '1' : '0'; ?>">
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="chatbot_id">Chatbot ID</label></th>
                <td>
                    <input name="chatbot_id" type="text" id="chatbot_id" value="<?php echo esc_attr($chatbot['chatbot_id']); ?>" class="regular-text" required>
                    <p class="description">Please enter your Chatbot ID, which can be found in the <a href="https://studio.hyperleapai.com/website-chatbots" target="_blank">Studio</a>.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="private_key">Private Key</label></th>
                <td>
                    <input name="private_key" type="password" id="private_key" value="<?php echo esc_attr($chatbot['private_key']); ?>" class="regular-text" required>
                    <p class="description">Please enter the Private Key, which can be found in the <a href="https://studio.hyperleapai.com/website-chatbots" target="_blank">Studio</a>.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="location">Display Location</label></th>
                <td>
                    <select name="location" id="location">
                        <option value="sitewide" <?php selected($chatbot['location'], 'sitewide'); ?>>Sitewide</option>
                        <option value="specific" <?php selected($chatbot['location'], 'specific'); ?>>Specific Pages</option>
                    </select>
                </td>
            </tr>
            <tr class="page-selection<?php echo $chatbot['location'] === 'specific' ? ' show' : ''; ?>">
                <th scope="row"><label for="pages">Select Pages</label></th>
                <td>
                    <select name="pages[]" id="pages" multiple="multiple" style="min-width: 300px;">
                        <?php
                        $pages = get_pages();
                        foreach ($pages as $page) {
                            $selected = in_array($page->ID, $chatbot['pages']) ? 'selected' : '';
                            echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
                        }
                        ?>
                    </select> 
                    <p class="description">Select the pages where you want to display this chatbot.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="note">Note</label></th>
                <td>
                    <textarea name="note" id="note" class="large-text" rows="3"><?php echo esc_textarea($chatbot['note']); ?></textarea>
                    <p class="description">Optional: Add a note to help you identify this chatbot.</p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary" id="save-chatbot">
                <?php echo $is_new ? 'Add Chatbot' : 'Update Chatbot'; ?>
            </button>
            <a href="<?php echo admin_url('admin.php?page=website-chatbots'); ?>" class="button">Cancel</a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize select2 with proper multi-select configuration
    $('#pages').select2({
        width: '100%',
        multiple: true,
        placeholder: 'Select pages...',
        allowClear: true,
        closeOnSelect: false,
        dropdownParent: $('.chatbot-form')
    });

    // Toggle page selection visibility based on location
    $('#location').on('change', function() {
        var isSpecific = $(this).val() === 'specific';
        $('.page-selection').toggleClass('show', isSpecific);
    });

    // Trigger initial state
    $('#location').trigger('change');
});
</script>