document.addEventListener("DOMContentLoaded", function () {
  const btn = document.getElementById("chatbot-button");
  const box = document.getElementById("chatbot-box");
  const form = document.getElementById("chat-form");
  const input = document.getElementById("chat-input");
  const chat = document.getElementById("chat-content");

  btn.addEventListener("click", () => {
      box.style.display = box.style.display === "none" ? "block" : "none";
  });

  form.addEventListener("submit", function (e) {
      e.preventDefault();
      const mensaje = input.value.trim();
      if (!mensaje) return;

      chat.innerHTML += `<div><strong>Tú:</strong> ${mensaje}</div>`;
      input.value = "";

      fetch(chatpd_ajax.ajax_url, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
              action: "chatpd_ask",
              nonce: chatpd_ajax.nonce,
              pregunta: mensaje
          })
      })
      .then(res => res.json())
      .then(data => {
          if (data.success) {
              const botMessage = data.data.replace(
                /(https?:\/\/[^\s]+)/g,
                '<a href="$1" target="_blank" style="color:#25d366;">$1</a>'
              );
              chat.innerHTML += `<div><strong>Bot:</strong> ${botMessage}</div>`;    
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
            
                // Agregar eventos a los botones
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
          }
      });
  });
});
