<?php
/**
 * Plugin Name: AI Chatbots
 * Plugin URI: https://hyperleap.ai
 * Description: Integrate AI chatbots into your WordPress website easily and securely. Manage multiple chatbots, control their visibility on specific pages, and securely handle private keys.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Hyperleap AI
 * Author URI: https://hyperleap.ai
 * Text Domain: ai-chatbots
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('WPINC')) {
    die;
}

define('WEBSITE_CHATBOTS_VERSION', '1.0.0');
define('WEBSITE_CHATBOTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WEBSITE_CHATBOTS_PLUGIN_URL', plugin_dir_url(__FILE__));

function activate_website_chatbots() {
    require_once WEBSITE_CHATBOTS_PLUGIN_DIR . 'includes/class-website-chatbots-activator.php';
    Website_Chatbots_Activator::activate();
}

function deactivate_website_chatbots() {
    require_once WEBSITE_CHATBOTS_PLUGIN_DIR . 'includes/class-website-chatbots-deactivator.php';
    Website_Chatbots_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_website_chatbots');
register_deactivation_hook(__FILE__, 'deactivate_website_chatbots');

require WEBSITE_CHATBOTS_PLUGIN_DIR . 'includes/class-website-chatbots.php';

function run_website_chatbots() {
    $plugin = new Website_Chatbots();
    $plugin->run();
}

run_website_chatbots();

//TODO: cron job for verifying chatbot data.
//1hr interval only in admin panel. clean the scheduler after uninstall or deactivation.