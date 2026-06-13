# V-Pulse System Diagnostics

V-Pulse is a powerful, dynamic, and multi-lingual system diagnostics package for Laravel and Filament v3. It acts as the heartbeat monitor for your entire software ecosystem.

## 🚀 Features

- **Distributed Ecosystem Support**: Monitor single monolith apps or an array of distributed API nodes simultaneously.
- **Environment Awareness**: Automatically ignores safe failures (like APP_DEBUG) when running in local development mode, and translates them intelligently.
- **AI Diagnostics Assistant**: Integrates with LLMs (Qwen, OpenAI GPT, Google Gemini) to analyze failing components and instantly provide step-by-step resolution plans in native Filament Modals.
- **Built-in Localization**: Fully independent translation system without overriding Laravel's core language files. Supports Persian (فارسی) and English interfaces and logs natively.
- **Telegram Alerting**: Send individual, detailed alert messages to Telegram directly from the dashboard. Supports MTProto/HTTP proxies for restricted regions.
- **Native Logging System**: Keeps a chronological history of all errors and system health checks in the configured language, viewable directly from the dashboard.
- **Premium UI Widgets**: Includes high-end, cyberpunk-inspired Filament widgets for a stunning admin dashboard experience.

## ⚙️ Installation

You can install the package via composer:

```bash
composer require vjects/pulse
```

Publish the assets and upgrade Filament:

```bash
php artisan filament:upgrade
```

## 🧠 AI Assistant Setup

To enable the AI Assistant:
1. Navigate to the **V-Pulse System Diagnostics** page in your Filament admin panel.
2. Go to the **AI Assistant** tab.
3. Select your provider (Qwen, OpenAI, or Google).
4. Enter the model name (e.g., `qwen3.6-flash`, `gpt-4o`, `gemini-1.5-pro`).
5. Enter your API Key.
6. Click the "AI Analysis" button on any failing module to receive a step-by-step fix in a popup modal.

## 🌍 Multi-Language & Environment

V-Pulse is fully localized. You can change the language directly in the **General Scope** settings tab. 
If you set the **Environment Type** to `Local`, V-Pulse intelligently bypasses strict security and network checks to prevent false alarms during development.

### 💡 Local Development Quirks (ارورهای طبیعی در لوکال)

When testing V-Pulse locally using PHP's built-in server (`php artisan serve`), you might encounter certain expected errors:

- **API Connection Timeout (cURL Error 28):** PHP's built-in server is single-threaded. When the dashboard loads, it cannot simultaneously respond to an API ping request to itself (`localhost:8000/api/health`). This causes a cURL timeout. This is completely normal and proves your network code works! **If Environment is set to Local, V-Pulse will automatically ignore this error and turn the sensor green.**
- **Security Check Failures:** In local development, `APP_DEBUG` is usually true. V-Pulse will detect this as a vulnerability in Production, but will safely ignore it in Local mode.

*(در صورتی که نوع محیط را روی `Local` قرار دهید، تمامی این خطاهای طبیعی نادیده گرفته می‌شوند تا باعث مزاحمت در هنگام کدنویسی نشوند.)*

## 📊 Live System Logs

Every failed diagnostic check is recorded in a dedicated `vpulse.log` file. You can view these logs directly in the **System Logs** tab on the dashboard, completely translated into your selected interface language.

---
Engineered by VJECTS Digital Reality.
