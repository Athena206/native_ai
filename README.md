# SBI AI Hackathon Chatbot

An AI-powered banking chatbot concept for helping customers discover digital banking features, get guided support, and reach a human agent when needed.

> **Project status:** Planning / initial scaffold. The architecture and features below describe the intended implementation; they are not a claim that the integrations or banking operations are already implemented.

## Overview

The chatbot is designed to provide conversational, around-the-clock support through web, mobile, and WhatsApp channels. It combines intent recognition and retrieval-based answers with an optional large language model (LLM) for context-aware guidance. Conversation context and feedback can be stored to improve continuity and help teams evaluate answer quality.

### Goals

- Help customers adopt digital banking features with clear, step-by-step instructions.
- Answer common banking questions in a responsive, conversational style.
- Maintain a consistent experience across supported channels.
- Escalate sensitive, complex, or unresolved requests to a human support agent.
- Capture feedback and interaction data for ongoing quality improvements.

## Intended Architecture

```text
Web / Mobile / WhatsApp
          |
          v
Channel adapters and webhook API
          |
          v
FastAPI application
  ├── Intent recognition and answer retrieval
  ├── Optional LLM response generation
  ├── Human handoff and feedback handling
  └── Conversation and customer context
          |
          ├── PostgreSQL: structured application data
          └── MongoDB: conversation history and profile context
```

The backend is planned in Python with FastAPI. The web chat experience is planned as a React component. External LLM, messaging, and database integrations should be enabled only through explicitly configured credentials and services.

## Planned Features

- **Natural-language support:** Identify customer intent from plain-language messages.
- **Guided answers:** Provide relevant answers and instructions for using digital banking features.
- **Multi-channel interface:** Support web, mobile application, and WhatsApp integrations through channel adapters.
- **Conversation context:** Retain relevant history and preferences, subject to consent and retention policies.
- **Human handoff:** Escalate requests that are sensitive, complex, or cannot be handled confidently.
- **Feedback loop:** Record answer feedback and operational metrics for review and improvement.

## Proposed Project Structure

The following is a suggested layout for the initial implementation:

```text
.
├── backend/
│   ├── app/
│   │   ├── api/             # Chat and health endpoints
│   │   ├── services/        # Intent, retrieval, LLM, and handoff logic
│   │   ├── db/              # PostgreSQL and MongoDB access
│   │   └── main.py          # FastAPI application
│   ├── tests/
│   └── requirements.txt
├── frontend/
│   ├── src/
│   │   └── components/      # React chat widget
│   └── package.json
├── .env.example
├── Dockerfile
└── README.md
```

## Technology Plan

| Area | Planned technology |
| --- | --- |
| API | Python, FastAPI |
| Web interface | React, HTML, CSS |
| Structured data | PostgreSQL |
| Conversation history and context | MongoDB |
| Language understanding and generation | Intent recognition, retrieval, optional GPT or Claude integration |
| Channels | Web SDK, mobile application integration, WhatsApp Business API |
| Deployment | Docker; AWS or compatible cloud platform |

## Implementation Roadmap

1. **MVP**
   - Establish the backend and frontend project structure.
   - Add a basic chat endpoint and web chat widget.
   - Implement a small set of intents and retrieval-based answers.
   - Define the channel adapter interface.
2. **Intelligence and integrations**
   - Add an LLM provider behind a configurable service interface.
   - Connect PostgreSQL and MongoDB as appropriate for the data model.
   - Add conversation context, feedback capture, and human handoff.
   - Test privacy, safety, and failure handling.
3. **Deployment**
   - Containerize the services.
   - Configure production secrets and observability.
   - Deploy to the selected cloud platform and validate channel integrations.

## Development Setup

The project includes a PHP chatbot API backed by Groq and a browser-based chat interface. Run it with PHP 8+ and the cURL extension. MySQL and PDO MySQL are optional; when configured, the API saves chat history.

