<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffOnboardingNotification extends Notification
{
    public function __construct(
        private readonly string $loginUrl,
        private readonly string $email,
        private readonly string $temporaryPassword
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
            ->line('以下のログイン情報で、管理スタッフ用ログイン画面からログインしてください。')
            ->line('メールアドレス：' . $this->email)
            ->line('仮パスワード：' . $this->temporaryPassword)
            ->action('管理スタッフ用ログイン画面を開く', $this->loginUrl)
            ->line('初回ログイン後、パスワード変更画面へ移動します。')
            ->line('ご自身で新しいパスワードを設定したあと、再度ログインしてください。')
            ->line('仮パスワードは第三者に共有しないでください。')
            ->line('このメールに心当たりがない場合は、メールを破棄してください。');
    }
}
