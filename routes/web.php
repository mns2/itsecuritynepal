<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\ExamBookingController;
use App\Http\Controllers\NewsletterController;
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
Route::get('/career-pathways', [SiteController::class, 'careerPathways'])->name('career-pathways');
Route::get('/career-pathways/{slug}', [SiteController::class, 'careerPathway'])->where('slug', '[a-z0-9\-]+')->name('career-pathway');

Route::get('/courses-index.json', [SeoController::class, 'courseIndex'])->name('courses.index-json');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');
Route::get('/feed', [SeoController::class, 'feed'])->name('feed');

Route::post('/enquiries', [EnquiryController::class, 'store'])->middleware('throttle:enquiries')->name('enquiries.store');

// Red Hat exam booking: public page + form (payment proof upload), confirmation, staff-only proof viewer
Route::get('/red-hat-exam-booking-nepal', [ExamBookingController::class, 'show'])->name('exam-booking');
Route::post('/red-hat-exam-booking-nepal', [ExamBookingController::class, 'store'])->middleware('throttle:exam-bookings')->name('exam-booking.store');
Route::get('/red-hat-exam-booking-nepal/submitted', [ExamBookingController::class, 'submitted'])->name('exam-booking.submitted');
Route::get('/admin/exam-bookings/{examBooking}/proof', [ExamBookingController::class, 'proof'])->name('exam-booking.proof');

// Marketing campaigns: landing pages for ads + pop-up view/click counter
Route::get('/offer/{slug}', [CampaignController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('campaigns.show');
Route::post('/campaigns/{campaign}/track', [CampaignController::class, 'track'])->middleware('throttle:60,1')->name('campaigns.track');

// Newsletter / offers list
Route::post('/newsletter', [NewsletterController::class, 'subscribe'])->middleware('throttle:newsletter')->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'showUnsubscribe'])->name('newsletter.unsubscribe');
Route::post('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe.do');
Route::get('/newsletter/click/{emailCampaign}', [NewsletterController::class, 'click'])->name('newsletter.click');

// Categories, courses, blog posts, training methods and legal pages all live at the root (legacy URLs).
Route::get('/{slug}', [SiteController::class, 'slug'])->where('slug', '[a-z0-9\-]+')->name('slug');
