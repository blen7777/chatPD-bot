<?php
if (!defined('ABSPATH')) exit;

add_action('admin_post_chatpd_save_settings', 'chatpd_save_settings');

function chatpd_save_settings()
{
    if (!current_user_can('manage_options') || !check_admin_referer('chatpd_settings_nonce', 'chatpd_nonce')) {
        wp_die('No autorizado.');
    }

    global $wpdb;
    $config_table  = $wpdb->prefix . 'chatpd_config';
    $prompts_table = $wpdb->prefix . 'chatpd_prompts';

    $business_type = sanitize_text_field($_POST['business_type']);
    $language      = sanitize_text_field($_POST['language']);
    $bot_name      = sanitize_text_field($_POST['bot_name']);
    $chat_color    = sanitize_text_field($_POST['chat_color']);
    

    $data = [
        'business_type'     => $business_type,
        'language'          => $language,
        'timeout_warning'   => isset($_POST['timeout_warning']) ? intval($_POST['timeout_warning']) : 60,
        'timeout_end'       => isset($_POST['timeout_end']) ? intval($_POST['timeout_end']) : 300,
        'bot_name'          => $bot_name,
        'chat_color'        => $chat_color,
        'updated_at'        => current_time('mysql'),
    ];

    // Verifica si ya existe configuración previa
    $exists = $wpdb->get_var("SELECT id FROM $config_table LIMIT 1");

    if ($exists) {
        $wpdb->update($config_table, $data, ['id' => $exists]);
    } else {
        $data['created_at'] = current_time('mysql');
        $wpdb->insert($config_table, $data);
    }

    // Solo cargar prompts si no existen para ese tipo de negocio
    $existing_prompts = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $prompts_table WHERE business_type = %s", $business_type)
    );

    if ((int)$existing_prompts === 0) {
        require_once plugin_dir_path(__DIR__) . '/admin/prompts-loader.php';
        chatpd_load_default_prompts($business_type);
    }

    wp_redirect(admin_url('admin.php?page=chatpd-settings&saved=true'));
    exit;
}
