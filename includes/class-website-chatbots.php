<?php
/**
 * core plugin class
 */
class Website_Chatbots {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->version = WEBSITE_CHATBOTS_VERSION;
        $this->plugin_name = 'ai-chatbots';
        
        $this->load_dependencies();
    }

    private function load_dependencies() {
        require_once WEBSITE_CHATBOTS_PLUGIN_DIR . 'admin/class-website-chatbots-admin-menu.php';
    }

    private function define_admin_hooks() {
        $plugin_admin = new Website_Chatbots_Admin_Menu($this->get_plugin_name(), $this->get_version());

        //TODO: plugin prefixes for action cbacks
        // Add menu items
        add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($plugin_admin, 'register_settings'));

        // Add admin assets
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));

        add_action('wp_ajax_save_chatbot', array($plugin_admin, 'save_chatbot'));
        add_action('wp_ajax_delete_chatbot', array($plugin_admin, 'delete_chatbot'));
        add_action('wp_ajax_validate_chatbot', array($plugin_admin, 'validate_chatbot'));
        add_action('wp_ajax_update_chatbot_status', array($plugin_admin, 'update_chatbot_status'));
    }

    private function define_public_hooks() {
        // Add chatbot script to selected pages
        add_action('wp_head', array($this, 'insert_chatbot_script'));
    }

    public function insert_chatbot_script() {
        global $post;
        
        $chatbots = get_option('website_chatbots_data', array());
        
        foreach ($chatbots as $chatbot) {
            // Check if chatbot is active first
            if (!isset($chatbot['status']) || $chatbot['status'] !== 'active') {
                continue; 
            }
            $location = isset($chatbot['location']) ? $chatbot['location'] : '';
            $pages = isset($chatbot['pages']) ? $chatbot['pages'] : array();
            
            $should_display = false;
            
            if ($location === 'sitewide') {
                $should_display = true;
            } elseif ($location === 'specific' && !empty($pages) && isset($post)) {
                $should_display = in_array($post->ID, $pages);
            }
            
            if ($should_display) {
                $this->output_chatbot_script($chatbot);
                break; // Only output one active chatbot
            }
        }
    }

    private function output_chatbot_script($chatbot) {
        if (empty($chatbot['chatbot_id']) || empty($chatbot['private_key'])) {
            return;
        }
        
        ?>
        <script>
            (function () {
            // Define chatbot configuration
                window.userChatbotConfig = {
                    chatbotId: "<?php echo esc_js($chatbot['chatbot_id']); ?>",
                    privateKey: "<?php echo esc_js($chatbot['private_key']); ?>",
                };
            // Dynamically load chatbot plugin
                const chatbotScript = document.createElement("script");
                chatbotScript.src = "https://chatjs.hyperleap.ai/chatbot.min.js";
                chatbotScript.async = true;
                document.head.appendChild(chatbotScript);
            })();
        </script>
        <?php
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
    }
}