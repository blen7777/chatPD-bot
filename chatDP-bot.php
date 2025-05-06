<?php
/*
Plugin Name: ChatPD Bot
Description: Chat flotante con ChatGPT integrado para WooCommerce.
Version: 1.0
Author: Presencia Digital
*/

add_action('wp_footer', 'chatpd_bot_html');
add_action('wp_enqueue_scripts', 'chatpd_bot_scripts');

function chatpd_bot_html()
{
?>
    <div id="chatbot-button" style="position:fixed;bottom:20px;right:20px;background:#25d366;color:white;padding:15px;border-radius:50%;cursor:pointer;z-index:9999;">
        💬
    </div>

    <div id="chatbot-box" style="display:none;position:fixed;bottom:80px;right:20px;width:300px;height:400px;background:white;border:1px solid #ccc;z-index:9999;border-radius:10px;box-shadow:0 0 15px rgba(0,0,0,0.3);overflow:hidden;">
        <div style="background:#25d366;padding:10px;color:white;font-weight:bold;">Asistente ChatPD</div>
        <div id="chat-content" style="padding:10px;height:300px;overflow-y:auto;font-size:14px;"></div>
        <form id="chat-form" style="display:flex;padding:10px;border-top:1px solid #ccc;">
            <input type="text" id="chat-input" style="flex:1;padding:5px;" placeholder="Escribe tu consulta..." required />
            <button type="submit" style="background:#25d366;color:white;border:none;padding:0 10px;">➤</button>
        </form>
    </div>
<?php
}

function chatpd_bot_scripts()
{
    wp_enqueue_script('chatpd-bot-js', plugins_url('/chatPD-bot.js', __FILE__), [], null, true);
    wp_localize_script('chatpd-bot-js', 'chatpd_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('chatpd_nonce')
    ]);
}

add_action('wp_ajax_chatpd_ask', 'chatpd_ask_callback');
add_action('wp_ajax_nopriv_chatpd_ask', 'chatpd_ask_callback');

function chatpd_ask_callback()
{
    check_ajax_referer('chatpd_nonce', 'nonce');

    $pregunta = sanitize_text_field($_POST['pregunta']);
    $saludoDetectado = preg_match('/\b(hola|buenas|saludos|qué tal|buenos días|buenas tardes|buenas noches)\b/i', $pregunta);

    // Preparar prompt
    if ($saludoDetectado) {
        $prompt = "Un cliente acaba de saludar diciendo: '$pregunta'. Respóndele con amabilidad y dile que estás listo para ayudarle a encontrar productos o responder dudas sobre la tienda. Al final, pregunta: '¿En qué puedo ayudarte hoy?'";
    } else {
        // Buscar productos en WooCommerce
        $productos = wc_get_products([
            'limit' => 3,
            's' => $pregunta,
        ]);

        $info = "";
        foreach ($productos as $p) {
            $nombre = $p->get_name();
            $precio = wc_price($p->get_price());
            $descripcion = strip_tags($p->get_short_description());
            $url = get_permalink($p->get_id());
            $imagen = wp_get_attachment_url($p->get_image_id());

            $info .= "$nombre\n";
            $info .= "$precio\n";
            if ($imagen) {
                $info .= "Imagen: $imagen\n";
            }
            $info .= "Ver producto: $url\n";
            $info .= "Descripción: $descripcion\n\n";
        }

        $hayProductos = count($productos) > 0;

        if ($hayProductos) {
            $searchQuery = urlencode($pregunta);
            $tiendaUrl = site_url('/tienda?s=' . $searchQuery);

            $prompt = "Eres un asistente virtual de una tienda en línea. Un cliente preguntó: '$pregunta'.";
            $prompt .= "\nEstos son productos disponibles:\n$info";
            $prompt .= "\nSi desea ver más, sugiérele este enlace: $tiendaUrl";
            $prompt .= "\nResponde con amabilidad y claridad.";
        } else {
            $prompt = "Eres un asistente de una tienda de ropa llamada Presencia Digital.";
            $prompt .= "\nUn cliente preguntó: '$pregunta', pero no se encontraron productos relacionados.";
            $prompt .= "\nInfórmale amablemente que por ahora no hay productos disponibles con esa descripción.";
            $prompt .= "\nTambién coméntale que aceptamos pedidos personalizados desde tiendas como Shein y Temu, y que puede contactarnos por WhatsApp al +503 6963 0252 para más información.";
            $prompt .= "\nResponde con tono amigable y útil.";
        }
    }

    // Llamar a la API de OpenAI
    $api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ],
        'body' => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]),
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error('Error al conectar con la API.');
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $respuesta = $body['choices'][0]['message']['content'] ?? 'No se obtuvo respuesta.';
    wp_send_json_success($respuesta);
}
