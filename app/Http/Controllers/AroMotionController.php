<?php

namespace App\Http\Controllers;

use App\Models\AroMotionDevice;
use App\Models\AroMotionProject;
use App\Models\AroMotionSubscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AroMotionController extends Controller
{
    public function show(): View
    {
        return view('pages.aromotion.index', [
            'version' => config('aromotion.version'),
            'channel' => config('aromotion.channel'),
        ]);
    }

    public function auth(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('aromotion.dashboard');
        }

        return view('pages.aromotion.auth');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
            'is_admin' => false,
        ]);

        AroMotionSubscription::create([
            'user_id' => $user->id,
            'plan' => 'beta',
            'status' => 'active',
            'started_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('aromotion.dashboard')
            ->with('status', 'Your AROMOTION Cloud account is ready.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([
            'email' => strtolower($credentials['email']),
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'The email address or password is incorrect.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('aromotion.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('aromotion.show');
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $subscription = AroMotionSubscription::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['plan' => 'beta', 'status' => 'active', 'started_at' => now()]
        );

        return view('pages.aromotion.dashboard', [
            'subscription' => $subscription,
            'devices' => AroMotionDevice::query()
                ->where('user_id', $user->id)
                ->orderByDesc('last_seen_at')
                ->limit(20)
                ->get(),
            'projects' => AroMotionProject::query()
                ->where('user_id', $user->id)
                ->orderByDesc('last_synced_at')
                ->limit(20)
                ->get(),
            'version' => config('aromotion.version'),
            'channel' => config('aromotion.channel'),
        ]);
    }

    public function download(): BinaryFileResponse|RedirectResponse
    {
        $path = (string) config('aromotion.binary_path');

        if ($path === '' || ! is_file($path)) {
            return redirect()->route('aromotion.dashboard')
                ->with('download_error', 'The Windows build is being published to the download node. Please retry shortly.');
        }

        return response()->download(
            $path,
            (string) config('aromotion.download_name'),
            [
                'Content-Type' => 'application/vnd.microsoft.portable-executable',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function manifest(): JsonResponse
    {
        return response()->json([
            'product' => 'AROMOTION Studio',
            'version' => config('aromotion.version'),
            'channel' => config('aromotion.channel'),
            'minimum_supported_version' => config('aromotion.minimum_supported_version'),
            'platforms' => ['windows-x64'],
            'download_page' => route('aromotion.account'),
            'release_notes' => config('aromotion.release_notes'),
            'cloud' => [
                'activation' => route('aromotion.api.activate'),
                'heartbeat' => route('aromotion.api.heartbeat'),
                'project_sync' => route('aromotion.api.projects.sync'),
            ],
        ]);
    }

    public function activate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'string'],
            'device_id' => ['required', 'string', 'max:120'],
            'device_name' => ['nullable', 'string', 'max:180'],
            'platform' => ['nullable', 'string', 'max:40'],
            'app_version' => ['nullable', 'string', 'max:40'],
        ]);

        $user = User::query()->where('email', strtolower($validated['email']))->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['ok' => false, 'message' => 'Invalid account credentials.'], 401);
        }

        $subscription = AroMotionSubscription::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['plan' => 'beta', 'status' => 'active', 'started_at' => now()]
        );

        if ($subscription->status !== 'active') {
            return response()->json([
                'ok' => false,
                'message' => 'Your AROMOTION subscription is not active.',
                'status' => $subscription->status,
            ], 403);
        }

        $plainToken = Str::random(64);

        $device = AroMotionDevice::query()->updateOrCreate(
            ['user_id' => $user->id, 'device_uuid' => $validated['device_id']],
            [
                'device_name' => $validated['device_name'] ?? 'Windows PC',
                'platform' => $validated['platform'] ?? 'windows',
                'app_version' => $validated['app_version'] ?? null,
                'token_hash' => hash('sha256', $plainToken),
                'activated_at' => now(),
                'last_seen_at' => now(),
                'revoked_at' => null,
            ]
        );

        return response()->json([
            'ok' => true,
            'token' => $plainToken,
            'account' => ['name' => $user->name, 'email' => $user->email],
            'subscription' => ['plan' => $subscription->plan, 'status' => $subscription->status],
            'device' => ['id' => $device->device_uuid, 'name' => $device->device_name],
            'latest_version' => config('aromotion.version'),
        ]);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $device = $this->deviceFromBearer($request);

        if (! $device) {
            return response()->json(['ok' => false, 'message' => 'Invalid device token.'], 401);
        }

        $device->forceFill([
            'last_seen_at' => now(),
            'app_version' => $request->string('app_version')->toString() ?: $device->app_version,
        ])->save();

        $subscription = AroMotionSubscription::query()->where('user_id', $device->user_id)->first();

        return response()->json([
            'ok' => true,
            'subscription' => [
                'plan' => $subscription?->plan ?? 'beta',
                'status' => $subscription?->status ?? 'active',
            ],
            'latest_version' => config('aromotion.version'),
            'minimum_supported_version' => config('aromotion.minimum_supported_version'),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function syncProject(Request $request): JsonResponse
    {
        $device = $this->deviceFromBearer($request);

        if (! $device) {
            return response()->json(['ok' => false, 'message' => 'Invalid device token.'], 401);
        }

        $validated = $request->validate([
            'project_id' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:240'],
            'duration_ms' => ['nullable', 'integer', 'min:0'],
            'size_bytes' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:32'],
            'app_version' => ['nullable', 'string', 'max:40'],
        ]);

        $project = AroMotionProject::query()->updateOrCreate(
            ['user_id' => $device->user_id, 'project_uuid' => $validated['project_id']],
            [
                'name' => $validated['name'],
                'duration_ms' => $validated['duration_ms'] ?? 0,
                'size_bytes' => $validated['size_bytes'] ?? 0,
                'status' => $validated['status'] ?? 'local',
                'app_version' => $validated['app_version'] ?? $device->app_version,
                'last_synced_at' => now(),
            ]
        );

        $device->forceFill(['last_seen_at' => now()])->save();

        return response()->json([
            'ok' => true,
            'project' => [
                'id' => $project->project_uuid,
                'name' => $project->name,
                'last_synced_at' => $project->last_synced_at?->toIso8601String(),
            ],
        ]);
    }

    private function deviceFromBearer(Request $request): ?AroMotionDevice
    {
        $token = trim((string) $request->bearerToken());

        if ($token === '') {
            return null;
        }

        return AroMotionDevice::query()
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->first();
    }
}
