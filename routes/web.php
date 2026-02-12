<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/services/printing', [PageController::class, 'printing'])->name('services.printing');
Route::get('/services/website-design', [PageController::class, 'websiteDesign'])->name('services.website-design');
Route::get('/services/web-development', [PageController::class, 'webDevelopment'])->name('services.web-development');
Route::get('/services/training-courses', [PageController::class, 'trainingCourses'])->name('services.training-courses');
Route::get('/tutorials', [PageController::class, 'tutorials'])->name('tutorials');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/tools', [PageController::class, 'tools'])->name('tools');
