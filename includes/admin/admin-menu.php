<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('chatpd_register_admin_menu')) {
    function chatpd_register_admin_menu()
    {
        add_menu_page(
            'ChatPD Bot',
            'ChatPD Bot',
            'manage_options',
            'chatpd-dashboard',
            'chatpd_render_admin_tabs',
            'dashicons-format-chat',
            90
        );
    }
    add_action('admin_menu', 'chatpd_register_admin_menu');
}

if (!function_exists('chatpd_render_admin_tabs')) {
    function chatpd_render_admin_tabs()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'chatpd_config';
        $config = $wpdb->get_row("SELECT * FROM $table ORDER BY id DESC LIMIT 1");
        $GLOBALS['chatpd_config'] = $config;

        $active_tab = $_GET['tab'] ?? 'settings';
        ?>
        <div class="wrap">
            <h1>ChatPD Bot</h1>
            <nav class="nav-tab-wrapper">
                <a href="?page=chatpd-dashboard&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">Configuración</a>
                <a href="?page=chatpd-dashboard&tab=training" class="nav-tab <?php echo $active_tab === 'training' ? 'nav-tab-active' : ''; ?>">Entrenamiento del Bot</a>
                <a href="?page=chatpd-dashboard&tab=manage" class="nav-tab <?php echo $active_tab === 'manage' ? 'nav-tab-active' : ''; ?>">Gestionar Prompts</a>
            </nav>

            <div style="padding: 20px; background: #fff; border: 1px solid #ccd0d4;">
                <?php
                switch ($active_tab) {
                    case 'training':
                        include plugin_dir_path(__FILE__) . 'training-page.php';
                        break;
                    case 'manage':
                        include plugin_dir_path(__FILE__) . 'manage-prompts-page.php';
                        break;
                    default:
                        include plugin_dir_path(__FILE__) . 'settings-page.php';
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
}
