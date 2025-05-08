<?php

if (isset($_GET['added'])) {
    echo '<div class="notice notice-success"><p>Prompt guardado correctamente.</p></div>';
}
if (isset($_GET['error']) && $_GET['error'] === 'duplicate') {
    echo '<div class="notice notice-warning"><p>La pregunta ya existe.</p></div>';
}
if (isset($_GET['error']) && $_GET['error'] === 'empty') {
    echo '<div class="notice notice-error"><p>Debes llenar ambos campos.</p></div>';
}

if (!current_user_can('manage_options')) return;

global $wpdb;
$table = $wpdb->prefix . 'chatpd_prompts';

// Ver mensaje si se importó
if (isset($_GET['imported'])) {
    echo '<div class="notice notice-success"><p>Se importaron ' . intval($_GET['imported']) . ' prompts exitosamente.</p></div>';
}
?>

<div class="wrap">
    <h1>Entrenamiento del Bot (FAQs)</h1>

    <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('chatpd_import_prompts_nonce', 'chatpd_nonce'); ?>
        <input type="hidden" name="action" value="chatpd_import_prompts">

        <p><strong>Sube un archivo .CSV con columnas <code>pregunta</code> y <code>respuesta</code></strong></p>
        <input type="file" name="faq_csv" accept=".csv" required />

        <p><button class="button button-primary" type="submit">Importar Prompts</button></p>
    </form>

    <hr>
<h2>Agregar Prompt Manualmente</h2>

<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <?php wp_nonce_field('chatpd_add_prompt_nonce', 'chatpd_nonce'); ?>
    <input type="hidden" name="action" value="chatpd_add_prompt">

    <table class="form-table">
        <tr>
            <th><label for="prompt_type">Pregunta</label></th>
            <td><input type="text" name="prompt_type" id="prompt_type" required style="width: 100%;" /></td>
        </tr>
        <tr>
            <th><label for="prompt_text">Respuesta</label></th>
            <td><textarea name="prompt_text" id="prompt_text" rows="4" required style="width: 100%;"></textarea></td>
        </tr>
    </table>

    <button class="button button-primary" type="submit">Guardar Prompt</button>
</form>

</div>
