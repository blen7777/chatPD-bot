<?php
/*
Plugin Name: ChatPD Bot
Description: Chat flotante con ChatGPT integrado para WooCommerce.
Version: 1.0
Author: Presencia Digital
*/

if (!defined('ABSPATH')) exit;

// Cargar archivo de instalación
require_once plugin_dir_path(__FILE__) . 'includes/installer.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax/ask-callback.php';
require_once plugin_dir_path(__FILE__) . 'includes/session/session-manager.php';

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

    wp_localize_script('chatpd-bot-js', 'chatpd_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('chatpd_nonce'),
    ]);
}


if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'includes/admin/settings-page.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin/settings-handler.php';
}

add_action('wp_footer', 'chatpd_bot_html');

function chatpd_bot_html()
{
?>
    <div id="chatbot-button" style="position:fixed;bottom:20px;right:20px;background:#25d366;color:white;padding:15px;border-radius:50%;cursor:pointer;z-index:9999;">
        💬
    </div>

    <div id="chatbot-box" style="display:none;position:fixed;bottom:80px;right:20px;width:300px;height:400px;background:white;border:1px solid #ccc;z-index:9999;border-radius:10px;box-shadow:0 0 15px rgba(0,0,0,0.3);overflow:hidden;">
        <div style="background:#25d366;padding:10px;color:white;font-weight:bold;">Asistente ChatPD</div>
        <div id="chat-content" style="padding:10px;height:300px;overflow-y:auto;font-size:14px;"></div>
        <form id="chat-form" style="display:flex;padding:10px;border-top:1px solid #ccc;">
            <input type="text" id="chat-input" style="flex:1;padding:5px;" placeholder="Escribe tu consulta..." required />
            <button type="submit" style="background:#25d366;color:white;border:none;padding:0 10px;">➤</button>
        </form>
    </div>
<?php
}

add_action('wp_ajax_nopriv_chatpd_ask', 'chatpd_ask_callback');
add_action('shutdown', 'chatpd_end_session');
