<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function createWriter(): View
    {
        return view('auth.register', [
            'registerRoute' => 'writer.register.store',
            'loginRoute' => 'writer.login',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.unique' => 'こちらのメールアドレスは使用されています',
        ]);

        $writerRole = Role::where('name', 'writer')->first();

        if (! $writerRole) {
            $writerRole = new Role();
            $writerRole->name = 'writer';

            if (Schema::hasColumn('roles', 'label')) {
                $writerRole->label = '一般ユーザー';
            }

            if (Schema::hasColumn('roles', 'display_name')) {
                $writerRole->display_name = '一般ユーザー';
            }

            if (Schema::hasColumn('roles', 'description')) {
                $writerRole->description = 'AI執筆補助機能を利用する一般ユーザー';
            }

            $writerRole->save();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $writerRole->id,
            'status' => 'active',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('writer.dashboard', absolute: false));
    }
}
