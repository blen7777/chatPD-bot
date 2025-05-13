<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_chatpd_send_order', 'chatpd_handle_order_request');
add_action('wp_ajax_nopriv_chatpd_send_order', 'chatpd_handle_order_request');

function chatpd_handle_order_request()
{
    check_ajax_referer('chatpd_nonce', 'nonce');

    $nombre    = sanitize_text_field($_POST['nombre'] ?? '');
    $telefono  = sanitize_text_field($_POST['telefono'] ?? '');
    $direccion = sanitize_textarea_field($_POST['direccion'] ?? '');
    $producto  = sanitize_text_field($_POST['producto'] ?? '');

    if (!$nombre || !$telefono || !$direccion || !$producto) {
        wp_send_json_error('Campos incompletos');
    }

    // Validar número de teléfono (solo números, longitud mínima 8)
    $telefono_validado = preg_replace('/\D/', '', $telefono);
    if (strlen($telefono_validado) < 8) {
        wp_send_json_error('El número de teléfono no es válido.');
    }

    // Crear mensaje ordenado
    $mensaje = "\ud83d\udce2 *Nuevo pedido desde el ChatPD Bot*\n\n";
    $mensaje .= "*Nombre:* $nombre\n";
    $mensaje .= "*Teléfono:* $telefono_validado\n";
    $mensaje .= "*Dirección:* $direccion\n";
    $mensaje .= "*Producto:* $producto\n";

    // Enviar correo de confirmación a la empresa
    $correo_empresa = 'rpmstoresvinfo@gmail.com';
    $asunto = 'Nuevo pedido desde el ChatPD Bot';
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: ChatPD Bot <no-reply@' . parse_url(home_url(), PHP_URL_HOST) . '>'
    ];
    wp_mail($correo_empresa, $asunto, $mensaje, $headers);

    // Generar link de WhatsApp al número del cliente directamente
    $mensaje_encoded = urlencode($mensaje);
    $whatsapp_url = "https://wa.me/{$telefono_validado}?text=$mensaje_encoded";

    // Mostrar botón y confirmación
    $button = "<p style='margin-top:10px;'>🎉 <strong>Tu pedido ha sido registrado con éxito.</strong><br>Te contactaremos pronto por WhatsApp o teléfono.</p>";
    $button .= "<a href='$whatsapp_url' target='_blank' style='display:inline-block;margin-top:10px;padding:10px 15px;background:#25d366;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;'>📧 Confirmar pedido por WhatsApp</a>";

    // Respuesta para el frontend
    wp_send_json_success($button);
}
