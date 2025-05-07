<?php
if (!defined('ABSPATH')) exit;

function chatpd_load_default_prompts($business_type)
{
    global $wpdb;
    $table = $wpdb->prefix . 'chatpd_prompts';

    $prompts = [];

    switch ($business_type) {
        case 'tienda':
            $prompts = [
                ['prompt_type' => 'saludo', 'prompt_text' => '¡Hola! ¿Buscas algo específico hoy?'],
                ['prompt_type' => 'envio', 'prompt_text' => 'Nuestros envíos demoran entre 2 y 5 días hábiles.'],
                ['prompt_type' => 'producto', 'prompt_text' => '¿Qué tipo de producto deseas? Ej. camisetas, zapatos, accesorios...'],
            ];
            break;

        case 'restaurante':
            $prompts = [
                ['prompt_type' => 'saludo', 'prompt_text' => '¡Bienvenido! ¿Deseas ver nuestro menú de hoy?'],
                ['prompt_type' => 'envio', 'prompt_text' => 'Ofrecemos servicio a domicilio de 11:00 a 21:00.'],
                ['prompt_type' => 'producto', 'prompt_text' => '¿Qué deseas pedir hoy? Puedo sugerirte platos populares.'],
            ];
            break;

        case 'servicios':
            $prompts = [
                ['prompt_type' => 'saludo', 'prompt_text' => '¡Hola! ¿Qué servicio necesitas hoy?'],
                ['prompt_type' => 'info', 'prompt_text' => 'Ofrecemos asesoría, instalación, y mantenimiento técnico.'],
                ['prompt_type' => 'contacto', 'prompt_text' => 'Puedes agendar una cita o pedir presupuesto por WhatsApp.'],
            ];
            break;

        // Agrega más categorías aquí...

        default:
            return;
    }

    // Insertar los prompts
    foreach ($prompts as $item) {
        $wpdb->insert($table, [
            'business_type' => $business_type,
            'prompt_type'   => $item['prompt_type'],
            'prompt_text'   => $item['prompt_text'],
            'is_active'     => true,
            'created_at'    => current_time('mysql'),
        ]);
    }
}
