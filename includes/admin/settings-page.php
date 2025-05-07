<?php
if (!defined('ABSPATH')) exit;

function chatpd_register_admin_menu()
{
    add_menu_page(
        'Configuración ChatPD',
        'ChatPD Bot',
        'manage_options',
        'chatpd-settings',
        'chatpd_render_settings_page',
        'dashicons-format-chat',
        90
    );
}
add_action('admin_menu', 'chatpd_register_admin_menu');

function chatpd_render_settings_page()
{
    ?>
    <div class="wrap">
        <h1>Configuración Inicial de ChatPD Bot</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="chatpd_save_settings">
            <?php wp_nonce_field('chatpd_settings_nonce', 'chatpd_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="business_type">Tipo de negocio</label></th>
                    <td>
                    <select name="business_type" id="business_type" required>
                        <option value="">-- Selecciona --</option>
                        <option value="tienda" <?php selected($config->business_type ?? '', 'tienda'); ?>>Tienda online</option>
                        <option value="restaurante" <?php selected($config->business_type ?? '', 'restaurante'); ?>>Restaurante</option>
                        <option value="servicios" <?php selected($config->business_type ?? '', 'servicios'); ?>>Servicios</option>
                        <option value="inmobiliaria" <?php selected($config->business_type ?? '', 'inmobiliaria'); ?>>Inmobiliaria</option>
                        <option value="educacion" <?php selected($config->business_type ?? '', 'educacion'); ?>>Educación</option>
                    </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="language">Idioma</label></th>
                    <td>
                    <select name="language" id="language" required>
                        <option value="es" <?php selected($config->language ?? '', 'es'); ?>>Español</option>
                        <option value="en" <?php selected($config->language ?? '', 'en'); ?>>Inglés</option>
                    </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="language">Tiempo para mostrar advertencia (segundos)</label></th>
                    <td>
                    <label for="timeout_warning">Tiempo para mostrar advertencia (segundos)</label>
                    <input type="number" name="timeout_warning" value="<?php echo esc_attr($config->timeout_warning ?? 60); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="language">Idioma</label></th>
                    <td>
                    <label for="timeout_end">Tiempo para terminar conversación (segundos)</label>
                    <input type="number" name="timeout_end" value="<?php echo esc_attr($config->timeout_end ?? 300); ?>" />
                    </td>
                </tr>
            </table>

            <?php submit_button('Guardar configuración'); ?>
        </form>
    </div>
    <?php
}
