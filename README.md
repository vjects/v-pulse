<p align="center">
  <img src="https://ui-avatars.com/api/?name=V-Pulse&background=0D8ABC&color=fff&size=128&rounded=true" alt="V-Pulse Logo">
</p>

<h1 align="center">V-Pulse System Diagnostics</h1>

<p align="center">
  <strong>Advanced Self-Diagnostic & Autonomous Operations Engine for VJECTS Ecosystem</strong><br>
  Designed for extreme resilience, isolation, and intelligent error handling.
</p>

---

## 📖 Table of Contents
- [Overview](#-overview)
- [Architecture & Features](#-architecture--features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Available Modules](#-available-modules)
  - [Core Diagnostics](#1-core-diagnostics)
  - [Security Scanner](#2-security-scanner)
  - [Telegram MTProto](#3-telegram-mtproto-fallback)
  - [AI Agent Integration](#4-ai-agent-integration)
- [Writing Custom Checkers](#-writing-custom-checkers)
- [Copyright & License](#-copyright--license)

---

## 🚀 Overview

**V-Pulse** acts as the central nervous system ("Safe Mode") for your application. It operates in a fully isolated `try-catch` sandbox, guaranteeing that even if the primary database crashes, the `.env` file is misconfigured, or external microservices go offline, the V-Pulse Dashboard will **always** load to provide emergency actions.

## 🏗 Architecture & Features

- **Isolated Execution (Sandbox):** Fatal application errors do not affect V-Pulse.
- **Dynamic Scope (Monolith vs. Ecosystem):** Automatically toggles network ecosystem checks based on the project scale.
- **Action-Driven Resolution:** Provides direct Filament buttons to execute artisan commands (e.g., `Run Migrations`, `Clear Cache`).
- **Distributed Master-Agent Handoff:** In Ecosystem mode, V-Pulse delegates heavy infrastructure checks to the API processing nodes.

## ⚙️ Requirements

- **PHP:** `^8.1`
- **Laravel:** `^10.0` or `^11.0`
- **Filament PHP:** `^3.0`

## 📦 Installation

Since this is a private internal package for the VJECTS ecosystem, add the local path repository to your host application's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/vjects/pulse"
    }
]
```

Then install via Composer:

```bash
composer require vjects/pulse
```

## 🛠 Configuration

Register the Plugin in your Filament Panel Provider (usually `app/Providers/Filament/AdminPanelProvider.php`):

```php
use Vjects\Pulse\PulsePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            PulsePlugin::make(),
        ]);
}
```

> **Note:** V-Pulse settings are saved securely in `storage/app/vpulse.json` to bypass any database dependency issues.

---

## 🧩 Available Modules

V-Pulse runs tests in a strict cascading priority order.

### 1. Core Diagnostics
Checks fundamental infrastructure: Database connection, migration status, and cache availability.

### 2. Security Scanner
Audits application security configurations:
- Detects if `APP_DEBUG` is active in production.
- Ensures environment is not left on `local`.
- Verifies API Rate Limiting (`throttle` middleware) is active to prevent DDoS and Brute Force attacks.

### 3. Telegram MTProto Fallback
Pings the Telegram API for notification readiness. If the server is in a restricted region (e.g., Iran), V-Pulse automatically falls back to the configured **MTProto/HTTP Proxy** tunnel to guarantee alert delivery.

### 4. AI Agent Integration
Extracts error stack traces and sends them securely to an LLM provider (OpenAI, Google Gemini, Qwen). The AI Agent analyzes the fatal error and suggests the exact terminal command or code fix directly in the dashboard.

---

## 💻 Writing Custom Checkers

You can extend V-Pulse to monitor custom application logic. 
Create a class extending `BaseChecker` and implement your logic:

```php
namespace App\Checkers;

use Vjects\Pulse\Checkers\BaseChecker;

class PaymentGatewayChecker extends BaseChecker
{
    public function getName(): string { return 'Payment Gateway Check'; }
    public function getDescription(): string { return 'Pings the banking API.'; }
    
    public function run(): array
    {
        // Your isolated check logic here
        return ['success' => true, 'message' => 'Gateway is online.'];
    }
}
```

Register it in your `AppServiceProvider`:

```php
app('vjects-pulse')->registerChecker(\App\Checkers\PaymentGatewayChecker::class);
```

---

### v1.2.0 (Dual-Server Architecture & Deep Scanning)
- **Ecosystem Route Scanning**: The 500-Error Route Crawler now natively supports dual-server (Main + API) architectures. When `Ecosystem Mode` is active, V-Pulse automatically reaches into the API server's workspace to perform parallel HTTP 500 checks, presenting a unified diagnostic report.
- **Filament Syntax Auto-Correction Compatibility**: Resolved deeply rooted `Target class [auth] does not exist` bugs by restructuring the crawler's application container initialization, bypassing multi-guard conflicts during isolated HTTP simulations.

> **Note on Future Updates:** V-Pulse is now in a stable base functional state. Further customizations and business-logic integrations will be rolled out in upcoming updates. If you encounter any anomalies or errors during production use, please report them to the development team immediately for hotfixing.

---

<p align="center">
  <strong>&copy; 2026 VJECTS Ecosystem. All rights reserved.</strong><br>
  <em>Designed exclusively for <a href="https://vjects.com">VJECTS.com</a> architectures.</em><br>
  Built with a Warrior Mindset.
</p>

