<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly ?string $actionUrl = null,
    ) {}

    /**
     * Canais de entrega da notificação.
     *
     * O canal Telegram só é ativado quando as variáveis de ambiente estiverem
     * preenchidas — caso contrário, falha silenciosamente (fail gracefully).
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        $hasTelegramConfig = filled(config('services.telegram-bot-api.token'))
            && filled(config('services.telegram-bot-api.chat_id'));

        if ($hasTelegramConfig) {
            $channels[] = TelegramChannel::class;
        }

        return $channels;
    }

    /**
     * Payload persistido na tabela notifications.
     *
     * @return array{title: string, message: string, action_url: string|null}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
        ];
    }

    /**
     * Mensagem formatada para o Bot do Telegram.
     */
    public function toTelegram(object $notifiable): TelegramMessage
    {
        $telegram = TelegramMessage::create()
            ->to(config('services.telegram-bot-api.chat_id'))
            ->content("*{$this->title}*\n\n{$this->message}");

        if ($this->actionUrl) {
            $telegram->button('Abrir', $this->actionUrl);
        }

        return $telegram;
    }
}
