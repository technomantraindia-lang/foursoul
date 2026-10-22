<?php

declare(strict_types=1);

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\LiveSession;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Customer auth is consumed by the React app on port 5173. The web
// middleware keeps the existing Laravel session guard and CSRF protection;
// Vite proxies these requests to this backend during development.
Route::middleware('web')->prefix('auth')->group(function (): void {
    Route::get('/csrf', static fn (): \Illuminate\Http\JsonResponse => response()->json([
        'token' => csrf_token(),
    ]));
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login'])
        ->middleware(['throttle:30,1']);
    Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::get('/me', [\App\Http\Controllers\Api\AuthController::class, 'me'])
        ->middleware('auth');
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])
        ->middleware('auth');
});

Route::middleware('web')->post('/subscriptions/purchase', [\App\Http\Controllers\Api\AuthController::class, 'purchaseSubscription']);

Route::get('/home', static function () {
    return response()->json([
        'stats' => [
            'sessions' => LiveSession::query()->count(),
            'plans' => SubscriptionPlan::query()->active()->count(),
            'faqs' => Faq::query()->published()->count(),
        ],
    ]);
});

Route::get('/faqs', static function () {
    return Faq::query()
        ->published()
        ->get(['id', 'question', 'answer', 'category', 'sort_order'])
        ->map(static fn (Faq $faq): array => [
            'id' => $faq->id,
            'q' => $faq->question,
            'a' => $faq->answer,
            'category' => $faq->category,
        ])
        ->values();
});

Route::middleware('web')->get('/live-sessions', static function (Request $request) {
    LiveSession::clearExpiredMeetingLinks();
    $user = $request->user('web');

    // Determine user tier level
    // 0 = Guest / No active subscription
    // 1 = Begin
    // 2 = Commit
    // 3 = Transform
    // 4 = Sadhana VIP
    $userTierLevel = 0;
    $userPlanSlug = null;

    if ($user) {
        $activeSub = $user->subscriptions()
            ->whereIn('status', [\App\Enums\SubscriptionStatus::Active, \App\Enums\SubscriptionStatus::Trialing])
            ->latest('current_period_end')
            ->first();

        // Hard expiry date check: once expiry date has passed, access is revoked
        if ($activeSub) {
            $now = now();
            $expiry = $activeSub->expires_at ?? $activeSub->current_period_end;
            if ($expiry && $now->gt($expiry)) {
                $activeSub->update(['status' => \App\Enums\SubscriptionStatus::Expired]);
                $activeSub = null;
            }
        }

        if ($activeSub && $activeSub->plan && $activeSub->grantsAccess()) {
            $userPlanSlug = Illuminate\Support\Str::slug((string) $activeSub->plan->slug);
            $userTierLevel = match ($userPlanSlug) {
                'sadhana' => 4,
                'transform' => 3,
                'commit' => 2,
                'begin' => 1,
                default => 1,
            };
        }
    }

    return LiveSession::query()
        ->where('status', 'scheduled')
        ->whereJsonContains('metadata->member_content', true)
        ->latest('scheduled_start_at')
        ->get()
        ->map(static function (LiveSession $session) use ($userTierLevel, $userPlanSlug): array {
            $requiredTier = strtolower((string) ($session->min_plan_tier ?? 'all'));
            $requiredLevel = match ($requiredTier) {
                'sadhana' => 4,
                'transform' => 3,
                'commit' => 2,
                'begin' => 1,
                default => 1, // 'all' requires at least tier 1 (any active member)
            };

            $allowedPlanSlugs = array_values(array_filter(array_map(
                static fn (mixed $slug): string => \Illuminate\Support\Str::slug((string) $slug),
                (array) ($session->allowed_plans ?? []),
            )));
            $allowedPlanNames = SubscriptionPlan::query()
                ->whereIn('slug', $allowedPlanSlugs)
                ->orderBy('sort_order')
                ->pluck('name')
                ->implode(' / ');

            $isLocked = $userTierLevel === 0;
            if (! $isLocked && $requiredTier === 'custom') {
                $isLocked = !in_array($userPlanSlug, $allowedPlanSlugs, true);
            } elseif (! $isLocked) {
                $isLocked = $userTierLevel < $requiredLevel;
            }

            $planBadge = match ($requiredTier) {
                'sadhana' => '👑 Sadhana VIP Only',
                'transform' => '🌳 Transform & Above',
                'commit' => '🌿 Commit & Above',
                'begin' => '🌱 Begin & Above',
                'custom' => '🎯 Specific Plans Only',
                default => '🌟 All Membership Plans',
            };

            return [
                'id' => $session->id,
                'uuid' => $session->uuid,
                'title' => $session->title,
                'description' => $session->description,
                'video_type' => $session->video_type ?? 'live_meeting',
                'provider' => $session->provider?->value ?? 'zoom',
                'min_plan_tier' => $session->min_plan_tier ?? 'all',
                'plan_badge' => $requiredTier === 'custom' && $allowedPlanNames !== '' ? $allowedPlanNames : $planBadge,
                'allowed_plans' => $allowedPlanSlugs,
                'scheduled_start_at' => $session->scheduled_start_at?->toIso8601String(),
                'scheduled_end_at' => $session->scheduled_end_at?->toIso8601String(),
                'status' => $session->status?->value ?? 'scheduled',
                'is_locked' => $isLocked,
                'user_has_access' => !$isLocked,
                'meeting_url' => $isLocked ? null : $session->meeting_url,
                'meeting_password' => $isLocked ? null : $session->meeting_password,
                'video_url' => $isLocked ? null : $session->video_url,
                'video_file_url' => $isLocked ? null : ($session->video_file ? asset('storage/' . $session->video_file) : null),
                'replay_url' => $isLocked ? null : $session->replay_url,
            ];
        })
        ->values();
});

