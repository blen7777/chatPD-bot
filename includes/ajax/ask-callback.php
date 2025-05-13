<?php
if (!defined('ABSPATH')) exit;

// Hook para AJAX (usuarios autenticados y no autenticados)
add_action('wp_ajax_chatpd_ask', 'chatpd_ask_callback');
add_action('wp_ajax_nopriv_chatpd_ask', 'chatpd_ask_callback');

function chatpd_ask_callback()
{
    check_ajax_referer('chatpd_nonce', 'nonce');

    $pregunta = sanitize_text_field($_POST['pregunta']);

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
        $respuesta = "No encontramos productos relacionados con *$pregunta*.\n";
        $respuesta .= "Puedes intentar con otro nombre o enviarnos el enlace del producto desde Shein o Temu para ayudarte a traerlo.\n";
        $respuesta .= "También puedes contactarnos por WhatsApp: https://wa.me/50369630252";

        wp_send_json_success($respuesta);
    }
}
