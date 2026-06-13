# V-Pulse ⚡️

**V-Pulse** is an advanced, self-contained diagnostic and DevOps assistant designed exclusively for the **VJECTS Ecosystem**. It continuously monitors your infrastructure, background queues, AI modules, and basic security settings, ensuring your application runs flawlessly without silent failures.

## 🚀 Key Features

V-Pulse is not just a passive dashboard; it is an active assistant that can automatically resolve common DevOps issues.

- **8 Intelligent Sensors**:
  - `DatabaseChecker`: Verifies DB connection and checks for missing baseline data (Seed check).
  - `QueueChecker`: Monitors pending backlog and failed jobs.
  - `MailChecker`: Verifies SMTP socket connections.
  - `CacheChecker`: Ensures the application is not using slow cache drivers (file/database) in production.
  - `RedisChecker`: Verifies high-speed Redis connectivity.
  - `ApiConnectionChecker`: Checks network connectivity to the centralized API ecosystem.
  - `SecurityChecker`: Enforces production security standards (e.g., APP_DEBUG=false).
  - `TelegramChecker`: Validates Telegram Bot API and Proxy configuration.

- **Automated Fix Actions**: 
  - One-click injection of default seed data.
  - One-click processing of backed-up queues (`queue:work --stop-when-empty`).
  - One-click queue retries (`queue:retry all`).
  - One-click cache optimization.

- **Zero-Impact Performance**: 
  - Extremely lightweight architecture using Laravel Cache.
  - Asynchronous lazy-loading of error logs.
  - Background polling intervals strictly capped to avoid server load.

- **AI Log Analysis**: Deep integration with the VJECTS AI system to automatically analyze complex stack traces and suggest fixes.

## 📂 Architecture & Storage

To prevent infinite loops during database failures, V-Pulse **does not rely on MySQL** for its core settings. 
Instead, it uses a localized, flat-file architecture located in `storage/app/`:
- `vpulse.json` - Core settings, language preferences, and module flags.
- `vpulse_ai.json` - AI analysis history and states.

## 🛠️ Onboarding & Setup

When installing V-Pulse on a new environment, the **Gatekeeper Middleware** is automatically injected into your Filament Admin Panel.

1. **Automatic Gatekeeper:**
   The `Vjects\Pulse\PulsePlugin` automatically intercepts any request to the admin panel and forcefully redirects the admin to V-Pulse if it hasn't been configured yet. No manual middleware installation is required!
2. **Crash-Proof Your Widgets:**
   Because V-Pulse operates on a raw environment initially, external databases (like `vjects_ai` or `vjects_ecosystem`) might be missing. You MUST wrap your custom Filament Dashboard Widget queries (e.g., `AiVokenPurchase::count()`) in a `try/catch` block. Otherwise, Filament will crash before the admin can even reach the V-Pulse Dashboard to fix the errors!
3. **Configure the Scope:**
   Once inside, the admin must navigate to **Scope Settings** and select the environment, architecture, and language. Upon saving, V-Pulse unlocks the diagnostic sensors.

> **Warning for Developers**: Do NOT manually alter `vpulse.json` unless absolutely necessary. Rely on the Filament dashboard for configuration.

## 📋 Common Expected Errors (Local Environment)

V-Pulse is highly aggressive in production but understands local development constraints. When `system_environment` is set to `local`, the following errors are **expected and ignored**:
- **API Network Timeout (cURL 28)**: Standard `php artisan serve` cannot ping itself asynchronously. This is normal.
- **APP_DEBUG Alert**: Having Debug mode ON is completely expected in local environments.
- **Slow Cache Driver**: Local setups using `file` cache will not trigger critical warnings.

---
*Built with ❤️ for the VJECTS Ecosystem.*
