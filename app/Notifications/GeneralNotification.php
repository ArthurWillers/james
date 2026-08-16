<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $details  Dados adicionais chave-valor (ex: ['Valor' => 'R$ 150,00', 'Vencimento' => '20/08/2026'])
     * @param  array<int, string>  $channels  Canais de entrega desejados ('database', 'telegram', 'mail')
     */
    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly ?string $actionUrl = null,
        public readonly string $level = 'info',
        public readonly array $details = [],
        public readonly array $channels = ['database', 'telegram', 'mail'],
    ) {}

    /**
     * Canais de entrega ativos da notificação.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $activeChannels = [];

        if (in_array('database', $this->channels, true)) {
            $activeChannels[] = 'database';
        }

        if (in_array('mail', $this->channels, true) && ! empty($notifiable->email)) {
            $isMailEnabled = (bool) config('services.notifications.mail', false);

            if ($isMailEnabled) {
                $activeChannels[] = 'mail';
            }
        }

        if (in_array('telegram', $this->channels, true)) {
            $hasTelegramConfig = filled(config('services.telegram-bot-api.token'))
                && filled(config('services.telegram-bot-api.chat_id'));

            if ($hasTelegramConfig) {
                $activeChannels[] = TelegramChannel::class;
            }
        }

        return $activeChannels;
    }

    /**
     * Payload persistido na tabela notifications.
     *
     * @return array{title: string, message: string, action_url: string|null, level: string, details: array<string, mixed>}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'level' => $this->level,
            'details' => $this->details,
        ];
    }

    /**
     * Mensagem formatada e estilizada para o Bot do Telegram.
     */
    public function toTelegram(object $notifiable): TelegramMessage
    {
        $emoji = match ($this->level) {
            'success' => '✅',
            'warning' => '⚠️',
            'danger' => '🚨',
            default => '🔔',
        };

        $lines = ["{$emoji} *{$this->title}*", '', $this->message];

        if (! empty($this->details)) {
            $lines[] = '';
            $lines[] = '📋 *Detalhes:*';
            foreach ($this->details as $key => $value) {
                $formattedValue = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;
                $lines[] = "• *{$key}:* {$formattedValue}";
            }
        }

        $content = implode("\n", $lines);

        $telegram = TelegramMessage::create()
            ->to(config('services.telegram-bot-api.chat_id'))
            ->content($content);

        if ($this->actionUrl) {
            $telegram->button('Acessar no Sistema', $this->actionUrl);
        }

        return $telegram;
    }

    /**
     * E-mail transacional formatado nativamente pelo Laravel Mail.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("[James] {$this->title}")
            ->greeting('Olá, '.($notifiable->name ?? 'usuário').'!');

        if ($this->level === 'danger') {
            $mail->error();
        }

        $mail->line($this->message);

        if (! empty($this->details)) {
            $mail->line('---');
            foreach ($this->details as $key => $value) {
                $formattedValue = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;
                $mail->line("**{$key}:** {$formattedValue}");
            }
            $mail->line('---');
        }

        if ($this->actionUrl) {
            $mail->action('Acessar no Sistema', $this->actionUrl);
        }

        $mail->salutation('Atenciosamente, James');

        return $mail;
    }
}
