<?php
/**
 * All admin menu related functionality of the plugin.
 */
class Website_Chatbots_Admin_Menu {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, WEBSITE_CHATBOTS_PLUGIN_URL . 'assets/css/admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, WEBSITE_CHATBOTS_PLUGIN_URL . 'assets/js/admin.js', array(), $this->version, true);
        //TODO: websiteChatbotsAdmin -> pluginprefix_ajax_obj object name
        wp_localize_script($this->plugin_name, 'websiteChatbotsAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('website_chatbots_nonce'),
            'listUrl' => admin_url('admin.php?page=website-chatbots')
        ));
        
        // Enqueue Select2
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);
    }

    public function add_plugin_admin_menu() {
        //add_menu_page(
        //title
        //menu title
        //capability
        //menu_slug
        //callback
        //icon_url
        //position
        //)
        //add_submenu_page(
        //parent_slug
        //page_title
        //menu_title
        //capability
        //menu_slug
        //callback
        //position
        //)
        add_menu_page(
            'Website Chatbots', 
            'Chatbots',
            'manage_options',
            'website-chatbots',
            array($this, 'display_plugin_admin_page'),
            'dashicons-format-chat',
            30
        );

        add_submenu_page(
            'website-chatbots',
            'Add New Chatbot',
            'Add New',
            'manage_options',
            'website-chatbots-new',
            array($this, 'display_add_new_page')
        );
    }

    public function display_plugin_admin_page() {
        // Only handle list and edit views here
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $this->display_edit_page($_GET['id']);
        } else {
            $this->display_list_page();
        }
    }

    public function display_list_page() {
        require_once WEBSITE_CHATBOTS_PLUGIN_DIR . 'admin/partials/chatbot-list.php';
    }

    public function display_add_new_page() {
        // Empty data for new chatbot
        $chatbot = array(
            'id' => '',
            'chatbot_id' => '',
            'private_key' => '',
            'chatbot_name' => '',
            'location' => 'sitewide',
            'pages' => array(),
            'note' => '',
            'status' => 'inactive' // Add default status
        );
        
        $is_new = true;
        require_once WEBSITE_CHATBOTS_PLUGIN_DIR . 'admin/partials/chatbot-edit.php';
    }

    public function display_edit_page($id) {
        $chatbots = get_option('website_chatbots_data', array());
        
        if (!isset($chatbots[$id])) {
            wp_die('Chatbot not found');
        }
        
        $chatbot = $chatbots[$id];
        $is_new = false;
        require_once WEBSITE_CHATBOTS_PLUGIN_DIR . 'admin/partials/chatbot-edit.php';
    }

    public function register_settings() {
        register_setting('website_chatbots_options', 'website_chatbots_data');
    }

    public function save_chatbot() {
        check_ajax_referer('website_chatbots_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        // Get form data
        $chatbot_id = sanitize_text_field($_POST['chatbot_id']);
        $private_key = sanitize_text_field($_POST['private_key']);
        $chatbot_name = sanitize_text_field($_POST['chatbot_name']);
        $location = sanitize_text_field($_POST['location']);
        $pages = isset($_POST['pages']) ? array_map('intval', $_POST['pages']) : array();
        $note = sanitize_textarea_field($_POST['note']);
        $is_new = isset($_POST['is_new']) && $_POST['is_new'] === '1';
        
        // Get existing chatbots
        $chatbots = get_option('website_chatbots_data', array());

        if ($is_new) {
            // Generate new unique ID for new chatbot
            $id = uniqid('chatbot_');
            while (isset($chatbots[$id])) {
                $id = uniqid('chatbot_');
            }
        } else {
            $id = sanitize_text_field($_POST['id']);
            if (!isset($chatbots[$id])) {
                wp_send_json_error('Chatbot not found');
                return;
            }
        }
        $chatbot_data = array(
            'id' => $id,
            'chatbot_id' => $chatbot_id,
            'private_key' => $private_key,
            'chatbot_name' => $chatbot_name,
            'location' => $location,
            'pages' => $pages,
            'note' => $note,
            'status' => 'inactive'
        );

        if (!$is_new && isset($chatbots[$id]['status'])) {
            $chatbot_data['status'] = $chatbots[$id]['status'];
        }

        // Save/update chatbot
        $chatbots[$id] = $chatbot_data;
        update_option('website_chatbots_data', $chatbots);

        wp_send_json_success(array(
            'id' => $id,
            'message' => $is_new ? 'Chatbot created successfully' : 'Chatbot updated successfully'
        ));
    }

    public function delete_chatbot() {
        check_ajax_referer('website_chatbots_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $id = sanitize_text_field($_POST['id']);
        $chatbots = get_option('website_chatbots_data', array());

        if (isset($chatbots[$id])) {
            unset($chatbots[$id]);
            update_option('website_chatbots_data', $chatbots);
            wp_send_json_success();
        }

        wp_send_json_error('Chatbot not found');
    }

    public function validate_chatbot() {
        check_ajax_referer('website_chatbots_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $chatbot_id = sanitize_text_field($_POST['chatbot_id']);
        $current_id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        
        $chatbots = get_option('website_chatbots_data', array());

        foreach ($chatbots as $id => $chatbot) {
            if ($chatbot['chatbot_id'] === $chatbot_id && $id !== $current_id) {
                wp_send_json_error('A chatbot with this ID already exists.');
                return;
            }
        }

        wp_send_json_success();
    }

    public function update_chatbot_status() {
        check_ajax_referer('website_chatbots_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }
        $id = sanitize_text_field($_POST['id']);
        $status = sanitize_text_field($_POST['status']);

        // Get existing chatbots
        $chatbots = get_option('website_chatbots_data', array());

        if (!isset($chatbots[$id])) {
            wp_send_json_error('Chatbot not found');
            return;
        }

        // Toggle status of other chatbots
        foreach ($chatbots as $chatbot_id => &$chatbot) {
            $chatbot['status'] = ($chatbot_id === $id && $status === 'active') ? 'active' : 'inactive';
        }

        //save updated chatbots
        update_option('website_chatbots_data', $chatbots);
        wp_send_json_success();
    }
}