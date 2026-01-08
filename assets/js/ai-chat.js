(function () {
  const apiEndpoint = 'includes/ai-complete.php';
  let isOpen = false;
  let isSending = false;
  let greeted = false;
  let chatLog = [];
  let lastUserMessage = '';

  const STORAGE_VERSION = 'v4';
  const STORAGE_KEY = 'aiChatLog_' + STORAGE_VERSION;
  const OPEN_KEY = 'aiChatOpen_' + STORAGE_VERSION;
  const QUICK_PROMPTS = [
    'Where is my order?',
    'How do I return an item?',
    'How long is shipping?',
    'Can I change my address?'
  ];

  const style = document.createElement('style');
  style.textContent = `
  .ai-chat-toggle { position: fixed; bottom: 18px; right: 18px; z-index: 1600; background: #ff5007; color: #fff; border: none; border-radius: 999px; padding: 12px 16px; box-shadow: 0 12px 28px rgba(0,0,0,0.22); display: flex; gap: 8px; align-items: center; font-weight: 700; letter-spacing: 0.4px; cursor: pointer; }
  .ai-chat-toggle:hover { background: #e64703; }
  .ai-chat-panel { position: fixed; bottom: 80px; right: 18px; width: min(360px, 94vw); height: 70vh; max-height: 70vh; background: #0f0f0f; color: #f5f5f5; border-radius: 16px; box-shadow: 0 16px 38px rgba(0,0,0,0.35); display: none; flex-direction: column; overflow: hidden; z-index: 1600; border: 1px solid rgba(255,255,255,0.08); }
  .ai-chat-panel.open { display: flex; }
  .ai-chat-header { padding: 14px 16px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); background: #141414; }
  .ai-chat-header h6 { margin: 0; font-size: 14px; letter-spacing: 0.3px; }
  .ai-chat-prompts { display: flex; flex-wrap: wrap; gap: 8px; padding: 10px 14px; background: #141414; border-bottom: 1px solid rgba(255,255,255,0.08); }
  .ai-chat-prompts button { background: rgba(255,255,255,0.06); color: #fff; border: 1px solid rgba(255,255,255,0.12); border-radius: 999px; padding: 6px 10px; font-size: 12px; cursor: pointer; }
  .ai-chat-prompts button:hover { background: rgba(255,255,255,0.12); }
  .ai-chat-messages { padding: 12px 14px; display: flex; flex-direction: column; gap: 10px; overflow-y: auto; flex: 1; max-height: none; background: radial-gradient(circle at 20% 20%, rgba(255,80,7,0.07), transparent 32%), #0f0f0f; }
  .ai-chat-msg { padding: 10px 12px; border-radius: 12px; max-width: 90%; font-size: 13px; line-height: 1.45; white-space: pre-wrap; word-break: break-word; }
  .ai-chat-msg.user { align-self: flex-end; background: #ff5007; color: #fff; }
  .ai-chat-msg.bot { align-self: flex-start; background: #1c1c1c; color: #f8f8f8; border: 1px solid rgba(255,255,255,0.05); }
  .ai-chat-input { display: flex; gap: 8px; padding: 12px 14px; border-top: 1px solid rgba(255,255,255,0.08); background: #141414; }
  .ai-chat-input textarea { flex: 1; resize: none; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); background: #0f0f0f; color: #fff; padding: 10px; min-height: 56px; max-height: 120px; font-size: 13px; }
  .ai-chat-input button { background: #ff5007; color: #fff; border: none; border-radius: 10px; padding: 0 16px; font-weight: 700; letter-spacing: 0.3px; }
  .ai-chat-input button:disabled { opacity: 0.7; cursor: not-allowed; }
  .ai-chat-error { color: #ffb4a2; font-size: 12px; }
  .ai-chat-spinner { width: 16px; height: 16px; display: inline-block; border: 2px solid rgba(255,255,255,0.35); border-top-color: #fff; border-right-color: #fff; border-bottom-color: transparent; border-left-color: transparent; border-radius: 50%; animation: ai-chat-spin 0.8s linear infinite; }
  .ai-chat-retry { margin-top: 6px; }
  .ai-chat-retry button { background: transparent; border: 1px solid rgba(255,255,255,0.3); color: #fff; border-radius: 999px; padding: 4px 10px; font-size: 12px; cursor: pointer; }
  .ai-chat-retry button:hover { background: rgba(255,255,255,0.1); }
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
    <div class="ai-chat-prompts" role="group" aria-label="Suggested questions"></div>
    <div class="ai-chat-messages" role="log" aria-live="polite"></div>
    <div class="ai-chat-input">
      <textarea placeholder="Ask about orders, shipping, products…" aria-label="Message"></textarea>
      <button type="button">Send</button>
    </div>
  `;

  const headerClose = panel.querySelector('.ai-chat-header button');
  const promptsEl = panel.querySelector('.ai-chat-prompts');
  const messages = panel.querySelector('.ai-chat-messages');
  const textarea = panel.querySelector('textarea');
  const sendBtn = panel.querySelector('.ai-chat-input button');

  function saveState() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(chatLog.slice(-50)));
      localStorage.setItem(OPEN_KEY, isOpen ? '1' : '0');
    } catch (e) {
      // ignore storage errors
    }
  }

  function loadState() {
    try {
      const stored = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
      if (Array.isArray(stored)) {
        chatLog = stored;
        chatLog.forEach(msg => appendMessage(msg.text, msg.role, false));
      }
      isOpen = localStorage.getItem(OPEN_KEY) === '1';
      if (isOpen) {
        panel.classList.add('open');
      }
    } catch (e) {
      chatLog = [];
    }
  }

  function appendMessage(text, role, persist = true) {
    const div = document.createElement('div');
    div.className = `ai-chat-msg ${role}`;
    div.textContent = text;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
    if (persist) {
      chatLog.push({ role, text });
      saveState();
    }
  }

  function showRetry() {
    if (!lastUserMessage) return;
    const div = document.createElement('div');
    div.className = 'ai-chat-msg bot ai-chat-retry';
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = 'Try again';
    btn.addEventListener('click', () => {
      textarea.value = lastUserMessage;
      div.remove();
      sendMessage();
    });
    div.appendChild(btn);
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

  QUICK_PROMPTS.forEach((text) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = text;
    btn.addEventListener('click', () => {
      textarea.value = text;
      sendMessage(); // auto-send prompt
    });
    promptsEl.appendChild(btn);
  });

  function greetIfNeeded() {
    if (greeted || chatLog.length) return;
    appendMessage('Hey there! I’m your happy SoleSource helper. Ask me anything about your orders, shipping, returns, or products. ', 'bot');
    greeted = true;
  }

  async function sendMessage() {
    const text = textarea.value.trim();
    if (!text || isSending) return;
    appendMessage(text, 'user');
    lastUserMessage = text;
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
        showRetry();
      } else if (json.data) {
        const data = json.data;

        const pickReply = (payload) => {
          if (!payload) return '';
          if (typeof payload === 'string') {
            // try to parse stringified JSON
            try {
              const maybe = JSON.parse(payload);
              if (maybe && typeof maybe === 'object' && typeof maybe.reply === 'string') {
                return maybe.reply;
              }
            } catch (_) {
              return payload;
            }
            return payload;
          }
          if (typeof payload.reply === 'string' && payload.reply) return payload.reply;
          return JSON.stringify(payload, null, 2);
        };

        const reply = pickReply(data);
        appendMessage(reply || 'Here to help—ask away!', 'bot');

        // Apply safe, whitelisted UI actions
        if (Array.isArray(data.actions)) {
          const allow = new Set([
            'input[name="email"]',
            'input[name="phone"]',
            'input[name="full_name"]',
            'textarea[name="message"]',
            'input[name="order_number"]'
          ]);
          data.actions.forEach((action) => {
            if (!action || action.type !== 'setValue') return;
            const sel = action.selector;
            if (!allow.has(sel)) return;
            const el = document.querySelector(sel);
            if (el && typeof action.value === 'string') {
              el.value = action.value;
              el.dispatchEvent(new Event('input', { bubbles: true }));
            }
          });
        }
      } else {
        appendMessage('No response received. Please try again.', 'bot');
        showRetry();
      }
    } catch (err) {
      appendMessage('Network error. Please try again.', 'bot');
      showRetry();
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
    saveState();
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
  loadState();
  if (isOpen) {
    greetIfNeeded();
    textarea.focus();
  }
})();
