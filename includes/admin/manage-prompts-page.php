<?php
if (!current_user_can('manage_options')) return;

global $wpdb;
$table = $wpdb->prefix . 'chatpd_prompts';
$prompts = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 100");

if (isset($_GET['deleted'])) {
    echo '<div class="notice notice-success"><p>Prompt eliminado exitosamente.</p></div>';
}
if (isset($_GET['updated'])) {
    echo '<div class="notice notice-success"><p>Prompt actualizado correctamente.</p></div>';
}

if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $prompt = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $edit_id));

    if ($prompt): ?>
        <h2>Editar Prompt</h2>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('chatpd_update_prompt', 'chatpd_nonce'); ?>
            <input type="hidden" name="action" value="chatpd_update_prompt">
            <input type="hidden" name="id" value="<?php echo $prompt->id; ?>">

            <p><label>Pregunta:</label><br>
                <input type="text" name="prompt_type" value="<?php echo esc_attr($prompt->prompt_type); ?>" style="width:100%;" required>
            </p>

            <p><label>Respuesta:</label><br>
                <textarea name="prompt_text" rows="5" style="width:100%;" required><?php echo esc_textarea($prompt->prompt_text); ?></textarea>
            </p>

            <button class="button button-primary">Guardar Cambios</button>
        </form>
        <hr>
<?php endif;
}
?>

<div class="wrap">
    <h1>Gestionar Prompts</h1>
    <table class="widefat striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Pregunta</th>
                <th>Respuesta</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prompts as $prompt): ?>
                <tr>
                    <td><?php echo $prompt->id; ?></td>
                    <td><?php echo esc_html($prompt->prompt_type); ?></td>
                    <td><?php echo esc_html(wp_trim_words($prompt->prompt_text, 15)); ?></td>
                    <td>
                        <a class="button button-primary" href="admin.php?page=chatpd-dashboard&tab=manage&edit=<?php echo $prompt->id; ?>">✏️ Editar</a>
                        
                        <a class="button button-secondary" 
                        href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=chatpd_delete_prompt&id=' . $prompt->id . '&redirect=manage'), 'chatpd_delete_prompt'); ?>" 
                        onclick="return confirm('¿Eliminar este prompt?')">🗑️ Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>