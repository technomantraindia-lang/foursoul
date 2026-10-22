<?php

declare(strict_types=1);

/**
 * One-off local functional QA against the live MySQL app.
 * Not a phpunit test (phpunit.xml uses sqlite :memory:).
 */

use App\Models\Admin;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\LiveSession;
use App\Models\Notification;
use App\Models\Quiz;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AssignmentService;
use App\Services\AttendanceService;
use App\Services\Commerce\CheckoutService;
use App\Services\Commerce\PaymentService;
use App\Services\Communication\Contracts\PushProviderInterface;
use App\Services\Communication\Contracts\WhatsAppProviderInterface;
use App\Services\Communication\ReminderService;
use App\Services\LiveSessionService;
use App\Services\ProgressService;
use App\Services\QuizService;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$http = $app->make(HttpKernel::class);

$results = [];

$hit = static function (string $method, string $uri, $user = null, string $guard = 'web') use ($http, &$results): int {
    Auth::guard('web')->forgetUser();
    Auth::guard('admin')->forgetUser();

    if ($user !== null) {
        Auth::shouldUse($guard);
        Auth::guard($guard)->setUser($user);
    }

    $request = Request::create($uri, $method);
    $request->headers->set('Accept', 'text/html,application/json');

    try {
        $response = $http->handle($request);
        $status = $response->getStatusCode();
        $body = (string) $response->getContent();
        $error = '';

        if ($status >= 500) {
            if (preg_match('/<(?:title|h1)[^>]*>(.*?)<\//is', $body, $m)) {
                $error = html_entity_decode(strip_tags($m[1]));
            } else {
                $error = substr(trim(preg_replace('/\s+/', ' ', strip_tags($body))), 0, 240);
            }
        }

        $http->terminate($request, $response);
        $results[] = compact('method', 'uri', 'status', 'error') + ['guard' => $guard];

        return $status;
    } catch (Throwable $e) {
        $results[] = [
            'method' => $method,
            'uri' => $uri,
            'status' => 500,
            'error' => $e->getMessage().' @ '.basename($e->getFile()).':'.$e->getLine(),
            'guard' => $guard,
        ];

        return 500;
    }
};

$ok = static fn (int $status): bool => $status >= 200 && $status < 400;

echo "LOCAL FUNCTIONAL QA\n";

$admin = Admin::query()->where('email', 'admin@foursouls.test')->first();
$student = User::query()->where('email', 'student@foursouls.test')->first();
$locked = User::query()->where('email', 'locked@foursouls.test')->first();
$course = Course::query()->where('slug', 'local-demo-course')->first();
$plan = SubscriptionPlan::query()->where('slug', 'local-demo-plan')->first();
$preview = $course?->sections()->first()?->lessons()->where('is_free_preview', true)->first();
$paid = $course?->sections()->first()?->lessons()->where('is_free_preview', false)->first();
$quiz = $course ? Quiz::query()->where('course_id', $course->id)->first() : null;
$assignment = $course ? Assignment::query()->where('course_id', $course->id)->first() : null;
$session = $course ? LiveSession::query()->where('course_id', $course->id)->first() : null;

echo 'Admin: '.($admin ? 'found' : 'MISSING').PHP_EOL;
echo 'Student: '.($student ? 'found' : 'MISSING').PHP_EOL;
echo 'Course: '.($course?->slug ?? 'MISSING').PHP_EOL;

$guestPages = [
    '/',
    '/login',
    '/register',
    '/forgot-password',
    '/blog',
    '/home',
    '/pages/home',
    '/admin',
    '/admin/login',
];

if ($course && $preview) {
    $guestPages[] = '/courses/'.$course->slug.'/preview/'.$preview->uuid;
}

if ($course && $paid) {
    $guestPages[] = '/blog/local-welcome';
}

foreach ($guestPages as $uri) {
    $hit('GET', $uri);
}

if ($admin) {
    foreach ([
        '/admin',
        '/admin/courses',
        '/admin/pages',
        '/admin/blog-posts',
        '/admin/media',
        '/admin/tenants',
        '/admin/analytics-dashboard',
        '/admin/analytics',
    ] as $uri) {
        $hit('GET', $uri, $admin, 'admin');
    }

    if ($course) {
        $hit('GET', '/admin/courses/'.$course->getKey().'/edit', $admin, 'admin');
    }

    $tenant = \App\Models\Tenant::query()->where('slug', 'foursouls-local')->first();
    if ($tenant) {
        $hit('GET', '/admin/tenants/'.$tenant->uuid.'/edit', $admin, 'admin');
    }
}

if ($student) {
    foreach ([
        '/dashboard',
        '/learn',
        '/account/notifications',
        '/forgot-password',
        '/learn/live-sessions',
        '/certificates',
    ] as $uri) {
        $hit('GET', $uri, $student);
    }

    if ($course) {
        $hit('GET', '/learn/courses/'.$course->slug, $student);
    }
    if ($course && $preview) {
        $hit('GET', '/learn/courses/'.$course->slug.'/lessons/'.$preview->uuid, $student);
    }
    if ($course && $paid) {
        $hit('GET', '/learn/courses/'.$course->slug.'/lessons/'.$paid->uuid, $student);
    }
    if ($course && $quiz) {
        $hit('GET', '/learn/courses/'.$course->slug.'/quizzes/'.$quiz->uuid, $student);
    }
    if ($course && $assignment) {
        $hit('GET', '/learn/courses/'.$course->slug.'/assignments/'.$assignment->uuid, $student);
    }
    if ($session) {
        $hit('GET', '/learn/live-sessions/'.$session->uuid, $student);
    }
}

