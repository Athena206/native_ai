<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#003366">
    <title>SBI AI Assistant</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #172b4d;
            background: #f2f5f9;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            --sbi-blue: #003366;
            --sbi-blue-light: #e8f1fb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .chat-card {
            display: flex;
            flex-direction: column;
            width: min(100%, 760px);
            height: min(780px, calc(100vh - 48px));
            min-height: 440px;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e3e9f0;
            border-radius: 20px;
            box-shadow: 0 18px 55px rgb(20 43 73 / 10%);
        }

        .chat-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 24px;
            color: #fff;
            background: var(--sbi-blue);
        }

        .assistant-mark {
            display: grid;
            width: 44px;
            height: 44px;
            flex: 0 0 auto;
            place-items: center;
            border: 1px solid rgb(255 255 255 / 28%);
            border-radius: 14px;
            background: rgb(255 255 255 / 10%);
        }

        .assistant-mark svg {
            width: 23px;
            height: 23px;
        }

        .header-copy h1 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 650;
            letter-spacing: 0.01em;
        }

        .header-copy p {
            margin: 5px 0 0;
            color: rgb(255 255 255 / 78%);
            font-size: 0.84rem;
        }

        .online-indicator {
            width: 9px;
            height: 9px;
            margin-left: auto;
            border-radius: 50%;
            background: #68dfa1;
            box-shadow: 0 0 0 4px rgb(104 223 161 / 15%);
        }

        .messages {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 18px;
            overflow-y: auto;
            padding: 26px clamp(16px, 5vw, 38px);
            overscroll-behavior: contain;
            scroll-behavior: smooth;
        }

        .message-row {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            max-width: min(88%, 560px);
        }

        .message-row.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message-avatar {
            display: grid;
            width: 30px;
            height: 30px;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 50%;
            color: var(--sbi-blue);
            background: var(--sbi-blue-light);
            font-size: 0.68rem;
            font-weight: 700;
        }

        .message-row.user .message-avatar {
            color: #fff;
            background: #5379a2;
        }

        .message-bubble {
            padding: 12px 15px;
            border: 1px solid #e7edf4;
            border-radius: 16px 16px 16px 5px;
            background: #f7f9fc;
            color: #263b56;
            font-size: 0.94rem;
            line-height: 1.55;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        .message-row.user .message-bubble {
            border-color: var(--sbi-blue);
            border-radius: 16px 16px 5px 16px;
            background: var(--sbi-blue);
            color: #fff;
        }

        .message-row.error .message-bubble {
            border-color: #f0d0ce;
            background: #fff7f6;
            color: #862f2a;
        }

        .message-row.pending .message-bubble {
            color: #687c94;
            font-style: italic;
        }

        .composer {
            padding: 16px clamp(16px, 5vw, 32px) 13px;
            border-top: 1px solid #edf0f4;
            background: #fff;
        }

        .composer-tools {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 9px;
        }

        .language-select {
            min-height: 36px;
            padding: 0 34px 0 11px;
            border: 1px solid #dce4ed;
            border-radius: 10px;
            background: #fff;
            color: #263b56;
            font: inherit;
            font-size: 0.82rem;
            cursor: pointer;
        }

        .language-select:focus-visible,
        .voice-button:focus-visible {
            outline: 3px solid rgb(0 51 102 / 20%);
            outline-offset: 2px;
        }

        .composer-form {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px;
            border: 1px solid #dce4ed;
            border-radius: 15px;
            background: #fbfcfe;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .composer-form:focus-within {
            border-color: #6b8fb5;
            box-shadow: 0 0 0 3px rgb(0 51 102 / 9%);
        }

        .composer input {
            width: 100%;
            min-width: 0;
            padding: 11px 12px;
            border: 0;
            outline: 0;
            background: transparent;
            color: #172b4d;
            font: inherit;
            font-size: 0.94rem;
        }

        .composer input::placeholder {
            color: #8492a4;
        }

        .send-button {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 0 17px;
            border: 0;
            border-radius: 11px;
            background: var(--sbi-blue);
            color: #fff;
            font: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 150ms ease, transform 150ms ease;
        }

        .send-button:hover:not(:disabled) {
            background: #06477f;
        }

        .send-button:active:not(:disabled) {
            transform: scale(0.98);
        }

        .send-button:disabled {
            cursor: wait;
            opacity: 0.72;
        }

        .send-button svg {
            width: 16px;
            height: 16px;
        }

        .voice-button {
            display: inline-grid;
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            place-items: center;
            border: 1px solid #dce4ed;
            border-radius: 11px;
            background: #fff;
            color: var(--sbi-blue);
            cursor: pointer;
            transition: background 150ms ease, border-color 150ms ease, color 150ms ease;
        }

        .voice-button:hover:not(:disabled) {
            background: var(--sbi-blue-light);
        }

        .voice-button.is-listening {
            border-color: #b42318;
            background: #fff1f0;
            color: #b42318;
        }

        .voice-button:disabled {
            cursor: not-allowed;
            opacity: 0.55;
        }

        .voice-button svg {
            width: 19px;
            height: 19px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .privacy-note {
            margin: 10px 4px 0;
            color: #7d8998;
            font-size: 0.75rem;
            line-height: 1.4;
            text-align: center;
        }

        @media (max-width: 520px) {
            body {
                display: block;
                padding: 0;
            }

            .chat-card {
                width: 100%;
                height: 100vh;
                height: 100dvh;
                min-height: 0;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .chat-header {
                padding: 16px 18px;
            }

            .messages {
                padding-top: 20px;
                padding-bottom: 20px;
            }

            .message-row {
                max-width: 94%;
            }

            .composer {
                padding-bottom: max(12px, env(safe-area-inset-bottom));
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <main class="chat-card" aria-label="SBI AI banking assistant chat">
        <header class="chat-header">
            <div class="assistant-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" role="img">
                    <path d="M12 3.5 19 7v5.2c0 4.2-2.9 7.1-7 8.3-4.1-1.2-7-4.1-7-8.3V7l7-3.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="header-copy">
                <h1>SBI AI Assistant</h1>
                <p>Here to help with digital banking</p>
            </div>
            <span class="online-indicator" role="img" aria-label="Assistant available"></span>
        </header>

        <section
            class="messages"
            id="messages"
            role="log"
            aria-label="Chat messages"
            aria-live="polite"
            aria-relevant="additions text"
        >
            <div class="message-row bot">
                <span class="message-avatar" aria-hidden="true">AI</span>
                <div class="message-bubble">Hello! I’m your SBI AI Assistant. Ask me about digital banking, fund transfers, or YONO app help.</div>
            </div>
        </section>

        <footer class="composer">
            <div class="composer-tools">
                <label class="sr-only" for="voice-language">Voice input language</label>
                <select class="language-select" id="voice-language" name="voice-language">
                    <option value="en-US">English</option>
                    <option value="hi-IN">हिन्दी (Hindi)</option>
                    <option value="bn-IN">বাংলা (Bengali)</option>
                </select>
            </div>
            <form class="composer-form" id="chat-form">
                <label class="sr-only" for="message-input">Your message</label>
                <input
                    id="message-input"
                    name="message"
                    type="text"
                    maxlength="16000"
                    placeholder="Type your message..."
                    autocomplete="off"
                    aria-label="Type your message"
                    required
                >
                <button
                    class="voice-button"
                    id="voice-button"
                    type="button"
                    aria-label="Start voice input"
                    aria-pressed="false"
                    title="Start voice input"
                >
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="9" y="3" width="6" height="12" rx="3" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M5.5 11.5a6.5 6.5 0 0 0 13 0M12 18v3m-4 0h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
                <button class="send-button" id="send-button" type="submit">
                    <span>Send</span>
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M3 10h13M10 4l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>
            <p class="sr-only" id="voice-status" aria-live="polite"></p>
            <p class="privacy-note">Never share your password, PIN, or one-time passcode in chat.</p>
        </footer>
    </main>

    <script>
        const form = document.getElementById('chat-form');
        const input = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        const voiceButton = document.getElementById('voice-button');
        const voiceLanguage = document.getElementById('voice-language');
        const voiceStatus = document.getElementById('voice-status');
        const messages = document.getElementById('messages');
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        let recognition = null;
        let isListening = false;

        if (SpeechRecognition) {
            recognition = new SpeechRecognition();
            recognition.lang = voiceLanguage.value;
            recognition.continuous = false;
            recognition.interimResults = false;

            voiceLanguage.addEventListener('change', () => {
                recognition.lang = voiceLanguage.value;
            });

            recognition.addEventListener('start', () => {
                isListening = true;
                voiceButton.classList.add('is-listening');
                voiceButton.setAttribute('aria-pressed', 'true');
                voiceButton.setAttribute('aria-label', 'Stop voice input');
                voiceButton.title = 'Stop voice input';
                voiceStatus.textContent = `Listening in ${voiceLanguage.selectedOptions[0].textContent}.`;
            });

            recognition.addEventListener('result', (event) => {
                const result = event.results[event.results.length - 1];
                if (!result.isFinal) {
                    return;
                }

                input.value = result[0].transcript.trim();
                if (input.value) {
                    recognition.stop();
                    sendMessage();
                }
            });

            recognition.addEventListener('error', (event) => {
                if (event.error !== 'aborted' && event.error !== 'no-speech') {
                    voiceStatus.textContent = 'Voice input could not start. Check microphone access and try again.';
                    appendMessage(voiceStatus.textContent, 'bot', 'error');
                }
            });

            recognition.addEventListener('end', () => {
                isListening = false;
                voiceButton.classList.remove('is-listening');
                voiceButton.setAttribute('aria-pressed', 'false');
                voiceButton.setAttribute('aria-label', 'Start voice input');
                voiceButton.title = 'Start voice input';
                voiceButton.disabled = sendButton.disabled;
                if (!sendButton.disabled) {
                    voiceStatus.textContent = '';
                }
            });

            voiceButton.addEventListener('click', () => {
                if (isListening) {
                    recognition.stop();
                    return;
                }

                recognition.lang = voiceLanguage.value;
                voiceStatus.textContent = '';
                try {
                    recognition.start();
                } catch (error) {
                    voiceStatus.textContent = 'Voice input could not start. Please try again.';
                    appendMessage(voiceStatus.textContent, 'bot', 'error');
                }
            });
        } else {
            voiceButton.disabled = true;
            voiceButton.title = 'Voice input is not supported in this browser';
            voiceStatus.textContent = 'Voice input is not supported in this browser.';
        }

        function appendMessage(text, sender, extraClass = '') {
            const row = document.createElement('div');
            row.className = `message-row ${sender}${extraClass ? ` ${extraClass}` : ''}`;

            const avatar = document.createElement('span');
            avatar.className = 'message-avatar';
            avatar.setAttribute('aria-hidden', 'true');
            avatar.textContent = sender === 'user' ? 'You' : 'AI';

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            bubble.textContent = text;

            row.append(avatar, bubble);
            messages.append(row);
            messages.scrollTop = messages.scrollHeight;
            return row;
        }

        async function sendMessage() {
            const message = input.value.trim();
            if (!message || sendButton.disabled) {
                return;
            }

            if (isListening) {
                recognition.stop();
            }
            appendMessage(message, 'user');
            input.value = '';
            input.focus();
            sendButton.disabled = true;
            input.disabled = true;
            voiceButton.disabled = true;
            sendButton.querySelector('span').textContent = 'Sending';
            const pendingMessage = appendMessage('Preparing a response…', 'bot', 'pending');

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message })
                });
                let payload;
                try {
                    payload = await response.json();
                } catch {
                    throw new Error('The assistant returned an invalid response. Please try again later.');
                }

                pendingMessage.remove();
                if (!response.ok) {
                    throw new Error(payload.error || 'The assistant could not process your message.');
                }
                if (typeof payload.answer !== 'string') {
                    throw new Error('The assistant returned an unexpected response.');
                }

                appendMessage(payload.answer, 'bot');
                if (typeof payload.warning === 'string') {
                    appendMessage(payload.warning, 'bot', 'error');
                }
            } catch (error) {
                pendingMessage.remove();
                appendMessage(
                    error instanceof TypeError
                        ? 'I couldn’t connect to the assistant. Please try again in a moment.'
                        : error.message,
                    'bot',
                    'error'
                );
            } finally {
                sendButton.disabled = false;
                input.disabled = false;
                voiceButton.disabled = !recognition;
                sendButton.querySelector('span').textContent = 'Send';
                input.focus();
            }
        }

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            sendMessage();
        });
    </script>
</body>
</html>
