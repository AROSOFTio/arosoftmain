<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminForgotPasswordController;
use App\Http\Controllers\Admin\AdminResetPasswordController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BlogDashboardController;
use App\Http\Controllers\Admin\BlogMediaController;
use App\Http\Controllers\Admin\BlogPostController as AdminBlogPostController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogFeedController;
use App\Http\Controllers\BlogMediaController as PublicBlogMediaController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ToolsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])
    ->middleware('throttle:240,1')
    ->name('search.suggestions');
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/search', [BlogController::class, 'search'])->name('blog.search');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{slug}', [BlogController::class, 'tag'])->name('blog.tag');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/media/blog/{path}', [PublicBlogMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('blog.media');
Route::get('/storage/blog/{path}', [PublicBlogMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('blog.media.legacy');

Route::get('/sitemap.xml', [BlogFeedController::class, 'sitemap'])->name('sitemap');
Route::get('/rss.xml', [BlogFeedController::class, 'rss'])->name('rss');

Route::get('/services', [ServicesController::class, 'index'])->name('services');
Route::post('/services/quote', [ServicesController::class, 'generateQuote'])->middleware('throttle:20,1')->name('services.quote');
Route::post('/services/quote/pay', [ServicesController::class, 'payQuote'])->middleware('throttle:12,1')->name('services.quote.pay');
Route::get('/services/quote/{quoteId}/status', [ServicesController::class, 'quoteStatus'])->name('services.quote.status');
Route::get('/payments/pesapal/callback', [ServicesController::class, 'paymentCallback'])->name('services.payment.callback');
Route::match(['get', 'post'], '/payments/pesapal/ipn', [ServicesController::class, 'paymentIpn'])->name('services.payment.ipn');
Route::get('/services/printing', [PageController::class, 'printing'])->name('services.printing');
Route::get('/services/website-design', [PageController::class, 'websiteDesign'])->name('services.website-design');
Route::get('/services/web-development', [PageController::class, 'webDevelopment'])->name('services.web-development');
Route::get('/services/training-courses', [PageController::class, 'trainingCourses'])->name('services.training-courses');
Route::get('/tutorials', [PageController::class, 'tutorials'])->name('tutorials');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:10,1')->name('contact.send');
Route::get('/tools', [ToolsController::class, 'index'])->name('tools');
Route::get('/tools/{slug}', [ToolsController::class, 'show'])->name('tools.show');
Route::post('/tools/{slug}/process', [ToolsController::class, 'process'])
    ->middleware('throttle:12,1')
    ->name('tools.process');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])
            ->middleware('throttle:8,1')
            ->name('login.store');

        Route::get('/forgot-password', [AdminForgotPasswordController::class, 'create'])->name('password.request');
        Route::post('/forgot-password', [AdminForgotPasswordController::class, 'store'])
            ->middleware('throttle:8,1')
            ->name('password.email');
        Route::get('/reset-password/{token}', [AdminResetPasswordController::class, 'create'])->name('password.reset');
        Route::post('/reset-password', [AdminResetPasswordController::class, 'store'])
            ->middleware('throttle:8,1')
            ->name('password.update');
    });

    Route::middleware(['auth', 'can:manage-blog'])->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

        Route::get('/', fn () => redirect()->route('admin.blog.dashboard'))->name('index');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');

        Route::prefix('blog')->name('blog.')->group(function (): void {
            Route::get('/', BlogDashboardController::class)->name('dashboard');
            Route::post('/media/upload', [BlogMediaController::class, 'store'])->name('media.upload');
            Route::get('/posts/{blogPost}/preview', [AdminBlogPostController::class, 'preview'])->name('posts.preview');
            Route::resource('/posts', AdminBlogPostController::class)
                ->parameters(['posts' => 'blogPost'])
                ->except(['show']);
        });
    });
});
