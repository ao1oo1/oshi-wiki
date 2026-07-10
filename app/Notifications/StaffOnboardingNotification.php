<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffOnboardingNotification extends Notification
{
    public function __construct(
        private readonly string $resetUrl
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('【Oshi-Wiki】スタッフ登用開始のお知らせ')
            ->greeting('Oshi-Wikiスタッフ登用開始のお知らせです。')
            ->line('スタッフ申請を確認し、情報入力スタッフとしての登用を開始しました。')
            ->line('以下のボタンから初回パスワードを設定してください。')
            ->action('初回パスワードを設定する', $this->resetUrl)
            ->line('パスワード設定後、管理画面にログインして情報入力を開始できます。')
            ->line('このリンクの有効期限は60分です。')
            ->line('このメールに心当たりがない場合は、メールを破棄してください。');
    }
}