if ($locked && $course && $paid) {
    $hit('GET', '/learn/courses/'.$course->slug.'/lessons/'.$paid->uuid, $locked);
}

if ($student) {
    $hit('GET', '/admin', $student, 'web');
}

// Service-level flows (avoid CSRF on POSTs)
$serviceResults = [];

try {
    if ($student && $plan) {
        $checkout = app(CheckoutService::class);
        $quote = $checkout->preview($student, [
            'product_type' => 'plan',
            'product_id' => $plan->id,
            'currency' => 'aed',
            'coupon_code' => 'DEMO10',
            'billing' => ['name' => 'Demo Student', 'country' => 'AE'],
        ]);
        $order = $checkout->createOrder($student, [
            'product_type' => 'plan',
            'product_id' => $plan->id,
            'currency' => 'aed',
            'billing' => ['name' => 'Demo Student', 'country' => 'AE'],
        ]);
        $initiated = app(PaymentService::class)->initiate($order, 'cash');
        $payment = $initiated['payment'];
        $fulfilled = app(PaymentService::class)->handleCallback($payment, ['confirmed' => true, 'reference' => 'LOCAL-QA']);
        $hit('GET', '/orders/'.$order->uuid, $student);
        $serviceResults['checkout'] = 'OK order '.$order->order_number.' payment '.$fulfilled->status->value;
    }
} catch (Throwable $e) {
    $serviceResults['checkout'] = $e->getMessage().' @ '.basename($e->getFile()).':'.$e->getLine();
}

try {
    if ($student && $quiz) {
        $quizService = app(QuizService::class);
        $attempt = $quizService->startAttempt($student, $quiz);
        $correct = $quiz->questions()->with('options')->first()?->options()->where('is_correct', true)->first();
        $submitted = $quizService->submitAttempt($attempt, [
            $quiz->questions()->first()->id => $correct?->id,
        ]);
        $hit('GET', '/learn/courses/'.$course->slug.'/quizzes/'.$quiz->uuid.'/attempts/'.$submitted->uuid.'/result', $student);
        $serviceResults['quiz'] = 'OK passed='.($submitted->passed ? 'yes' : 'no').' score='.$submitted->score;
    }
} catch (Throwable $e) {
    $serviceResults['quiz'] = $e->getMessage().' @ '.basename($e->getFile()).':'.$e->getLine();
}

try {
    if ($student && $assignment) {
        $submission = app(AssignmentService::class)->submit($assignment, $student, [
            'submission_text' => 'Local QA assignment submission.',
        ]);
        $serviceResults['assignment'] = 'OK submission '.$submission->uuid;
    }
} catch (Throwable $e) {
    $serviceResults['assignment'] = $e->getMessage().' @ '.basename($e->getFile()).':'.$e->getLine();
}

try {
    if ($student && $session) {
        $join = app(LiveSessionService::class)->validateJoin($student, $session);
        $serviceResults['live_join_validate'] = json_encode($join);
        if (! empty($join['allowed'])) {
            app(AttendanceService::class)->join($student, $session);
            $serviceResults['live_attendance'] = 'OK';
        }
        app(LiveSessionService::class)->replayFor($student, $session);
        $serviceResults['live_replay'] = 'OK';
    }
} catch (Throwable $e) {
    $serviceResults['live'] = $e->getMessage().' @ '.basename($e->getFile()).':'.$e->getLine();
}

try {
    if ($student && $course && $preview) {
        app(ProgressService::class)->getOrCreateLessonProgress($student, $course, $preview);
        $serviceResults['progress'] = 'OK';
    }
} catch (Throwable $e) {
    $serviceResults['progress'] = $e->getMessage().' @ '.basename($e->getFile()).':'.$e->getLine();
}

try {
    if ($student) {
        $notification = Notification::query()->where('user_id', $student->id)->first();
        if ($notification && $notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }
        app(WhatsAppProviderInterface::class)->send('+971500000000', 'Local QA WhatsApp log');
        app(PushProviderInterface::class)->send('local-token', 'QA', 'Local push log');
        if ($session) {
            app(ReminderService::class)->liveSessionScheduled($session);
        }
        $serviceResults['communication'] = 'OK';
    }
} catch (Throwable $e) {
    $serviceResults['communication'] = $e->getMessage().' @ '.basename($e->getFile()).':'.$e->getLine();
}

echo PHP_EOL.'HTTP RESULTS'.PHP_EOL;
foreach ($results as $row) {
    $mark = ($row['status'] >= 200 && $row['status'] < 400) ? 'PASS' : 'FAIL';
    echo sprintf("%s %s %s -> %s %s\n", $mark, $row['method'], $row['uri'], $row['status'], $row['error']);
}

echo PHP_EOL.'SERVICE RESULTS'.PHP_EOL;
foreach ($serviceResults as $key => $value) {
    echo $key.': '.$value.PHP_EOL;
}

$fails = array_values(array_filter($results, fn ($r) => $r['status'] >= 400));
echo PHP_EOL.'HTTP FAIL COUNT: '.count($fails).PHP_EOL;