Route::get('/videos', static function (Request $request) {
    return redirect('/api/live-sessions');
});

Route::get('/plans', static function () {
    return SubscriptionPlan::query()
        ->active()
        ->ordered()
        ->get()
        ->map(static function (SubscriptionPlan $plan): array {
            $meta = $plan->metadata ?? [];

            return [
                'id' => $plan->slug,
                'uuid' => $plan->uuid,
                'name' => $plan->name,
                'duration' => $meta['duration'] ?? ($plan->billing_type?->label() ?? '1 MONTH'),
                'price_inr' => $plan->price_inr ? number_format((float) $plan->price_inr) : null,
                'price_aed' => $plan->price_aed ? number_format((float) $plan->price_aed) : null,
                'price_usd' => $plan->price_usd ? number_format((float) $plan->price_usd) : null,
                'strikethrough_inr' => $meta['strikethrough_inr'] ?? null,
                'strikethrough_aed' => $meta['strikethrough_aed'] ?? null,
                'strikethrough_usd' => $meta['strikethrough_usd'] ?? null,
                'per_month_inr' => $meta['per_month_inr'] ?? null,
                'per_month_aed' => $meta['per_month_aed'] ?? null,
                'per_month_usd' => $meta['per_month_usd'] ?? null,
                'savings' => $meta['savings'] ?? null,
                'note' => $plan->short_description ?? ($meta['note'] ?? null),
                'pause_days' => $meta['pause_days'] ?? 0,
                'highlight' => (bool) $plan->is_featured,
                'features' => $meta['features'] ?? [],
            ];
        })
        ->values();
});

Route::get('/subscription-plans', static fn () => redirect('/api/plans'));

Route::get('/blog', static function (Request $request) {
    $limit = $request->has('limit') ? (int) $request->input('limit') : 50;

    $posts = BlogPost::query()
        ->where('status', 'published')
        ->where(function ($q) {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now()->addHours(6));
        })
        ->select([
            'id',
            'slug',
            'title',
            'excerpt',
            'featured_image_path',
            'blog_category_id',
            'author_admin_id',
            'reading_time_minutes',
            'published_at',
        ])
        ->with([
            'category:id,name,slug',
            'author:id,name',
        ])
        ->latest('published_at')
        ->limit($limit)
        ->get()
        ->map(static function (BlogPost $post): array {
            $rawImage = $post->featured_image_path;
            $imageUrl = null;
            if (! empty($rawImage)) {
                if (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')) {
                    $imageUrl = $rawImage;
                } else {
                    $imageUrl = '/storage/' . ltrim($rawImage, '/');
                }
            }

            return [
                'id' => (string) $post->id,
                'slug' => $post->slug,
                'title' => $post->title,
                'excerpt' => $post->excerpt ?: '',
                'image' => $imageUrl,
                'featured_image_path' => $rawImage,
                'category' => $post->category?->name ?? 'Yoga & Living',
                'author' => [
                    'name' => $post->author?->name ?? 'FourSouls Guide',
                    'role' => 'Yoga & Wellness Instructor',
                    'avatar' => null,
                ],
                'reading_time_minutes' => $post->reading_time_minutes ?? 5,
                'readTime' => ($post->reading_time_minutes ?? 5) . ' min read',
                'published_at' => $post->published_at?->toIso8601String(),
                'date' => $post->published_at ? $post->published_at->format('M d, Y') : now()->format('M d, Y'),
            ];
        })
        ->values();

    return response()->json($posts)->header('Cache-Control', 'public, max-age=30');
});

Route::get('/blog/{slug}', static function (string $slug) {
    $post = BlogPost::query()
        ->where('slug', $slug)
        ->where('status', 'published')
        ->with(['category', 'author'])
        ->first();

    if (! $post) {
        return response()->json(['message' => 'Article not found'], 404);
    }

    $rawImage = $post->featured_image_path;
    $imageUrl = null;
    if (! empty($rawImage)) {
        if (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')) {
            $imageUrl = $rawImage;
        } else {
            $imageUrl = '/storage/' . ltrim($rawImage, '/');
        }
    }

    return response()->json([
        'id' => (string) $post->id,
        'slug' => $post->slug,
        'title' => $post->title,
        'excerpt' => $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->content), 160),
        'content' => $post->content,
        'image' => $imageUrl,
        'featured_image_path' => $rawImage,
        'category' => $post->category?->name ?? 'Yoga & Living',
        'author' => [
            'name' => $post->author?->name ?? 'FourSouls Guide',
            'role' => 'Yoga & Wellness Instructor',
            'avatar' => null,
        ],
        'reading_time_minutes' => $post->reading_time_minutes ?? 5,
        'readTime' => ($post->reading_time_minutes ?? 5) . ' min read',
        'published_at' => $post->published_at?->toIso8601String(),
        'date' => $post->published_at ? $post->published_at->format('M d, Y') : now()->format('M d, Y'),
    ]);
});
