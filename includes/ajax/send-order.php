<?php
if (!defined('ABSPATH')) exit;

// Registrar el handler para usuarios autenticados y no autenticados
add_action('wp_ajax_chatpd_send_order', 'chatpd_handle_send_order');
add_action('wp_ajax_nopriv_chatpd_send_order', 'chatpd_handle_send_order');

function chatpd_handle_send_order() {
    check_ajax_referer('chatpd_nonce', 'nonce');

    $nombre    = sanitize_text_field($_POST['nombre'] ?? '');
    $telefono  = sanitize_text_field($_POST['telefono'] ?? '');
    $direccion = sanitize_textarea_field($_POST['direccion'] ?? '');
    $producto  = sanitize_text_field($_POST['producto'] ?? '');

    if (!$nombre || !$telefono || !$direccion || !$producto) {
        wp_send_json_error('Campos incompletos');
    }

    $mensaje = "Nuevo pedido recibido:\n\n" .
               "- Producto: $producto\n" .
               "- Nombre: $nombre\n" .
               "- Dirección: $direccion\n" .
               "- Teléfono: $telefono";

    // Enviar por correo
    wp_mail('rpmstoresvinfo@gmail.com', 'Nuevo Pedido desde el Bot', $mensaje);

    // Enlace de WhatsApp
    $mensajeWhatsapp = rawurlencode("Hola, quiero confirmar mi pedido:\n\n📦 Producto: $producto\n👤 Nombre: $nombre\n📍 Dirección: $direccion\n📞 Teléfono: $telefono");

    $whatsappLink = "https://wa.me/50369630252?text=$mensajeWhatsapp";

    // Retorna éxito + enlace
    wp_send_json_success([
        'msg' => 'Pedido procesado correctamente',
        'whatsapp_link' => $whatsappLink
    ]);
}

