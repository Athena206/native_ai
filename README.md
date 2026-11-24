# Native AI — Banking Support Portal & Chatbot

An AI-powered digital banking support portal and conversational assistant built with **PHP 8+**, **MySQL**, and **Groq LLM** (`qwen/qwen3.8-27b`). It helps users explore banking FAQs, visual digital-banking guides, interactive financial calculators, and real-time multilingual AI assistance with text-to-speech playback.

---

## Implemented Features

### 1. Side-by-Side AI Chatbot
- **Always-Accessible Assistant**: Once a user logs in (or via the quick-help drawer in the navigation menu), the **Native AI** chatbot opens beside the main portal with the greeting *"How can I help you?"*.
- **Groq LLM Integration (`api.php`)**: Sends user queries to Groq (`qwen/qwen3.8-27b`) and returns structured JSON responses (`{"reply": "...", "answer": "..."}`), while logging user and bot messages to MySQL (`chats` table) when configured.
- **Voice Playback (`tts.php`)**: Built-in **Listen** controls on FAQ answers and chat responses with server-side TTS proxy support (`en`, `hi`, `bn`) and browser speech fallback.

### 2. User Authentication (`auth.php`)
- **Session-Based Sign Up & Log In**: Modal-based authentication (`#auth-modal`) backed by MySQL `users` table (`name`, `email`, `password_hash`, `phone`), HTTP-only `Lax` session cookies, and automatic schema migration via `ensureUsersAuthSchema()`.
- **Authenticated Experience**: Displays the signed-in user chip and logout control in the header, and surfaces the side-by-side chatbot automatically on login.

### 3. Multilingual Support (`EN` / `HI` / `BN`)
- Live language switcher supporting **English**, **Hindi (`हिन्दी`)**, and **Bengali (`বাংলা`)** across portal navigation, hero content, FAQs, guides, and TTS playback.

### 4. Accessible Light & Dark Themes
- **High-Contrast Color System**: Carefully tuned palettes for both **Light Mode** (`#F8FAFC` background, `#FFFFFF` cards, `#0F294A` navy, `#0D9488` teal, `#0F172A` text) and **Dark Mode** (`#090D16` deep slate, `#111827` / `#182234` surfaces, `#2DD4BF` luminous teal, `#F1F5F9` text) to reduce eye strain.
- **Icon-Only Theme Toggle**: Persists user theme preference in `localStorage`.

### 5. Banking Self-Service & UI Enhancements
- **Interactive FAQs (`#faqs`)**: Smoothly animated `<details>` accordions (Web Animations API height + fade transitions, `prefers-reduced-motion` aware) with **Listen** audio buttons.
- **Picture Services (`#services`) & Step Guides (`#steps`)**: Visual cards and step-by-step digital banking instructions.
- **Financial Calculator (`#calculator`)**: Interactive banking calculator panel.
- **Compact Navigation Menu (`#chatbot-dropdown`)**: Aligned, compact hamburger menu with Navigation links, Quick Help chatbot drawer, Account actions, and **About Developer**.
- **About Developer Modal (`#developer-modal`)**: Accessible from the menu in both guest and logged-in states, showcasing developer profile details (Sangita Sinha — BCA 3rd Year · Hackathon Project).
- **Static Corner Brand Badge**: Fixed `Native AI` badge pinned to the bottom-left corner across scrolling.

---

## Project Structure

```text
.
├── index.php                  # Main portal UI, themes, modals, multilingual JS, and chatbot interface
├── api.php                    # Groq LLM chat endpoint (POST JSON) + MySQL chat logging
├── auth.php                   # Session-based Sign Up / Log In / Log Out & users schema migration
├── tts.php                    # Text-to-Speech proxy endpoint (?q=...&lang=en|hi|bn)
├── db_connection.php          # PDO MySQL connection helper + local config loader
├── test_db_connection.php     # CLI script to verify MySQL connectivity
├── sbi_chatbot_db.sql         # Starter MySQL 8.0+ schema (users, chats, faqs) + seed FAQs
├── config.local.php.example   # Template for local Groq API key and MySQL credentials
└── README.md
```

---

## Manual Setup & Running Locally

### Prerequisites
- **PHP 8.0+** with `pdo_mysql` and `curl` extensions enabled
- **MySQL 8.0+** (or MariaDB) running on `127.0.0.1:3306`

### 1. Import the Database
Start MySQL (e.g., via XAMPP or Windows Services) and import [sbi_chatbot_db.sql](./sbi_chatbot_db.sql):

```sh
mysql -u <your_mysql_user> -p < sbi_chatbot_db.sql
```

This creates `sbi_chatbot_db` along with the `users`, `chats`, and `faqs` tables and starter FAQ data.

### 2. Configure Local Credentials
Copy [config.local.php.example](./config.local.php.example) to `config.local.php` (excluded from Git via `.gitignore`) and fill in your Groq API key and MySQL credentials (a non-empty `user` and `password` are required by `db_connection.php`):

```php
<?php

declare(strict_types=1);

return [
    'groq_api_key' => 'your_groq_api_key',
    'database' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'user' => 'your_database_user',
        'password' => 'your_database_password',
    ],
];
```

Environment variables (`GROQ_API_KEY`, `SBI_DB_HOST`, `SBI_DB_PORT`, `SBI_DB_USER`, `SBI_DB_PASSWORD`) override `config.local.php` if set.

> **TLS Note for Local Testing:** TLS certificate verification is enabled by default for Groq requests. If local XAMPP/Windows testing fails due to a missing CA bundle, configure `curl.cainfo` in `php.ini`; as a temporary local-only workaround, you can set `GROQ_SSL_VERIFY=0` in the PHP environment (never disable TLS verification in production).

### 3. Test Database Connection (Optional)
Run from the project root:

```sh
php test_db_connection.php
```

Expected output: `Database connection successful.`

### 4. Start the PHP Server
From the project directory, run:

```sh
php -S localhost:8000
```

Then open **`http://localhost:8000/`** in your browser.

---

## API Endpoints

- **`POST /api.php`** (`Content-Type: application/json`)
  - Body: `{"message": "How do I make a fund transfer?"}` (non-empty string, max 16 KB)
  - Response: `{"reply": "...", "answer": "..."}`
- **`POST /auth.php`**
  - Handles user registration, login, and logout using PHP sessions and MySQL `users`.
- **`GET /tts.php?q=<text>&lang=<en|hi|bn>`**
  - Returns audio stream (up to 200 characters per request) for FAQ and chatbot speech playback.

---

## Security and Responsible Use

- Never commit `config.local.php` or place API keys / database credentials in client-side code.
- Do not request or store passwords, PINs, OTPs, or full card numbers in chat messages.
- Do not treat an LLM response as authorization for financial transactions; escalate sensitive account issues or suspected fraud to official bank support channels.
- Restrict CORS (`Access-Control-Allow-Origin`) in `api.php` before deploying to production.

## License

No license has been specified yet. Add a `LICENSE` file before distributing or reusing this project.
