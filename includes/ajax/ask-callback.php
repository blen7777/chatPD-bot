<?php
if (!defined('ABSPATH')) exit;

// Hook para AJAX (usuarios autenticados y no autenticados)
add_action('wp_ajax_chatpd_ask', 'chatpd_ask_callback');
add_action('wp_ajax_nopriv_chatpd_ask', 'chatpd_ask_callback');

function chatpd_ask_callback()
{
    check_ajax_referer('chatpd_nonce', 'nonce');

    $pregunta = sanitize_text_field($_POST['pregunta'] ?? '');

    // Mostrar menú inicial si la pregunta es vacía o saludo
    if (empty($pregunta) || preg_match('/\b(hola|buenas|hey|saludos|hola bot|hola asistente)\b/i', $pregunta)) {
        $respuesta = "¡Hola! ¿En qué puedo ayudarte hoy? Aquí tienes algunas opciones rápidas:\n\n";
        $respuesta .= "[🛍️ Ver productos disponibles](ver productos disponibles)\n";
        $respuesta .= "[📦 ¿Cómo funcionan los envíos?](envios)\n";
        $respuesta .= "[🤝 Hablar con un asesor](asesor)\n";
        $respuesta .= "[✏️ Hacer un pedido personalizado](pedido personalizado)\n";
        $respuesta .= "[💬 Contactar por WhatsApp](https://wa.me/50369630252)";
        wp_send_json_success($respuesta);
    }

    // Si el usuario dice "ver productos disponibles", pedir que especifique
    if (preg_match('/ver productos/i', $pregunta)) {
        $respuesta = "Perfecto 😊 ¿Qué tipo de producto estás buscando? Ej: camisetas, sandalias, pantalones...";
        wp_send_json_success($respuesta);
    }

    // Intentar encontrar productos en WooCommerce por nombre o descripción
    $productos = wc_get_products([
        'limit' => 3,
        's' => $pregunta,
    ]);

    if (!empty($productos)) {
        $info = "\nAquí tienes algunas opciones que encontramos:\n\n";
        foreach ($productos as $p) {
            $nombre = $p->get_name();
            $precio = wc_price($p->get_price());
            $url = get_permalink($p->get_id());
            $info .= "- $nombre ($precio) → $url\n";
        }

        wp_send_json_success($info);
    } else {
        $respuesta = "No encontramos productos relacionados con *$pregunta*.
Puedes intentar con otro nombre o enviarnos el enlace del producto desde Shein o Temu para ayudarte a traerlo.
También puedes contactarnos por WhatsApp: https://wa.me/50369630252";

        wp_send_json_success($respuesta);
    }
}
