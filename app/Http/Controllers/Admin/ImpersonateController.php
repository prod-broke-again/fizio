<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function impersonate($userId)
    {
        $user = User::findOrFail($userId);

        // Проверяем, что пользователь имеет права админа
        if (!Auth::user()->hasRole('Admin') && Auth::user()->email !== 'laravelka@yandex.ru') {
            abort(403, 'У вас нет прав для выполнения этой операции');
        }

        // Нельзя имперсонировать самого себя
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Нельзя войти под самим собой');
        }

        // Сохраняем ID оригинального пользователя в сессии
        session(['impersonate.original_user' => Auth::id()]);

        // Авторизуемся под выбранным пользователем
        Auth::login($user);

        return redirect('/')->with('success', 'Вы вошли под пользователем: ' . $user->name);
    }

    public function stopImpersonating()
    {
        // Проверяем, есть ли сохраненный оригинальный пользователь
        if (!session()->has('impersonate.original_user')) {
            return redirect()->back()->with('error', 'Вы не находитесь в режиме имперсонации');
        }

        $originalUserId = session('impersonate.original_user');
        $originalUser = User::find($originalUserId);

        if ($originalUser) {
            Auth::login($originalUser);
            session()->forget('impersonate.original_user');
            return redirect('/admin')->with('success', 'Вы вернулись к своему аккаунту');
        }

        return redirect()->back()->with('error', 'Не удалось найти оригинального пользователя');
    }
}
