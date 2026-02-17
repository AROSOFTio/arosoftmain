<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderByDesc('is_admin')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => strtolower($request->string('email')->toString()),
            'password' => $request->string('password')->toString(),
            'is_admin' => $request->boolean('is_admin'),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created successfully.');
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        $payload = [
            'name' => $request->string('name')->toString(),
            'email' => strtolower($request->string('email')->toString()),
            'is_admin' => $request->boolean('is_admin'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = $request->string('password')->toString();
        }

        $user->update($payload);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ((int) auth()->id() === (int) $user->id) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['users' => 'You cannot delete your own account while logged in.']);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User deleted successfully.');
    }
}

