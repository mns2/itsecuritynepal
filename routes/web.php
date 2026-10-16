<?php

use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

/*
| Public website. URLs mirror the live itsecuritynepal.com (trailing slashes, legacy slugs) to keep SEO equity.
| Trailing slashes are enforced by App\Http\Middleware\TrailingSlash.
*/

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/course', [SiteController::class, 'courses'])->name('courses');
Route::get('/course-finder', [SiteController::class, 'courseFinder'])->name('course-finder');
Route::get('/group-training', [SiteController::class, 'groupTraining'])->name('group-training');
Route::get('/corporate-training', [SiteController::class, 'corporate'])->name('corporate');
Route::get('/mode-of-training', [SiteController::class, 'trainingMethods'])->name('training-methods');
Route::get('/about', [SiteController::class, 'about'])->name('about');
Route::get('/instructors', [SiteController::class, 'instructors'])->name('instructors');
Route::get('/instructors/{slug}', [SiteController::class, 'instructor'])->name('instructor');
Route::get('/blog', [SiteController::class, 'blog'])->name('blog');
Route::get('/contact', [SiteController::class, 'contact'])->name('contact');
Route::get('/batches', [SiteController::class, 'batches'])->name('batches');
Route::get('/faq', [SiteController::class, 'faq'])->name('faq');

Route::get('/courses-index.json', [SeoController::class, 'courseIndex'])->name('courses.index-json');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::post('/enquiries', [EnquiryController::class, 'store'])->middleware('throttle:enquiries')->name('enquiries.store');

// Categories, courses, blog posts, training methods and legal pages all live at the root (legacy URLs).
Route::get('/{slug}', [SiteController::class, 'slug'])->where('slug', '[a-z0-9\-]+')->name('slug');
