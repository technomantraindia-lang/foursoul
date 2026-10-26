<?php

declare(strict_types=1);

use App\Http\Controllers\Account\AccountStatusController;
use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\NotificationCenterController;
use App\Http\Controllers\Account\PreferencesController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Cms\BlogController;
use App\Http\Controllers\Cms\PageController;
use App\Http\Controllers\Commerce\CheckoutController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Learn\CourseLearningController;
use App\Http\Controllers\Learn\LearningDashboardController;
use App\Http\Controllers\Learn\LessonPlayerController;
use App\Http\Controllers\Learn\AssignmentController;
use App\Http\Controllers\Learn\CertificateController;
use App\Http\Controllers\Learn\LessonProgressController;
use App\Http\Controllers\Learn\LiveSessionController;
use App\Http\Controllers\Learn\QuizController;
use App\Http\Controllers\PublicCourseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Prompt 2A (User Authentication + User Account UI)
|--------------------------------------------------------------------------
|
| Guest auth routes, email verification, and the authenticated user
| account area. Every authenticated route runs through the shared
| 'auth' + 'account.access' middleware so UserStatus is enforced
| centrally (§9) — never per-controller.
|
*/

Route::get('/', static function () {
    if (file_exists(public_path('index.html'))) {
        return response()->file(public_path('index.html'));
    }
    return redirect('/admin');
})->name('home');

// Legal documents — linked from the registration consent checkboxes
// (public: reachable by guests and authenticated users alike).
Route::view('/terms', 'legal.terms')->name('legal.terms');
Route::view('/privacy', 'legal.privacy')->name('legal.privacy');

Route::get('/courses', static function () {
    if (file_exists(public_path('index.html'))) {
        return response()->file(public_path('index.html'));
    }
    return redirect('/admin');
})->name('courses.index');

Route::get('/plans', static function () {
    if (file_exists(public_path('index.html'))) {
        return response()->file(public_path('index.html'));
    }
    return redirect('/admin');
})->name('plans.index');

Route::get('/membership', static function () {
    if (file_exists(public_path('index.html'))) {
        return response()->file(public_path('index.html'));
    }
    return redirect('/admin');
})->name('membership.index');

Route::get('/pricing', static function () {
    if (file_exists(public_path('index.html'))) {
        return response()->file(public_path('index.html'));
    }
    return redirect('/admin');
})->name('pricing.index');

/*
|--------------------------------------------------------------------------
| Guest authentication
|--------------------------------------------------------------------------
*/

// Registration (§4–6)
Route::middleware('guest')->group(static function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->name('register.store');

    // Login (§7–8)
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');

    // Forgot password (§11)
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Reset password (§12)
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});

