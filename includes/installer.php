<?php
if (!defined('ABSPATH')) exit;

function chatpd_install_database()
{
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $table_prefix = $wpdb->prefix;

    $tables = [

        "{$table_prefix}chatpd_config" => "
            CREATE TABLE {$table_prefix}chatpd_config (
                id INT AUTO_INCREMENT PRIMARY KEY,
                business_type VARCHAR(100),
                language VARCHAR(10) DEFAULT 'es',
                api_key TEXT,
                timeout_warning INT DEFAULT 60,
                timeout_end INT DEFAULT 300,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) $charset_collate;
        ",

        "{$table_prefix}chatpd_prompts" => "
            CREATE TABLE {$table_prefix}chatpd_prompts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                business_type VARCHAR(100),
                prompt_type VARCHAR(50),
                prompt_text TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) $charset_collate;
        ",

        "{$table_prefix}chatpd_history" => "
            CREATE TABLE {$table_prefix}chatpd_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT NULL,
                session_id VARCHAR(100),
                question TEXT,
                response TEXT,
                matched_prompt_id INT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) $charset_collate;
        ",

        "{$table_prefix}chatpd_stats" => "
            CREATE TABLE {$table_prefix}chatpd_stats (
                id INT AUTO_INCREMENT PRIMARY KEY,
                prompt_id INT NOT NULL,
                times_used INT DEFAULT 0,
                last_used_at DATETIME,
                FOREIGN KEY (prompt_id) REFERENCES {$table_prefix}chatpd_prompts(id) ON DELETE CASCADE
            ) $charset_collate;
        ",
    ];

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($tables as $sql) {
        dbDelta($sql);
    }
}

$columns = $wpdb->get_results("SHOW COLUMNS FROM $config_table LIKE 'timeout_warning'");
if (empty($columns)) {
    $wpdb->query("ALTER TABLE $config_table ADD COLUMN timeout_warning INT DEFAULT 60");
}
$columns = $wpdb->get_results("SHOW COLUMNS FROM $config_table LIKE 'timeout_end'");
if (empty($columns)) {
    $wpdb->query("ALTER TABLE $config_table ADD COLUMN timeout_end INT DEFAULT 300");
}
