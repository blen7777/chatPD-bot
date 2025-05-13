<?php
/*
Plugin Name: ChatPD Bot
Description: Chat flotante con ChatGPT integrado para WooCommerce.
Version: 1.0
Author: Presencia Digital
*/

if (!defined('ABSPATH')) exit;

if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'includes/admin/settings-handler.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin/admin-menu.php'; 
}

// Cargar archivo de instalación
require_once plugin_dir_path(__FILE__) . 'includes/installer.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax/ask-callback.php';
require_once plugin_dir_path(__FILE__) . 'includes/session/session-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/training-handler.php';


// Hook de activación
register_activation_hook(__FILE__, 'chatpd_install_database');
add_action('init', 'chatpd_start_session', 1);

add_action('wp_enqueue_scripts', 'chatpd_enqueue_scripts');
function chatpd_enqueue_scripts()
{
    wp_enqueue_script(
        'chatpd-bot-js',
        plugin_dir_url(__FILE__) . 'assets/js/chatpd-bot.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_enqueue_style(
        'chatpd-style-css',
        plugin_dir_url(__FILE__) . 'assets/css/main.css',
        [],
        '1.0'
    );

    wp_localize_script('chatpd-bot-js', 'chatpd_ajax', [
        'ajax_url'          => admin_url('admin-ajax.php'),
        'nonce'             => wp_create_nonce('chatpd_nonce'),
        'timeout_warning'   => $config->timeout_warning ?? 60,
        'timeout_end'       => $config->timeout_end ?? 300,
    ]);
}

add_action('wp_footer', 'chatpd_bot_html');
function chatpd_bot_html()
{
    global $wpdb;
    $config = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}chatpd_config ORDER BY id DESC LIMIT 1");

    $chat_color = esc_attr($config->chat_color ?? '#25d366');
    $bot_name   = esc_html($config->bot_name ?? 'Asistente ChatPD');
?>
    <div id="chatbot-button" style="position:fixed;bottom:20px;right:20px;background:#25d366;color:white;padding:15px;border-radius:50%;cursor:pointer;z-index:9999;">
        💬
    </div>

    <div id="chatbot-box" style="display:none;position:fixed;bottom:80px;right:20px;width:300px;height:400px;background:white;border:1px solid #ccc;z-index:9999;border-radius:10px;box-shadow:0 0 15px rgba(0,0,0,0.3);overflow:hidden;">
    <div style="background:<?php echo $chat_color; ?>;padding:10px;color:white;font-weight:bold;">
        <?php echo $bot_name; ?>
    </div>
        <div id="chat-content" style="padding:10px;height:300px;overflow-y:auto;font-size:14px;"></div>
        <form id="chat-form" style="display:flex;padding:10px;border-top:1px solid #ccc;">
            <input type="text" id="chat-input" style="flex:1;padding:5px;" placeholder="Escribe tu consulta..." required />
            <div id="chatbot-button" style="position:fixed;bottom:20px;right:20px;background:<?php echo $chat_color; ?>;color:white;padding:15px;border-radius:50%;cursor:pointer;z-index:9999;">
                💬
            </div>
        </form>
    </div>
<?php
}

add_action('wp_ajax_nopriv_chatpd_ask', 'chatpd_ask_callback');
add_action('shutdown', 'chatpd_end_session');