// Logout available to any authenticated user (also allowed for suspended
// users by the account.access middleware).
Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Email verification (§13)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(static function (): void {
    Route::get('/verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Authenticated user area (§9, §15–23)
|--------------------------------------------------------------------------
|
| 'account.access' enforces UserStatus centrally: active proceeds,
| suspended is confined to the status page, blocked/closed are logged out.
|
*/

Route::middleware(['auth', 'account.access'])->group(static function (): void {
    // Dashboard (§15)
    Route::get('/dashboard', static function () {
        return redirect('/admin');
    })->name('dashboard');

    // Read-only account-status screen for suspended accounts (§10).
    Route::get('/account/status', AccountStatusController::class)->name('account.status');
    Route::get('/account/subscription', [SubscriptionController::class, 'show'])->name('subscription.show');
    Route::get('/subscription/renew', [RenewalController::class, 'show'])->name('subscription.renew');
    Route::post('/subscription/renew', [RenewalController::class, 'renew'])->name('subscription.renew.store');

    // Profile (§17–18)
    Route::get('/account/profile', [ProfileController::class, 'edit'])->name('account.profile.edit');
    Route::put('/account/profile', [ProfileController::class, 'update'])->name('account.profile.update');
    Route::post('/account/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('account.profile.avatar');

    // Security — change password (§19)
    Route::get('/account/security', [SecurityController::class, 'edit'])->name('account.security.edit');
    Route::put('/account/security/password', [SecurityController::class, 'updatePassword'])->name('account.security.password');

    // Preferences (§20–21)
    Route::get('/account/preferences', [PreferencesController::class, 'edit'])->name('account.preferences.edit');
    Route::put('/account/preferences', [PreferencesController::class, 'update'])->name('account.preferences.update');

    // Phase 8 — Notification Center (in-app inbox)
    Route::get('/account/notifications', [NotificationCenterController::class, 'index'])
        ->name('account.notifications.index');
    Route::post('/account/notifications/{notification}/read', [NotificationCenterController::class, 'markRead'])
        ->name('account.notifications.read');
    Route::post('/account/notifications/read-all', [NotificationCenterController::class, 'markAllRead'])
        ->name('account.notifications.read-all');

    /*
    |--------------------------------------------------------------------------
    | LMS learning area (Prompt 3 — Player + Progress)
    |--------------------------------------------------------------------------
    |
    | All paid lesson access is verified server-side via LessonReleaseService;
    | request-supplied ids are never trusted as proof of ownership.
    |
    */

    Route::get('/learn', LearningDashboardController::class)->name('learn.index');

    Route::get('/learn/courses/{course:slug}', [CourseLearningController::class, 'show'])
        ->name('learn.course');

    Route::get('/learn/courses/{course:slug}/lessons/{lesson:uuid}', [LessonPlayerController::class, 'show'])
        ->name('learn.lesson');

    Route::post('/learn/courses/{course:slug}/lessons/{lesson:uuid}/progress', [LessonProgressController::class, 'progress'])
        ->middleware('throttle:120,1')
        ->name('learn.lesson.progress');

    Route::post('/learn/courses/{course:slug}/lessons/{lesson:uuid}/complete', [LessonProgressController::class, 'complete'])
        ->middleware('throttle:60,1')
        ->name('learn.lesson.complete');

    /*
    |--------------------------------------------------------------------------
    | Commerce — checkout (Prompt 5)
    |--------------------------------------------------------------------------
    |
    | Totals are always recalculated server-side; frontend values are never
    | trusted. Payment initiation is gateway-independent.
    |
    */

    Route::post('/checkout/preview', [CheckoutController::class, 'preview'])
        ->name('checkout.preview');

    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->name('checkout.store');

    Route::get('/orders/{order:uuid}', [CheckoutController::class, 'show'])
        ->name('orders.show');

    /*
    |--------------------------------------------------------------------------
    | Assessment engine (Prompt 6 — Quizzes + Assignments + Certificates)
    |--------------------------------------------------------------------------
    |
    | Access is authorized server-side via QuizPolicy / AssignmentPolicy /
    | CertificateService eligibility; request ids are never trusted.
    |
    */

    // Quizzes
    Route::get('/learn/courses/{course:slug}/quizzes/{quiz:uuid}', [QuizController::class, 'show'])
        ->name('learn.quiz.show');
    Route::post('/learn/courses/{course:slug}/quizzes/{quiz:uuid}/start', [QuizController::class, 'start'])
        ->middleware('throttle:30,1')
        ->name('learn.quiz.start');
    Route::get('/learn/courses/{course:slug}/quizzes/{quiz:uuid}/attempts/{attemptUuid}', [QuizController::class, 'take'])
        ->name('learn.quiz.take');
    Route::post('/learn/courses/{course:slug}/quizzes/{quiz:uuid}/attempts/{attemptUuid}', [QuizController::class, 'submit'])
        ->middleware('throttle:60,1')
        ->name('learn.quiz.submit');
    Route::get('/learn/courses/{course:slug}/quizzes/{quiz:uuid}/attempts/{attemptUuid}/result', [QuizController::class, 'result'])
        ->name('learn.quiz.result');

    // Assignments
    Route::get('/learn/courses/{course:slug}/assignments/{assignment:uuid}', [AssignmentController::class, 'show'])
        ->name('learn.assignment.show');
    Route::post('/learn/courses/{course:slug}/assignments/{assignment:uuid}', [AssignmentController::class, 'submit'])
        ->middleware('throttle:20,1')
        ->name('learn.assignment.submit');

    // Live sessions (Phase 7 — Live Learning Engine)
    Route::get('/learn/live-sessions', [LiveSessionController::class, 'index'])
        ->name('learn.live-sessions.index');
    Route::get('/learn/live-sessions/{session:uuid}', [LiveSessionController::class, 'show'])
        ->name('learn.live-sessions.show');
    Route::post('/learn/live-sessions/{session:uuid}/join', [LiveSessionController::class, 'join'])
        ->middleware('throttle:30,1')
        ->name('learn.live-sessions.join');
    Route::post('/learn/live-sessions/{session:uuid}/leave', [LiveSessionController::class, 'leave'])
        ->name('learn.live-sessions.leave');

    // Certificates
    Route::get('/certificates', [CertificateController::class, 'index'])
        ->name('certificates.index');
    Route::get('/learn/courses/{course:slug}/certificates/{certificate:uuid}', [CertificateController::class, 'show'])
        ->name('certificates.show');
});

// Public certificate verification (no auth required)
Route::get('/verify/certificates/{code}', [\App\Http\Controllers\Learn\CertificateController::class, 'verify'])
    ->name('certificates.verify');

/*
|--------------------------------------------------------------------------
| Public free preview (separate from the authenticated player)
|--------------------------------------------------------------------------
*/

Route::get('/courses/{course:slug}/preview/{lesson:uuid}', [\App\Http\Controllers\Learn\PublicPreviewController::class, 'show'])
    ->name('courses.preview');

/*
|--------------------------------------------------------------------------
| CMS — public website (Prompt 9)
|--------------------------------------------------------------------------
|
| Published pages and blog posts are rendered publicly. Drafts and
| scheduled content stay hidden; slugs resolve server-side only.
|
*/

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/pages/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('cms.page.prefixed');

// Static/published CMS pages — must be last so it never shadows /blog etc.
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('cms.page.show');

Route::fallback(static function () {
    if (file_exists(public_path('index.html'))) {
        return response()->file(public_path('index.html'));
    }
    abort(404);
});
