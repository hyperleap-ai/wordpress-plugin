<?php
/**
 * The core plugin class that orchestrates hooks and functionality
 */
class Hyperleap_Chatbots {

    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->version = HYPERLEAP_CHATBOTS_VERSION;
        $this->plugin_name = 'hyperleap-chatbots';
        
        $this->load_dependencies();
    }

    private function load_dependencies() {
        require_once HYPERLEAP_CHATBOTS_PLUGIN_DIR . 'includes/class-hyperleap-chatbots-admin.php';
        require_once HYPERLEAP_CHATBOTS_PLUGIN_DIR . 'includes/class-hyperleap-chatbots-public.php';
        require_once HYPERLEAP_CHATBOTS_PLUGIN_DIR . 'includes/class-hyperleap-chatbots-api.php';
    }

    private function define_admin_hooks() {
        $plugin_admin = new Hyperleap_Chatbots_Admin($this->get_plugin_name(), $this->get_version());

        // Admin menu and pages
        add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));
        add_action('admin_init', array($plugin_admin, 'register_settings'));

        // Admin assets
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));

        // AJAX actions
        add_action('wp_ajax_hyperleap_save_chatbot', array($plugin_admin, 'save_chatbot'));
        add_action('wp_ajax_hyperleap_delete_chatbot', array($plugin_admin, 'delete_chatbot'));
        add_action('wp_ajax_hyperleap_toggle_chatbot', array($plugin_admin, 'toggle_chatbot'));
        add_action('wp_ajax_hyperleap_validate_chatbot', array($plugin_admin, 'validate_chatbot'));
        add_action('wp_ajax_hyperleap_quick_install', array($plugin_admin, 'quick_install'));

        // Admin notices
        add_action('admin_notices', array($plugin_admin, 'show_admin_notices'));
    }

    private function define_public_hooks() {
        $plugin_public = new Hyperleap_Chatbots_Public($this->get_plugin_name(), $this->get_version());

        // Frontend chatbot injection
        add_action('wp_head', array($plugin_public, 'inject_chatbot_script'));
        add_action('wp_footer', array($plugin_public, 'inject_chatbot_fallback'));

        // REST API endpoints
        add_action('rest_api_init', array($plugin_public, 'register_rest_routes'));

        // Performance optimizations
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_public_assets'));
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    public function run() {
        $this->define_admin_hooks();
        $this->define_public_hooks();

        // Initialize API handler
        new Hyperleap_Chatbots_API();
    }
}