(function () {
  const apiEndpoint = 'includes/ai-complete.php';
  let isOpen = false;
  let isSending = false;
  let greeted = false;

  const style = document.createElement('style');
  style.textContent = `
  .ai-chat-toggle { position: fixed; bottom: 18px; right: 18px; z-index: 1600; background: #ff5007; color: #fff; border: none; border-radius: 999px; padding: 12px 16px; box-shadow: 0 12px 28px rgba(0,0,0,0.22); display: flex; gap: 8px; align-items: center; font-weight: 700; letter-spacing: 0.4px; cursor: pointer; }
  .ai-chat-toggle:hover { background: #e64703; }
  .ai-chat-panel { position: fixed; bottom: 80px; right: 18px; width: min(360px, 94vw); max-height: 70vh; background: #0f0f0f; color: #f5f5f5; border-radius: 16px; box-shadow: 0 16px 38px rgba(0,0,0,0.35); display: none; flex-direction: column; overflow: hidden; z-index: 1600; border: 1px solid rgba(255,255,255,0.08); }
  .ai-chat-panel.open { display: flex; }
  .ai-chat-header { padding: 14px 16px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); background: #141414; }
  .ai-chat-header h6 { margin: 0; font-size: 14px; letter-spacing: 0.3px; }
  .ai-chat-messages { padding: 12px 14px; display: flex; flex-direction: column; gap: 10px; overflow-y: auto; flex: 1; background: radial-gradient(circle at 20% 20%, rgba(255,80,7,0.07), transparent 32%), #0f0f0f; }
  .ai-chat-msg { padding: 10px 12px; border-radius: 12px; max-width: 90%; font-size: 13px; line-height: 1.45; white-space: pre-wrap; word-break: break-word; }
  .ai-chat-msg.user { align-self: flex-end; background: #ff5007; color: #fff; }
  .ai-chat-msg.bot { align-self: flex-start; background: #1c1c1c; color: #f8f8f8; border: 1px solid rgba(255,255,255,0.05); }
  .ai-chat-input { display: flex; gap: 8px; padding: 12px 14px; border-top: 1px solid rgba(255,255,255,0.08); background: #141414; }
  .ai-chat-input textarea { flex: 1; resize: none; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); background: #0f0f0f; color: #fff; padding: 10px; min-height: 56px; max-height: 120px; font-size: 13px; }
  .ai-chat-input button { background: #ff5007; color: #fff; border: none; border-radius: 10px; padding: 0 16px; font-weight: 700; letter-spacing: 0.3px; }
  .ai-chat-input button:disabled { opacity: 0.7; cursor: not-allowed; }
  .ai-chat-error { color: #ffb4a2; font-size: 12px; }
  .ai-chat-spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: ai-chat-spin 0.8s linear infinite; }
  @keyframes ai-chat-spin { to { transform: rotate(360deg);} }
  `;
  document.head.appendChild(style);

  const toggle = document.createElement('button');
  toggle.className = 'ai-chat-toggle';
  toggle.type = 'button';
  toggle.innerHTML = '<span>Ask SoleSource</span>';

  const panel = document.createElement('div');
  panel.className = 'ai-chat-panel';
  panel.innerHTML = `
    <div class="ai-chat-header">
      <h6>SoleSource Assistant</h6>
      <button type="button" aria-label="Close" style="background:none;border:0;color:#fff;font-size:18px;line-height:1;">×</button>
    </div>
    <div class="ai-chat-messages" role="log" aria-live="polite"></div>
    <div class="ai-chat-input">
      <textarea placeholder="Ask about orders, shipping, products…" aria-label="Message"></textarea>
      <button type="button">Send</button>
    </div>
  `;

  const headerClose = panel.querySelector('.ai-chat-header button');
  const messages = panel.querySelector('.ai-chat-messages');
  const textarea = panel.querySelector('textarea');
  const sendBtn = panel.querySelector('.ai-chat-input button');

  function appendMessage(text, role) {
    const div = document.createElement('div');
    div.className = `ai-chat-msg ${role}`;
    div.textContent = text;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  function setSending(state) {
    isSending = state;
    sendBtn.disabled = state;
    textarea.disabled = state;
    if (state) {
      sendBtn.innerHTML = '<span class="ai-chat-spinner"></span>';
    } else {
      sendBtn.textContent = 'Send';
    }
  }

  function greetIfNeeded() {
    if (greeted) return;
    appendMessage('Hi! I can help with SoleSource orders, shipping, returns, or product questions.', 'bot');
    greeted = true;
  }

  async function sendMessage() {
    const text = textarea.value.trim();
    if (!text || isSending) return;
    appendMessage(text, 'user');
    textarea.value = '';
    setSending(true);

    try {
      const res = await fetch(apiEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text }),
      });
      const json = await res.json();
      if (!json.ok) {
        if (json.raw) {
          console.error('AI error raw:', json.raw);
        }
        appendMessage('Sorry, that did not work. Tap Send to try again.', 'bot');
      } else if (json.data) {
        const pretty = typeof json.data === 'object' ? JSON.stringify(json.data, null, 2) : String(json.data);
        appendMessage(pretty, 'bot');
      } else {
        appendMessage('No response received. Please try again.', 'bot');
      }
    } catch (err) {
      appendMessage('Network error. Please try again.', 'bot');
    } finally {
      setSending(false);
      textarea.focus();
    }
  }

  function togglePanel() {
    isOpen = !isOpen;
    panel.classList.toggle('open', isOpen);
    if (isOpen) {
      greetIfNeeded();
      textarea.focus();
    }
  }

  toggle.addEventListener('click', togglePanel);
  headerClose.addEventListener('click', togglePanel);
  sendBtn.addEventListener('click', sendMessage);
  textarea.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  document.body.appendChild(toggle);
  document.body.appendChild(panel);
})();
