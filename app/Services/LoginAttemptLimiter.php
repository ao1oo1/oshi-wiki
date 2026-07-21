<?php

namespace App\Services;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginAttemptLimiter
{
    public function ensureNotLocked(
        Request $request,
        string $email
    ): void {
        $accountKey = $this->accountKey($request, $email);
        $ipKey = $this->ipKey($request);

        $accountLimit = max(
            1,
            (int) config(
                'security.login.account_max_attempts',
                5
            )
        );

        $ipLimit = max(
            $accountLimit,
            (int) config(
                'security.login.ip_max_attempts',
                20
            )
        );

        if (
            ! RateLimiter::tooManyAttempts(
                $accountKey,
                $accountLimit
            )
            && ! RateLimiter::tooManyAttempts(
                $ipKey,
                $ipLimit
            )
        ) {
            return;
        }

        event(new Lockout($request));

        $seconds = max(
            RateLimiter::availableIn($accountKey),
            RateLimiter::availableIn($ipKey)
        );

        throw ValidationException::withMessages([
            'email' => sprintf(
                'ログインに複数回失敗したため、一時的に制限しています。約%d分後に再度お試しください。',
                max(1, (int) ceil($seconds / 60))
            ),
        ]);
    }

    public function hit(
        Request $request,
        string $email
    ): void {
        $decay = max(
            60,
            (int) config(
                'security.login.decay_seconds',
                900
            )
        );

        RateLimiter::hit(
            $this->accountKey($request, $email),
            $decay
        );

        RateLimiter::hit(
            $this->ipKey($request),
            $decay
        );
    }

    public function clear(
        Request $request,
        string $email
    ): void {
        RateLimiter::clear(
            $this->accountKey($request, $email)
        );

        RateLimiter::clear(
            $this->ipKey($request)
        );
    }

    public function accountKey(
        Request $request,
        string $email
    ): string {
        return 'login:account:'.sha1(
            Str::lower(trim($email))
            .'|'
            .(string) $request->ip()
        );
    }

    public function ipKey(Request $request): string
    {
        return 'login:ip:'.sha1(
            (string) $request->ip()
        );
    }
}
