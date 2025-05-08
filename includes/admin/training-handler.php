<?php
if (!defined('ABSPATH')) exit;

add_action('admin_post_chatpd_import_prompts', 'chatpd_import_prompts');

function chatpd_import_prompts()
{
    if (!current_user_can('manage_options') || !check_admin_referer('chatpd_import_prompts_nonce', 'chatpd_nonce')) {
        wp_die('No autorizado.');
    }

    if (!isset($_FILES['faq_csv']) || $_FILES['faq_csv']['error'] !== UPLOAD_ERR_OK) {
        wp_die('Error al subir el archivo.');
    }

    $file = $_FILES['faq_csv']['tmp_name'];
    $handle = fopen($file, 'r');
    if (!$handle) wp_die('No se pudo abrir el archivo.');

    global $wpdb;
    $table = $wpdb->prefix . 'chatpd_prompts';

    $config = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}chatpd_config ORDER BY id DESC LIMIT 1");
    $business_type = $config->business_type ?? 'general';

    $imported = 0;
    $first = true;

    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        if ($first) {
            $first = false;
            continue; // saltar encabezado
        }

        [$pregunta, $respuesta] = $data;

        if (empty($pregunta) || empty($respuesta)) continue;

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE business_type = %s AND prompt_type = %s",
                $business_type,
                $pregunta
            )
        );
        if ($exists) continue;

        $wpdb->insert($table, [
            'business_type' => $business_type,
            'prompt_type'   => sanitize_text_field($pregunta),
            'prompt_text'   => sanitize_textarea_field($respuesta),
            'is_active'     => 1,
            'created_at'    => current_time('mysql'),
        ]);

        $imported++;
    }

    fclose($handle);

    wp_redirect(admin_url('admin.php?page=chatpd-training&imported=' . $imported));
    exit;
}


// Add Prompt Manually
add_action('admin_post_chatpd_add_prompt', 'chatpd_add_prompt');
function chatpd_add_prompt()
{
    if (!current_user_can('manage_options') || !check_admin_referer('chatpd_add_prompt_nonce', 'chatpd_nonce')) {
        wp_die('No autorizado.');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'chatpd_prompts';

    $prompt_type = sanitize_text_field($_POST['prompt_type']);
    $prompt_text = sanitize_textarea_field($_POST['prompt_text']);

    if (empty($prompt_type) || empty($prompt_text)) {
        wp_redirect(admin_url('admin.php?page=chatpd-training&error=empty'));
        exit;
    }

    $config = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}chatpd_config ORDER BY id DESC LIMIT 1");
    $business_type = $config->business_type ?? 'general';

    // Evitar duplicado
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE business_type = %s AND prompt_type = %s",
        $business_type,
        $prompt_type
    ));

    if ($exists) {
        wp_redirect(admin_url('admin.php?page=chatpd-training&error=duplicate'));
        exit;
    }

    $wpdb->insert($table, [
        'business_type' => $business_type,
        'prompt_type'   => $prompt_type,
        'prompt_text'   => $prompt_text,
        'is_active'     => 1,
        'created_at'    => current_time('mysql'),
    ]);

    wp_redirect(admin_url('admin.php?page=chatpd-training&added=1'));
    exit;
}

// Delete Prompt
add_action('admin_post_chatpd_delete_prompt', 'chatpd_handle_prompt_delete');
function chatpd_handle_prompt_delete()
{
    if (!current_user_can('manage_options')) {
        wp_die('No tienes permisos.');
    }

    if (!isset($_GET['id']) || !wp_verify_nonce($_GET['_wpnonce'], 'chatpd_delete_prompt')) {
        wp_die('Acceso no autorizado.');
    }

    global $wpdb;
    $id = intval($_GET['id']);
    $table = $wpdb->prefix . 'chatpd_prompts';

    $wpdb->delete($table, ['id' => $id]);

    // Redirige de nuevo a la pestaña de gestión
    $redirect = admin_url('admin.php?page=chatpd-dashboard&tab=' . ($_GET['redirect'] ?? 'manage') . '&deleted=1');
    wp_redirect($redirect);
    exit;
}

// Update Prompt
add_action('admin_post_chatpd_update_prompt', 'chatpd_update_prompt');
function chatpd_update_prompt()
{
    if (!current_user_can('manage_options') || !check_admin_referer('chatpd_update_prompt', 'chatpd_nonce')) {
        wp_die('No autorizado.');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'chatpd_prompts';

    $id = intval($_POST['id']);
    $data = [
        'prompt_type' => sanitize_text_field($_POST['prompt_type']),
        'prompt_text' => sanitize_textarea_field($_POST['prompt_text']),
    ];

    $wpdb->update($table, $data, ['id' => $id]);

    wp_redirect(admin_url('admin.php?page=chatpd-manage-prompts&updated=1'));
    exit;
}
