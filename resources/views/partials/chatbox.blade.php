<style>
    .va-chat-fab {
        position: fixed;
        right: 18px;
        bottom: 18px;
        z-index: 500;
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .75rem 1rem;
        border-radius: 999px;
        border: 1px solid rgba(15,118,110,.25);
        background: #0f766e;
        color: #fff;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 14px 36px rgba(2, 132, 199, .18), 0 10px 22px rgba(15,118,110,.28);
    }
    .va-chat-fab:active { transform: scale(.99); }
    .va-chat-panel {
        position: fixed;
        right: 18px;
        bottom: 78px;
        width: min(420px, calc(100vw - 36px));
        height: min(560px, calc(100vh - 140px));
        z-index: 520;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 28px 70px rgba(15,23,42,.18);
        display: none;
        flex-direction: column;
    }
    .va-chat-open .va-chat-panel { display: flex; }
    .va-chat-header {
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        color: #fff;
        padding: .95rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }
    .va-chat-header strong { font-size: .95rem; }
    .va-chat-close {
        border: 0;
        background: rgba(255,255,255,.16);
        color: #fff;
        font-weight: 900;
        width: 36px; height: 36px;
        border-radius: .6rem;
        cursor: pointer;
    }
    .va-chat-body {
        padding: .9rem .9rem 0;
        overflow: auto;
        flex: 1;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 60%);
    }
    .va-chat-msg {
        max-width: 88%;
        padding: .65rem .8rem;
        border-radius: .85rem;
        margin: 0 0 .65rem;
        white-space: pre-line;
        font-size: .92rem;
        line-height: 1.45;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #0f172a;
    }
    .va-chat-msg.me {
        margin-left: auto;
        background: rgba(20,184,166,.10);
        border-color: rgba(20,184,166,.25);
    }
    .va-chat-quick {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        padding: .2rem .9rem .9rem;
        background: #fff;
        border-top: 1px solid #f1f5f9;
    }
    .va-chat-chip {
        border: 1px solid rgba(15,118,110,.22);
        background: #fff;
        color: #0f766e;
        font-weight: 800;
        padding: .4rem .65rem;
        border-radius: 999px;
        cursor: pointer;
        font-size: .82rem;
    }
    .va-chat-footer {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: .6rem;
        padding: .75rem .9rem;
        border-top: 1px solid #e2e8f0;
        background: #fff;
    }
    .va-chat-input {
        width: 100%;
        border-radius: .65rem;
        border: 1px solid #cbd5e1;
        padding: .65rem .75rem;
        outline: none;
        font-size: .95rem;
    }
    .va-chat-send {
        border: 0;
        border-radius: .65rem;
        background: #0f766e;
        color: #fff;
        font-weight: 900;
        padding: .65rem .9rem;
        cursor: pointer;
    }
    .va-chat-send[disabled] { opacity: .6; cursor: not-allowed; }
    @media (max-width: 420px) {
        .va-chat-fab { padding: .72rem .9rem; }
        .va-chat-fab span { display: none; }
    }
</style>

<div id="va-chat-root" class="va-chat-root" aria-live="polite">
    <button type="button" class="va-chat-fab" id="va-chat-toggle" aria-haspopup="dialog" aria-expanded="false">
        <span>¿Dudas de planes?</span> Chat
    </button>

    <section class="va-chat-panel" id="va-chat-panel" role="dialog" aria-label="Chat VentasÁgil" aria-modal="false">
        <div class="va-chat-header">
            <div>
                <strong>Asistente VentasÁgil</strong>
                <div style="font-size:.8rem;opacity:.92">Planes, módulos y cómo se usa</div>
            </div>
            <button type="button" class="va-chat-close" id="va-chat-close" aria-label="Cerrar">✕</button>
        </div>

        <div class="va-chat-body" id="va-chat-body"></div>
        <div class="va-chat-quick" id="va-chat-quick"></div>

        <form class="va-chat-footer" id="va-chat-form">
            <input class="va-chat-input" id="va-chat-input" autocomplete="off" placeholder="Escribe: precio, módulos, SUNAT..." />
            <button class="va-chat-send" id="va-chat-send" type="submit">Enviar</button>
        </form>
    </section>
</div>

<script>
(function () {
    const toggle = document.getElementById('va-chat-toggle');
    const panel = document.getElementById('va-chat-panel');
    const closeBtn = document.getElementById('va-chat-close');
    const body = document.getElementById('va-chat-body');
    const form = document.getElementById('va-chat-form');
    const input = document.getElementById('va-chat-input');
    const quick = document.getElementById('va-chat-quick');
    const sendBtn = document.getElementById('va-chat-send');

    if (!toggle || !panel || !closeBtn || !body || !form || !input || !quick || !sendBtn) return;

    const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    const endpoint = @json(route('chatbot.ask'));

    function setOpen(open) {
        document.body.classList.toggle('va-chat-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) setTimeout(() => input.focus(), 50);
    }

    function addMsg(text, mine) {
        const el = document.createElement('div');
        el.className = 'va-chat-msg' + (mine ? ' me' : '');
        el.textContent = text;
        body.appendChild(el);
        body.scrollTop = body.scrollHeight;
    }

    function setQuickReplies(items) {
        quick.innerHTML = '';
        (items || []).slice(0, 8).forEach(label => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'va-chat-chip';
            b.textContent = label;
            b.addEventListener('click', () => {
                input.value = label;
                form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            });
            quick.appendChild(b);
        });
    }

    async function ask(message) {
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ message }),
        });
        if (!res.ok) throw new Error('bad_response');
        return await res.json();
    }

    toggle.addEventListener('click', () => {
        const open = !document.body.classList.contains('va-chat-open');
        setOpen(open);
        if (open && body.childNodes.length === 0) {
            addMsg('Hola. Pregúntame por planes, módulos o cómo se usa VentasÁgil.', false);
            setQuickReplies(['Ver planes', '¿Qué módulos necesito?', '¿Cómo empiezo?', 'Hablar por WhatsApp']);
        }
    });

    closeBtn.addEventListener('click', () => setOpen(false));
    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = (input.value || '').trim();
        if (!msg) return;
        input.value = '';
        addMsg(msg, true);
        sendBtn.disabled = true;
        try {
            const data = await ask(msg);
            addMsg((data && data.reply) ? data.reply : 'Listo.', false);
            setQuickReplies((data && data.quick_replies) ? data.quick_replies : null);
        } catch (err) {
            addMsg('No pude responder en este momento. Intenta nuevamente o escríbenos por WhatsApp.', false);
            setQuickReplies(['Hablar por WhatsApp', 'Ver planes']);
        } finally {
            sendBtn.disabled = false;
        }
    });
})();
</script>

