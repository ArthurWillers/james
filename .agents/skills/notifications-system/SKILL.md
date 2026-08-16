---
name: notifications-system
description: "Use this skill whenever creating, sending, or testing notifications in James. Covers the GeneralNotification class, NotificationLevel Enum, multi-channel delivery (Database, Telegram, Mail), structured details, action URLs, and fail-gracefully rules."
---

# Notifications System (James)

## Overview

The James notification infrastructure provides a unified, multi-channel notification pipeline. Notifications are always recorded internally in the database (visible on the `/notifications` dashboard) and optionally mirrored to **Telegram** and **E-mail**.

---

## When to Activate

- Activate when creating background jobs, schedulers, or actions that notify the user (e.g. invoice imports, settlement alerts, daily/monthly financial digests, backup status).
- Activate when modifying `GeneralNotification`, `NotificationLevel`, notification controllers, views, or channels.
- Activate when writing tests for any notification feature.

---

## Enum: `NotificationLevel`

Namespace: `App\Enums\NotificationLevel`

Cases:
- `NotificationLevel::Info` (`'info'`): Blue badge, `heroicon-o-information-circle`.
- `NotificationLevel::Success` (`'success'`): Green badge, `heroicon-o-check-circle`.
- `NotificationLevel::Warning` (`'warning'`): Yellow badge, `heroicon-o-exclamation-triangle`.
- `NotificationLevel::Danger` (`'danger'`): Red badge, `heroicon-o-exclamation-circle`.

Methods:
- `->label()`: Returns 'Informativo', 'Sucesso', 'Alerta', 'Atenção'.
- `->color()`: Returns 'blue', 'green', 'yellow', 'red'.
- `->icon()`: Returns corresponding Heroicon string.

---

## Core Class: `GeneralNotification`

Namespace: `App\Notifications\GeneralNotification`

### Constructor Signature

```php
public function __construct(
    public readonly string $title,
    public readonly string $message,
    public readonly ?string $actionUrl = null,
    NotificationLevel|string $level = NotificationLevel::Info,
    public readonly array $details = [],    // Key-value array of structured metadata
    public readonly array $channels = ['database', 'telegram', 'mail'],
)
```

---

## Usage Examples

### 1. Simple Informational Notification (Database Only)

```php
use App\Enums\NotificationLevel;
use App\Notifications\GeneralNotification;

$user->notify(new GeneralNotification(
    title: 'Backup Realizado',
    message: 'A rotina periódica de backup foi finalizada com êxito.',
    level: NotificationLevel::Info,
    channels: ['database']
));
```

### 2. Actionable Warning Notification (Multi-Channel with Details)

```php
use App\Enums\NotificationLevel;
use App\Notifications\GeneralNotification;

$user->notify(new GeneralNotification(
    title: 'Fatura de Cartão Fechada',
    message: 'A fatura do seu cartão fechou e já está disponível para conferência.',
    actionUrl: route('financial.cards.index'),
    level: NotificationLevel::Warning,
    details: [
        'Cartão' => 'Nubank Ultravioleta',
        'Valor Total' => 'R$ 4.850,20',
        'Vencimento' => '25/08/2026',
        'Status' => 'Aguardando Pagamento',
    ],
    channels: ['database', 'telegram', 'mail']
));
```

### 3. Critical Alert / Danger Notification

```php
use App\Enums\NotificationLevel;
use App\Notifications\GeneralNotification;

$user->notify(new GeneralNotification(
    title: 'Falha na Conciliação Bancária',
    message: 'Não foi possível processar o arquivo OFX importado devido a inconsistências no cabeçalho.',
    actionUrl: route('financial.transactions.index'),
    level: NotificationLevel::Danger,
    details: [
        'Arquivo' => 'extrato_agosto.ofx',
        'Conta' => 'Banco do Brasil',
        'Erro' => 'Formato de data inválido',
    ]
));
```

---

## Channel Delivery & Fail-Gracefully Rules

1. **Database (`database`)**:
   - Always enabled if requested in `$channels`.
   - Populates the `notifications` table, feeds the sidebar unread counter badge and the `/notifications` panel.

2. **Telegram (`telegram`)**:
   - Active only if `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHAT_ID` are configured in `.env`.
   - Formats message with clean bold title, uppercase level prefix, formatted bulleted details, and action link.
   - For `localhost` action URLs, safely outputs link in text body (avoiding Telegram Bot API 400 rejection).

3. **E-mail (`mail`)**:
   - Active only if `NOTIFICATIONS_MAIL_ENABLED=true` in `.env` and recipient has an email.
   - Renders a responsive Laravel Markdown transactional email (`MailMessage`) with header, details, and action button.

---

## Testing Conventions

- **Automated Tests (`pest` / `phpunit`)**:
  - `phpunit.xml` automatically sets `TELEGRAM_BOT_TOKEN=""` and `NOTIFICATIONS_MAIL_ENABLED=false` so test runs never hit external APIs or send real messages.
  - Use `Notification::fake()` when asserting that a notification was queued or sent by a job/controller.

```php
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification;

it('notifies user when settlement is closed', function () {
    Notification::fake();

    // Trigger action ...

    Notification::assertSentTo($user, GeneralNotification::class, function ($notification) {
        return $notification->title === 'Acerto Finalizado';
    });
});
```

---

## Do and Don't

### Do:
- Use `App\Enums\NotificationLevel` enum cases (`NotificationLevel::Info`, `NotificationLevel::Success`, etc.).
- Use clear, user-friendly Portuguese text for `title` and `message`.
- Provide meaningful `details` (key-value) instead of stuffing data into long unstructured message strings.
- Pass `actionUrl` with named routes (e.g. `route('financial.transactions.index')`).

### Don't:
- Don't use emojis in notification messages; use Heroicons on UI.
- Don't pass raw JSON strings as message or title.
- Don't hardcode full URLs; always use `route(...)` helper.
- Don't force `mail` or `telegram` if a background event only matters as an internal audit/database log.

