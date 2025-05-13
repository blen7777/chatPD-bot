// Obtener valor de una cookie
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

function mostrarRespuestaEnChat(mensaje) {
    const chatbox = document.getElementById('chat-content');
    if (!chatbox) return;

    const div = document.createElement('div');
    div.style = 'background:#eef;padding:8px;margin:5px 0;border-radius:5px;';
    div.innerHTML = `<strong>Bot:</strong> ${mensaje}`;
    chatbox.appendChild(div);
    chatbox.scrollTop = chatbox.scrollHeight;
}

// Capturar clics en enlaces interactivos
// incluso si están anidados dentro de otros divs

document.addEventListener("click", function (e) {
    if (
        e.target.tagName === "A" &&
        e.target.closest("#chat-content") &&
        !e.target.classList.contains("no-simular-submit")
    ) {
        e.preventDefault();
        const texto = e.target.innerText.trim();
        const input = document.getElementById("chat-input");

        input.value = texto;
        const form = document.getElementById("chat-form");
        form.dispatchEvent(new Event("submit"));
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("chatbot-button");
    const box = document.getElementById("chatbot-box");
    const form = document.getElementById("chat-form");
    const input = document.getElementById("chat-input");
    const chat = document.getElementById("chat-content");

    let primerSaludoMostrado = false;
    let warningTimeout, endTimeout;

    function iniciarTemporizadores() {
        clearTimeout(warningTimeout);
        clearTimeout(endTimeout);

        const warningTime = parseInt(chatpd_ajax.timeout_warning) * 1000;
        const endTime = parseInt(chatpd_ajax.timeout_end) * 1000;

        warningTimeout = setTimeout(() => {
            mostrarRespuestaEnChat("¿Sigues ahí? Puedes hacer otra pregunta o contactarnos por WhatsApp.");
        }, warningTime);

        endTimeout = setTimeout(() => {
            mostrarRespuestaEnChat("La conversación se ha cerrado por inactividad. ¡Estamos aquí si necesitas ayuda!");
            input.disabled = true;
        }, endTime);
    }

    if (!btn || !box || !form || !input || !chat) return;

    btn.addEventListener("click", () => {
        const estaVisible = box.style.display === "block";
        box.style.display = estaVisible ? "none" : "block";

        // Ejecutar mensaje inicial simulando submit
        if (!estaVisible && !primerSaludoMostrado) {
            const saludo = "🤖 ¡Hola! Soy tu asistente. ¿En qué puedo ayudarte?";
            mostrarRespuestaEnChat(saludo);
        
            const opciones = `
                <div style="margin-top: 10px;">
                    <a href="#" class="chatpd-opcion">🛒 Ver productos disponibles</a><br>
                    <a href="#" class="chatpd-opcion">📦 ¿Cómo funcionan los envíos?</a><br>
                    <a href="#" class="chatpd-opcion">🧾 Quiero hacer un pedido personalizado</a><br>
                    <a href="https://wa.me/50369630252?text=Me brinda información de productos" target="_blank" class="no-simular-submit">💬 Contactar por WhatsApp</a>
                </div>
            `;
            mostrarRespuestaEnChat(opciones);
        
            primerSaludoMostrado = true;
        }
    });

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        iniciarTemporizadores();

        const mensaje = input.value.trim();
        if (!mensaje) return;

        chat.innerHTML += `<div style="text-align:right;margin:5px 0;"><strong>Tú:</strong> ${mensaje}</div>`;
        input.value = "";

        if (mensaje.toLowerCase().includes("pedido personalizado")) {
            const textoInfo = `
                Podemos ayudarte a traer productos de tiendas como <strong>Shein</strong> o <strong>Temu</strong> 🛍️<br><br>
                Para pedidos especiales, escríbenos directamente:
                <a href="https://wa.me/50369630252?text=Necesito hacer un pedido de Shein o TEMU" target="_blank" class="no-simular-submit">💬 Enviar mensaje por WhatsApp</a>`;
            mostrarRespuestaEnChat(textoInfo);
            return; // Cancela el submit normal
        }
        
        fetch(chatpd_ajax.ajax_url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "chatpd_ask",
                nonce: chatpd_ajax.nonce,
                pregunta: mensaje,
                session_id: getCookie("chatpd_session_id")
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const botMessage = data.data.replace(
                    /(https?:\/\/[^\s]+)/g,
                    '<a href="$1" target="_blank" style="color:#25d366;">$1</a>'
                );
                mostrarRespuestaEnChat(botMessage);

                chat.scrollTop = chat.scrollHeight;
            } else {
                chat.innerHTML += `<div><em>Ocurrió un error al obtener la respuesta.</em></div>`;
            }
        })
        .catch(() => {
            chat.innerHTML += `<div><em>Error de red al contactar el bot.</em></div>`;
        });
    });
});

function mostrarFormularioPedido() {
    const chatbox = document.getElementById('chat-content');

    const formHtml = `
        <div id="formulario-pedido" style="margin-top:10px;padding:10px;border:1px solid #ccc;border-radius:8px;background:#f9f9f9;">
            <strong>📅 Completa tu pedido:</strong><br><br>
            <label>Nombre:<br><input type="text" id="pedido-nombre" style="width:100%;margin-bottom:6px;" /></label>
            <label>Teléfono:<br><input type="text" id="pedido-telefono" style="width:100%;margin-bottom:6px;" /></label>
            <label>Dirección:<br><textarea id="pedido-direccion" style="width:100%;margin-bottom:6px;"></textarea></label>
            <label>Producto deseado:<br><input type="text" id="pedido-producto" style="width:100%;margin-bottom:6px;" /></label>
            <button onclick="enviarPedidoCliente()" style="background:#25d366;color:#fff;border:none;padding:6px 12px;border-radius:5px;cursor:pointer;">Enviar pedido</button>
        </div>
    `;

    chatbox.innerHTML += formHtml;
    chatbox.scrollTop = chatbox.scrollHeight;
}

function enviarPedidoCliente() {
    const nombre = document.getElementById('pedido-nombre').value.trim();
    const telefono = document.getElementById('pedido-telefono').value.trim();
    const direccion = document.getElementById('pedido-direccion').value.trim();
    const producto = document.getElementById('pedido-producto').value.trim();

    if (!nombre || !telefono || !direccion || !producto) {
        alert('Por favor completa todos los campos.');
        return;
    }

    fetch(chatpd_ajax.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'chatpd_send_order',
            nonce: chatpd_ajax.nonce,
            nombre,
            telefono,
            direccion,
            producto
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            mostrarRespuestaEnChat("¡Pedido enviado! Nos pondremos en contacto contigo pronto.");
            document.getElementById('formulario-pedido').remove();
        } else {
            mostrarRespuestaEnChat('⚠️ Hubo un error al enviar el pedido. Intenta de nuevo.');
        }
    });
}
