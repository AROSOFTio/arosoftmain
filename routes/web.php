<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ServicesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');
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
Route::get('/tools', [PageController::class, 'tools'])->name('tools');
