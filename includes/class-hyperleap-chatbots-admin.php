<?php
/**
 * Enhanced admin functionality with modern UI and smooth UX
 */
class Hyperleap_Chatbots_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles($hook) {
        if (strpos($hook, 'hyperleap-chatbots') === false) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name . '-admin',
            HYPERLEAP_CHATBOTS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            $this->version,
            'all'
        );

        // Modern admin styles
        wp_enqueue_style(
            $this->plugin_name . '-modern',
            HYPERLEAP_CHATBOTS_PLUGIN_URL . 'assets/css/modern-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts($hook) {
        if (strpos($hook, 'hyperleap-chatbots') === false) {
            return;
        }

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            HYPERLEAP_CHATBOTS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            $this->version,
            true
        );

        // Enhanced admin JS
        wp_enqueue_script(
            $this->plugin_name . '-enhanced',
            HYPERLEAP_CHATBOTS_PLUGIN_URL . 'assets/js/enhanced-admin.js',
            array('jquery', $this->plugin_name . '-admin'),
            $this->version,
            true
        );

        wp_localize_script($this->plugin_name . '-enhanced', 'hyperleapChatbots', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hyperleap_chatbots_nonce'),
            'api_url' => HYPERLEAP_CHATBOTS_API_URL,
            'js_url' => HYPERLEAP_CHATBOTS_JS_URL,
            'strings' => array(
                'saving' => __('Saving...', 'hyperleap-chatbots'),
                'saved' => __('Saved successfully!', 'hyperleap-chatbots'),
                'error' => __('Error occurred', 'hyperleap-chatbots'),
                'validating' => __('Validating...', 'hyperleap-chatbots'),
                'confirm_delete' => __('Are you sure you want to delete this chatbot?', 'hyperleap-chatbots'),
                'quick_install' => __('Quick Install', 'hyperleap-chatbots'),
                'installing' => __('Installing...', 'hyperleap-chatbots')
            )
        ));
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            __('Hyperleap Chatbots', 'hyperleap-chatbots'),
            __('AI Chatbots', 'hyperleap-chatbots'),
            'manage_options',
            'hyperleap-chatbots',
            array($this, 'display_main_page'),
            'data:image/svg+xml;base64,' . base64_encode($this->get_menu_icon()),
            30
        );

        add_submenu_page(
            'hyperleap-chatbots',
            __('Quick Install', 'hyperleap-chatbots'),
            __('Quick Install', 'hyperleap-chatbots'),
            'manage_options',
            'hyperleap-chatbots-install',
            array($this, 'display_quick_install_page')
        );

        add_submenu_page(
            'hyperleap-chatbots',
            __('Settings', 'hyperleap-chatbots'),
            __('Settings', 'hyperleap-chatbots'),
            'manage_options',
            'hyperleap-chatbots-settings',
            array($this, 'display_settings_page')
        );
    }

    private function get_menu_icon() {
        return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 13.5c-.3 0-.5-.2-.5-.5s.2-.5.5-.5.5.2.5.5-.2.5-.5.5zm2.1-2.1c0 .3-.2.6-.5.6s-.5-.3-.5-.6c0-1.2.7-2.2 1.6-2.7.2-.1.3-.2.4-.4.1-.2.1-.4 0-.6-.1-.2-.3-.3-.5-.3s-.4.1-.5.3c0 .1 0 .1-.1.2-.4-.3-.9-.5-1.4-.5-1.4 0-2.5 1.1-2.5 2.5s1.1 2.5 2.5 2.5c.8 0 1.5-.4 1.9-1.1.1.7.7 1.1 1.4 1.1.8 0 1.5-.7 1.5-1.5 0-.4-.2-.8-.5-1z"/></svg>';
    }

    public function display_main_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        switch ($action) {
            case 'edit':
                $this->display_edit_page();
                break;
            case 'new':
                $this->display_new_page();
                break;
            default:
                $this->display_list_page();
                break;
        }
    }

    public function display_list_page() {
        $chatbots = get_option('hyperleap_chatbots_data', array());
        require_once HYPERLEAP_CHATBOTS_PLUGIN_DIR . 'admin/partials/chatbot-list.php';
    }

    public function display_edit_page() {
        $id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
        $chatbots = get_option('hyperleap_chatbots_data', array());
        
        if (!isset($chatbots[$id])) {
            wp_die(__('Chatbot not found.', 'hyperleap-chatbots'));
        }
        
        $chatbot = $chatbots[$id];
        $is_edit = true;
        require_once HYPERLEAP_CHATBOTS_PLUGIN_DIR . 'admin/partials/chatbot-form.php';
    }

    public function display_new_page() {
        $chatbot = array(
            'id' => '',
            'chatbot_id' => '',
            'chatbot_seed' => '',
            'name' => '',
            'enabled' => false,
            'placement' => 'sitewide',
            'pages' => array(),
            'exclude_pages' => array(),
            'settings' => array(
                'position' => 'bottom-right',
                'theme' => 'auto',
                'greeting_message' => '',
                'offline_message' => ''
            ),
            'created_at' => '',
            'updated_at' => ''
        );
        $is_edit = false;
        require_once HYPERLEAP_CHATBOTS_PLUGIN_DIR . 'admin/partials/chatbot-form.php';
    }

    public function display_quick_install_page() {
        require_once HYPERLEAP_CHATBOTS_PLUGIN_DIR . 'admin/partials/quick-install.php';
    }

    public function display_settings_page() {
        require_once HYPERLEAP_CHATBOTS_PLUGIN_DIR . 'admin/partials/settings.php';
    }

    public function register_settings() {
        register_setting('hyperleap_chatbots_options', 'hyperleap_chatbots_data');
        register_setting('hyperleap_chatbots_options', 'hyperleap_chatbots_settings');
    }

    public function save_chatbot() {
        check_ajax_referer('hyperleap_chatbots_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized access.', 'hyperleap-chatbots'));
        }

        $chatbot_data = $this->sanitize_chatbot_data($_POST);
        $chatbots = get_option('hyperleap_chatbots_data', array());

        if (empty($chatbot_data['id'])) {
            // New chatbot
            $chatbot_data['id'] = uniqid('hc_');
            $chatbot_data['created_at'] = current_time('mysql');
        }

        $chatbot_data['updated_at'] = current_time('mysql');
        $chatbots[$chatbot_data['id']] = $chatbot_data;

        update_option('hyperleap_chatbots_data', $chatbots);

        wp_send_json_success(array(
            'message' => __('Chatbot saved successfully!', 'hyperleap-chatbots'),
            'chatbot' => $chatbot_data
        ));
    }

    public function delete_chatbot() {
        check_ajax_referer('hyperleap_chatbots_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized access.', 'hyperleap-chatbots'));
        }

        $id = sanitize_text_field($_POST['id']);
        $chatbots = get_option('hyperleap_chatbots_data', array());

        if (isset($chatbots[$id])) {
            unset($chatbots[$id]);
            update_option('hyperleap_chatbots_data', $chatbots);
            wp_send_json_success(__('Chatbot deleted successfully.', 'hyperleap-chatbots'));
        }

        wp_send_json_error(__('Chatbot not found.', 'hyperleap-chatbots'));
    }

    public function toggle_chatbot() {
        check_ajax_referer('hyperleap_chatbots_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized access.', 'hyperleap-chatbots'));
        }

        $id = sanitize_text_field($_POST['id']);
        $enabled = filter_var($_POST['enabled'], FILTER_VALIDATE_BOOLEAN);
        $chatbots = get_option('hyperleap_chatbots_data', array());

        if (isset($chatbots[$id])) {
            $chatbots[$id]['enabled'] = $enabled;
            $chatbots[$id]['updated_at'] = current_time('mysql');
            update_option('hyperleap_chatbots_data', $chatbots);
            
            wp_send_json_success(array(
                'message' => $enabled ? __('Chatbot enabled.', 'hyperleap-chatbots') : __('Chatbot disabled.', 'hyperleap-chatbots'),
                'enabled' => $enabled
            ));
        }

        wp_send_json_error(__('Chatbot not found.', 'hyperleap-chatbots'));
    }

    public function validate_chatbot() {
        check_ajax_referer('hyperleap_chatbots_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized access.', 'hyperleap-chatbots'));
        }

        $chatbot_id = sanitize_text_field($_POST['chatbot_id']);
        $chatbot_seed = sanitize_text_field($_POST['chatbot_seed']);

        // Validate against Hyperleap API
        $api = new Hyperleap_Chatbots_API();
        $validation = $api->validate_chatbot($chatbot_id, $chatbot_seed);

        if ($validation['valid']) {
            wp_send_json_success(array(
                'message' => __('Chatbot credentials are valid!', 'hyperleap-chatbots'),
                'chatbot_info' => $validation['data']
            ));
        } else {
            wp_send_json_error($validation['message'] ?? __('Invalid chatbot credentials.', 'hyperleap-chatbots'));
        }
    }

    public function quick_install() {
        check_ajax_referer('hyperleap_chatbots_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized access.', 'hyperleap-chatbots'));
        }

        $chatbot_id = sanitize_text_field($_POST['chatbot_id']);
        $chatbot_seed = sanitize_text_field($_POST['chatbot_seed']);

        // Validate first
        $api = new Hyperleap_Chatbots_API();
        $validation = $api->validate_chatbot($chatbot_id, $chatbot_seed);

        if (!$validation['valid']) {
            wp_send_json_error($validation['message'] ?? __('Invalid chatbot credentials.', 'hyperleap-chatbots'));
        }

        // Create chatbot with smart defaults
        $chatbot_data = array(
            'id' => uniqid('hc_'),
            'chatbot_id' => $chatbot_id,
            'chatbot_seed' => $chatbot_seed,
            'name' => $validation['data']['name'] ?? 'My Chatbot',
            'enabled' => true,
            'placement' => 'sitewide',
            'pages' => array(),
            'exclude_pages' => array(),
            'settings' => array(
                'position' => 'bottom-right',
                'theme' => 'auto'
            ),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );

        $chatbots = get_option('hyperleap_chatbots_data', array());
        $chatbots[$chatbot_data['id']] = $chatbot_data;
        update_option('hyperleap_chatbots_data', $chatbots);

        wp_send_json_success(array(
            'message' => __('Chatbot installed successfully!', 'hyperleap-chatbots'),
            'chatbot' => $chatbot_data,
            'redirect' => admin_url('admin.php?page=hyperleap-chatbots')
        ));
    }

    public function show_admin_notices() {
        $chatbots = get_option('hyperleap_chatbots_data', array());
        $enabled_count = count(array_filter($chatbots, function($bot) { return $bot['enabled']; }));
        
        if (empty($chatbots) && strpos(get_current_screen()->id, 'hyperleap-chatbots') !== false) {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p>' . __('Welcome to Hyperleap AI Chatbots! Get started by using our Quick Install feature.', 'hyperleap-chatbots') . '</p>';
            echo '</div>';
        }
    }

    private function sanitize_chatbot_data($data) {
        return array(
            'id' => isset($data['id']) ? sanitize_text_field($data['id']) : '',
            'chatbot_id' => sanitize_text_field($data['chatbot_id'] ?? ''),
            'chatbot_seed' => sanitize_text_field($data['chatbot_seed'] ?? ''),
            'name' => sanitize_text_field($data['name'] ?? ''),
            'enabled' => filter_var($data['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'placement' => sanitize_text_field($data['placement'] ?? 'sitewide'),
            'pages' => array_map('intval', $data['pages'] ?? array()),
            'exclude_pages' => array_map('intval', $data['exclude_pages'] ?? array()),
            'settings' => array(
                'position' => sanitize_text_field($data['settings']['position'] ?? 'bottom-right'),
                'theme' => sanitize_text_field($data['settings']['theme'] ?? 'auto'),
                'greeting_message' => sanitize_textarea_field($data['settings']['greeting_message'] ?? ''),
                'offline_message' => sanitize_textarea_field($data['settings']['offline_message'] ?? '')
            )
        );
    }
}