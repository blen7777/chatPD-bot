<?php
if (!defined('ABSPATH')) exit;

// Hook para AJAX (usuarios autenticados y no autenticados)
add_action('wp_ajax_chatpd_ask', 'chatpd_ask_callback');
add_action('wp_ajax_nopriv_chatpd_ask', 'chatpd_ask_callback');

function chatpd_ask_callback()
{
    check_ajax_referer('chatpd_nonce', 'nonce');

    require_once plugin_dir_path(__FILE__) . '/../chatbot/responder.php';

    $pregunta = sanitize_text_field($_POST['pregunta']);
    $session_id = sanitize_text_field($_POST['session_id'] ?? session_id());
    $user_id = get_current_user_id();

    $respuesta = chatpd_get_bot_response($pregunta, $session_id, $user_id);
    wp_send_json_success($respuesta);
}
