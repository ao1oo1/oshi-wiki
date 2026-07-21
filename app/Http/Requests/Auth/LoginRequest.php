<?php

namespace App\Http\Requests\Auth;

use App\Services\LoginAttemptLimiter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $email = (string) $this->input('email');
        $limiter = app(LoginAttemptLimiter::class);

        $limiter->ensureNotLocked(
            $this,
            $email
        );

        if (! Auth::attempt(
            $this->only('email', 'password'),
            $this->boolean('remember')
        )) {
            $limiter->hit(
                $this,
                $email
            );

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $limiter->clear(
            $this,
            $email
        );
    }
}
