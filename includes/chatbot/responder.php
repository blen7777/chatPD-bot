<?php
if (!defined('ABSPATH')) exit;

function chatpd_get_bot_response($pregunta, $session_id = null, $user_id = null)
{
    global $wpdb;
    $config_table = $wpdb->prefix . 'chatpd_config';
    $prompts_table = $wpdb->prefix . 'chatpd_prompts';
    $history_table = $wpdb->prefix . 'chatpd_history';

    // Obtener tipo de negocio actual
    $config = $wpdb->get_row("SELECT business_type FROM $config_table ORDER BY id DESC LIMIT 1");
    $business_type = $config ? $config->business_type : 'general';

    // Intentar encontrar prompt predefinido que coincida
    $prompts = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $prompts_table WHERE business_type = %s AND is_active = 1",
            $business_type
        )
    );

    foreach ($prompts as $prompt) {
        if (preg_match('/' . preg_quote($prompt->prompt_type, '/') . '/i', $pregunta)) {
            $respuesta = $prompt->prompt_text;

            // Guardar historial
            $wpdb->insert($history_table, [
                'user_id'           => $user_id,
                'session_id'        => $session_id,
                'question'          => $pregunta,
                'response'          => $respuesta,
                'matched_prompt_id' => $prompt->id,
                'created_at'        => current_time('mysql'),
            ]);

            return $respuesta;
        }
    }

    // Si no hay respuesta predefinida, usar OpenAI
    return chatpd_call_openai($pregunta, $session_id, $user_id);
}

function chatpd_call_openai($pregunta, $session_id = null, $user_id = null)
{
    global $wpdb;
    $history_table = $wpdb->prefix . 'chatpd_history';

    $api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
    if (!$api_key) {
        return 'API Key no configurada.';
    }

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ],
        'body' => json_encode([
            'model'    => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $pregunta],
            ],
            'temperature' => 0.7,
        ]),
    ]);

    if (is_wp_error($response)) {
        return 'No se pudo conectar a OpenAI.';
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $respuesta = $body['choices'][0]['message']['content'] ?? 'No se obtuvo respuesta.';

    // Guardar historial
    $wpdb->insert($history_table, [
        'user_id'           => $user_id,
        'session_id'        => $session_id,
        'question'          => $pregunta,
        'response'          => $respuesta,
        'matched_prompt_id' => null,
        'created_at'        => current_time('mysql'),
    ]);

    return $respuesta;
}
