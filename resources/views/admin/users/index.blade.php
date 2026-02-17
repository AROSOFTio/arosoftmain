@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[25rem_minmax(0,1fr)]">
        <section class="admin-card p-6">
            <h2 class="font-heading text-2xl">Add User</h2>
            <p class="mt-1 text-sm muted-copy">Create content editors or additional admins.</p>

            <form action="{{ route('admin.users.store') }}" method="post" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="name" class="form-label">Full name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required class="form-field">
                </div>
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-field">
                </div>
                <div>
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password" required class="form-field" minlength="8">
                </div>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_admin" value="1" class="h-4 w-4 rounded border-[color:rgba(17,24,39,0.3)]">
                    <span>Grant admin access</span>
                </label>
                <button type="submit" class="btn-solid !w-full !text-[0.66rem]">Create User</button>
            </form>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[color:rgba(17,24,39,0.1)] px-5 py-4">
                <div>
                    <h2 class="font-heading text-2xl">Team Users</h2>
                    <p class="text-sm muted-copy">Manage profile, role, and password resets.</p>
                </div>
                <span class="nav-link-sm">{{ $users->total() }} users</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-[color:rgba(17,24,39,0.03)] text-left">
                            <th class="px-5 py-3 font-semibold">Name</th>
                            <th class="px-5 py-3 font-semibold">Email</th>
                            <th class="px-5 py-3 font-semibold">Role</th>
                            <th class="px-5 py-3 font-semibold">Updated</th>
                            <th class="px-5 py-3 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php($formId = 'user-form-'.$user->id)
                            <tr class="border-t border-[color:rgba(17,24,39,0.08)] align-top">
                                <td class="px-5 py-4">
                                    <input type="text" form="{{ $formId }}" name="name" value="{{ old('name', $user->name) }}" class="form-field !py-2">
                                </td>
                                <td class="px-5 py-4">
                                    <input type="email" form="{{ $formId }}" name="email" value="{{ old('email', $user->email) }}" class="form-field !py-2">
                                </td>
                                <td class="px-5 py-4">
                                    <label class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.08em]">
                                        <input type="checkbox" form="{{ $formId }}" name="is_admin" value="1" @checked($user->is_admin) class="h-4 w-4 rounded border-[color:rgba(17,24,39,0.3)]">
                                        <span>{{ $user->is_admin ? 'Admin' : 'Editor' }}</span>
                                    </label>
                                    <input type="password" form="{{ $formId }}" name="password" placeholder="New password (optional)" class="form-field !mt-2 !py-2">
                                </td>
                                <td class="px-5 py-4 text-xs muted-faint">
                                    {{ optional($user->updated_at)->format('M d, Y H:i') }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <form id="{{ $formId }}" action="{{ route('admin.users.update', $user) }}" method="post">
                                            @csrf
                                            @method('put')
                                            <button type="submit" class="btn-outline !w-auto !px-3 !py-2 !text-[0.62rem]">Save</button>
                                        </form>
                                        @if((int) auth()->id() !== (int) $user->id)
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="post" onsubmit="return confirm('Delete this user?');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn-outline !w-auto !px-3 !py-2 !text-[0.62rem]">Delete</button>
                                            </form>
                                        @else
                                            <span class="text-xs muted-faint">Current account</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-5 muted-faint">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[color:rgba(17,24,39,0.08)] px-5 py-4">
                {{ $users->links() }}
            </div>
        </section>
    </div>
@endsection