The starter MySQL database schema and FAQ seed data are in [sbi_chatbot_db.sql](./sbi_chatbot_db.sql). With MySQL 8.0+ installed and a user authorized to create databases, run:

```sh
mysql -u <your_mysql_user> -p < sbi_chatbot_db.sql
```

This script creates `sbi_chatbot_db` and its `users`, `chats`, and `faqs` tables. The MySQL schema is a starter database script; the intended production architecture described above separately proposes PostgreSQL and MongoDB.

### Local credentials

Copy `config.local.php.example` to `config.local.php` and add your newly generated Groq API key to the `groq_api_key` value. The local config is excluded from Git, and credentials must never be placed in browser-side code or committed:

```text
return [
    'groq_api_key' => 'your_new_groq_api_key',
    'database' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'user' => 'your_database_user',
        'password' => 'your_database_password',
    ],
];
```

Environment variables override local settings: `GROQ_API_KEY`, `SBI_DB_HOST`, `SBI_DB_PORT`, `SBI_DB_USER`, and `SBI_DB_PASSWORD`. The API sends each message to Groq using the `qwen/qwen3.8-27b` model and returns the completion as JSON. The previous `llama-3.3-70b-versatile` model has been retired from this Groq account's available models. TLS certificate verification is enabled by default. If local XAMPP testing fails because PHP cannot locate a CA bundle, configure cURL's CA bundle where possible; as a temporary local-only workaround, set `GROQ_SSL_VERIFY=0` in the PHP server environment and restart it. Never disable TLS verification in production.

The database is optional for answering chat requests. If it is unavailable or unconfigured, the AI answer is still returned and the API logs a warning that chat history was not saved. When using history, configure the database credentials in `config.local.php` or with the environment variables above.

To test the database connection and print its status in the terminal, run:

```sh
php test_db_connection.php
```

Example use:

```php
require_once __DIR__ . '/db_connection.php';

try {
    $pdo = getDatabaseConnection();
    // Use $pdo with prepared statements.
} catch (RuntimeException $exception) {
    // Handle configuration or connection failure without exposing credentials.
}
```

[api.php](./api.php) exposes the chatbot endpoint. Send a `POST` request with `Content-Type: application/json` and a JSON body containing a non-empty string `message` (maximum 16 KB):

```sh
curl -X POST http://localhost/api.php \
  -H "Content-Type: application/json" \
  -d '{"message":"How do I make a fund transfer?"}'
```

The endpoint returns the AI completion as `{"reply":"...","answer":"..."}` and, when database logging is configured, records both messages in `chats` in one transaction; because requests are anonymous, both records use `NULL` for `user_id`. `answer` is retained for compatibility with the included chat interface. The endpoint accepts cross-origin POST requests; restrict the allowed origin before deployment if it should only be called by a specific website. The SQL script makes this column nullable for both new and existing databases.

[index.html](./index.html) provides the responsive browser chat interface. Serve the project directory through a PHP-enabled web server (rather than opening the file directly) so `fetch('api.php')` can reach the endpoint. For example, from the project directory run `php -S localhost:8000` and open `http://localhost:8000/`.

Do not commit credentials. Store provider keys, database URLs, and messaging credentials in a local environment file or a secrets manager, and provide only placeholder variable names in `.env.example`.

## Security and Responsible Use

- Do not request or store passwords, PINs, one-time passcodes, full card numbers, or other authentication secrets in chat.
- Do not treat an LLM response as authorization to perform a financial transaction.
- Require authenticated, server-side authorization and appropriate confirmation for any future account-specific action.
- Escalate suspected fraud, account compromise, disputes, and other sensitive cases to an approved support channel.
- Minimize personal data, restrict access, encrypt data in transit and at rest, and define retention and deletion policies before storing conversation history.
- Verify applicable banking, privacy, and security requirements before connecting real customer data or production systems.

## License

No license has been specified yet. Add a `LICENSE` file before distributing or reusing this project.
