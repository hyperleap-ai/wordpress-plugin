<?php
/**
 * Plugin Name: Hyperleap AI Chatbots
 * Plugin URI: https://hyperleap.ai
 * Description: Seamlessly integrate Hyperleap AI chatbots into your WordPress website with one-click installation, advanced configuration, and smart deployment controls.
 * Version: 2.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Hyperleap AI
 * Author URI: https://hyperleap.ai
 * Text Domain: hyperleap-chatbots
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 */

if (!defined('WPINC')) {
    die;
}

define('HYPERLEAP_CHATBOTS_VERSION', '2.0.0');
define('HYPERLEAP_CHATBOTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HYPERLEAP_CHATBOTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HYPERLEAP_CHATBOTS_JS_URL', 'https://chatjs.hyperleap.ai/chatbot.min.js');
define('HYPERLEAP_CHATBOTS_API_URL', 'https://api.hyperleapai.com');

function activate_hyperleap_chatbots() {
    require_once HYPERLEAP_CHATBOTS_PLUGIN_DIR . 'includes/class-hyperleap-chatbots-activator.php';
    Hyperleap_Chatbots_Activator::activate();
}

function deactivate_hyperleap_chatbots() {
    require_once HYPERLEAP_CHATBOTS_PLUGIN_DIR . 'includes/class-hyperleap-chatbots-deactivator.php';
    Hyperleap_Chatbots_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_hyperleap_chatbots');
register_deactivation_hook(__FILE__, 'deactivate_hyperleap_chatbots');

require_once HYPERLEAP_CHATBOTS_PLUGIN_DIR . 'includes/class-hyperleap-chatbots.php';

function run_hyperleap_chatbots() {
    $plugin = new Hyperleap_Chatbots();
    $plugin->run();
}

add_action('plugins_loaded', 'run_hyperleap_chatbots');