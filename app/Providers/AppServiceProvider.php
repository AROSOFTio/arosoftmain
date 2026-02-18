<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Models\User;
use App\Policies\BlogPostPolicy;
use App\Services\TutorialVideoService;
use App\Support\AdminSettings;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            return route('admin.password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);
        });

        Gate::policy(BlogPost::class, BlogPostPolicy::class);

        Gate::define('manage-blog', function (User $user): bool {
            if ($user->is_admin) {
                return true;
            }

            $emails = array_map(
                static fn (string $email): string => strtolower(trim($email)),
                AdminSettings::get('admin_emails', config('blog.admin_emails', []))
            );

            return in_array(strtolower((string) $user->email), $emails, true);
        });

        View::composer('layouts.app', function ($view): void {
            $data = $view->getData();
            $tutorialVideoService = app(TutorialVideoService::class);

            if (!array_key_exists('tutorialVideos', $data)) {
                $view->with(
                    'tutorialVideos',
                    $tutorialVideoService->latest(8)
                );
            }

            if (!array_key_exists('tutorialPlaylists', $data)) {
                $view->with(
                    'tutorialPlaylists',
                    $tutorialVideoService->latestPlaylists(6)
                );
            }
        });
    }
}
