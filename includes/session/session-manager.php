<?php
if (!defined('ABSPATH')) exit;

function chatpd_start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Guardar session_id en cookie si no existe
    if (!isset($_COOKIE['chatpd_session_id'])) {
        setcookie('chatpd_session_id', session_id(), time() + (60 * 60 * 24), '/');
    }
}

function chatpd_end_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}
