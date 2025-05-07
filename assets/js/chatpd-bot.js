// Obtener valor de una cookie
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

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

    // Mostrar saludo inicial si se abre por primera vez
    if (!estaVisible && !primerSaludoMostrado) {
        const saludo = "🤖¡Hola! Bienvenido 😊";
        chat.innerHTML += `<div style="background:#eef;padding:8px;margin:5px 0;border-radius:5px;"><strong>Bot:</strong> ${saludo}</div>`;
        chat.scrollTop = chat.scrollHeight;
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

        fetch(chatpd_ajax.ajax_url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "chatpd_ask",
                nonce: chatpd_ajax.nonce,
                pregunta: mensaje,
                session_id: getCookie('chatpd_session_id')
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const botMessage = data.data.replace(
                    /(https?:\/\/[^\s]+)/g,
                    '<a href="$1" target="_blank" style="color:#25d366;">$1</a>'
                );
                chat.innerHTML += `<div style="background:#eef;padding:8px;margin:5px 0;border-radius:5px;"><strong>Bot:</strong> ${botMessage}</div>`;

                if (botMessage.includes("¿En qué puedo ayudarte hoy?")) {
                    const quickReplies = document.createElement("div");
                    quickReplies.innerHTML = `
                        <div style="margin-top: 10px;">
                            <button class="chatpd-btn" data-msg="Ver productos disponibles">🛒 Ver productos</button>
                            <button class="chatpd-btn" data-msg="¿Cómo funcionan los envíos?">📦 Información de envíos</button>
                            <button class="chatpd-btn" data-msg="Quiero hacer un pedido personalizado">🧾 Pedido personalizado</button>
                            <a href="https://wa.me/50369630252" target="_blank" style="text-decoration:none;">
                                <button class="chatpd-btn">💬 Contactar por WhatsApp</button>
                            </a>
                        </div>
                    `;
                    chat.appendChild(quickReplies);

                    // Estilos y eventos para botones rápidos
                    quickReplies.querySelectorAll(".chatpd-btn").forEach(btn => {
                        btn.style.margin = "5px";
                        btn.style.padding = "5px 10px";
                        btn.style.border = "none";
                        btn.style.borderRadius = "6px";
                        btn.style.background = "#25d366";
                        btn.style.color = "#fff";
                        btn.style.cursor = "pointer";
                        btn.addEventListener("click", function () {
                            input.value = btn.dataset.msg;
                            form.dispatchEvent(new Event("submit"));
                        });
                    });
                }

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
