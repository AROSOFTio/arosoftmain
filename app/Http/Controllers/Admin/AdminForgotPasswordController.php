<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminForgotPasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AdminForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function store(AdminForgotPasswordRequest $request): RedirectResponse
    {
        $email = strtolower(trim((string) $request->validated('email')));
        $user = User::query()->where('email', $email)->first();

        if ($user && $user->can('manage-blog')) {
            $status = Password::broker('users')->sendResetLink([
                'email' => $email,
            ]);

            if ($status !== Password::RESET_LINK_SENT) {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
            }
        }

        return back()->with('status', 'If that account exists, a reset link has been sent.');
    }
}
