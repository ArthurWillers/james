<?php

use App\Models\User;
use App\Notifications\GeneralNotification;
use NotificationChannels\Telegram\TelegramChannel;

it('sends only to the database channel when telegram is not configured', function () {
    config(['services.telegram-bot-api.token' => null]);
    config(['services.telegram-bot-api.chat_id' => null]);

    $notification = new GeneralNotification('Título', 'Mensagem');
    $user = new User;

    expect($notification->via($user))->toBe(['database']);
});

it('includes telegram channel when both token and chat_id are configured', function () {
    config(['services.telegram-bot-api.token' => 'fake-token']);
    config(['services.telegram-bot-api.chat_id' => '123456']);

    $notification = new GeneralNotification('Título', 'Mensagem');
    $user = new User;

    expect($notification->via($user))->toContain('database', TelegramChannel::class);
});

it('does not include telegram when only token is set', function () {
    config(['services.telegram-bot-api.token' => 'fake-token']);
    config(['services.telegram-bot-api.chat_id' => null]);

    $notification = new GeneralNotification('Título', 'Mensagem');
    $user = new User;

    expect($notification->via($user))->toBe(['database']);
});

it('returns correct toDatabase payload', function () {
    $notification = new GeneralNotification('Meu Título', 'Minha Mensagem', 'https://example.com');
    $user = new User;

    expect($notification->toDatabase($user))->toBe([
        'title' => 'Meu Título',
        'message' => 'Minha Mensagem',
        'action_url' => 'https://example.com',
    ]);
});

it('returns null action_url when not provided', function () {
    $notification = new GeneralNotification('Título', 'Mensagem');
    $user = new User;

    expect($notification->toDatabase($user)['action_url'])->toBeNull();
});
