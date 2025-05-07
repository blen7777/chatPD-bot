<?php
/**
 * Este archivo se ejecuta solo cuando el usuario desinstala completamente el plugin
 * desde el panel de administración de WordPress.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Puedes usar esta opción si en el futuro deseas dar control desde admin
// $delete_data = get_option('chatpd_delete_on_uninstall');

global $wpdb;

// Define las tablas a eliminar
$tables = [
    $wpdb->prefix . 'chatpd_config',
    $wpdb->prefix . 'chatpd_prompts',
    $wpdb->prefix . 'chatpd_history',
];

// Elimina todas las tablas
foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// (Opcional) Eliminar opciones si las guardaste en wp_options
// delete_option('chatpd_delete_on_uninstall');
// delete_option('chatpd_version');
