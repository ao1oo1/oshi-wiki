<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('【Oshi-Wiki】パスワード再設定のご案内')
                ->greeting('Oshi-Wikiをご利用いただきありがとうございます。')
                ->line('パスワード再設定のリクエストを受け付けました。')
                ->line('以下のボタンから、新しいパスワードを設定してください。')
                ->action('パスワードを再設定する', $url)
                ->line('このリンクの有効期限は60分です。')
                ->line('パスワード再設定をリクエストしていない場合は、このメールを破棄してください。');
        });

        //
    }
}
