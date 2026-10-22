<?php

declare(strict_types=1);

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\LiveSessionStatus;
use App\Enums\SessionProvider;
use App\Enums\SessionType;
use App\Models\Admin;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function liveSessionFixture(array $overrides = []): LiveSession
{
    $admin = Admin::factory()->create();
    $course = Course::factory()->create([
        'status' => CourseStatus::Published,
        'created_by' => $admin->id,
    ]);

    return LiveSession::create(array_merge([
        'uuid' => (string) Str::uuid(),
        'course_id' => $course->id,
        'instructor_id' => $admin->id,
        'title' => 'Live Q&A',
        'session_type' => SessionType::LiveClass->value,
        'provider' => SessionProvider::Zoom->value,
        'meeting_url' => 'https://zoom.example/j/123',
        'timezone' => 'UTC',
        'scheduled_start_at' => now()->addHour(),
        'scheduled_end_at' => now()->addHours(2),
        'waiting_room_enabled' => false,
        'recording_enabled' => true,
        'replay_enabled' => true,
        'status' => LiveSessionStatus::Scheduled->value,
    ], $overrides));
}

it('lists upcoming sessions for enrolled students only', function () {
    $user = User::factory()->create();
    $session = liveSessionFixture();

    Enrollment::create([
        'uuid' => (string) Str::uuid(),
        'user_id' => $user->id,
        'course_id' => $session->course_id,
        'source' => 'subscription',
        'status' => EnrollmentStatus::Active->value,
        'access_expires_at' => now()->addMonth(),
    ]);

    $this->actingAs($user)
        ->get(route('learn.live-sessions.index'))
        ->assertOk()
        ->assertSee($session->title);
});

it('hides the calendar from non-enrolled users', function () {
    $session = liveSessionFixture();

    $this->actingAs(User::factory()->create())
        ->get(route('learn.live-sessions.index'))
        ->assertOk()
        ->assertDontSee($session->title);
});

it('blocks joining without course access', function () {
    $session = liveSessionFixture();

    $this->actingAs(User::factory()->create())
        ->post(route('learn.live-sessions.join', $session))
        ->assertRedirect()
        ->assertSessionHas('flash.error');
});

it('records attendance on join and prevents duplicate joins', function () {
    $user = User::factory()->create();
    $session = liveSessionFixture(['scheduled_start_at' => now(), 'scheduled_end_at' => now()->addHour()]);

    Enrollment::create([
        'uuid' => (string) Str::uuid(),
        'user_id' => $user->id,
        'course_id' => $session->course_id,
        'source' => 'subscription',
        'status' => EnrollmentStatus::Active->value,
        'expires_at' => now()->addMonth(),
    ]);

    $plan = \App\Models\SubscriptionPlan::create([
        'name' => 'Live Access',
        'status' => \App\Enums\PlanStatus::Active,
        'billing_type' => \App\Enums\PlanBillingType::Monthly,
        'course_access_type' => \App\Enums\PlanCourseAccessType::AllCourses,
        'duration_days' => 30,
        'price_aed' => 100,
    ]);
    $subscription = \App\Models\UserSubscription::create([
        'uuid' => (string) Str::uuid(),
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'started_at' => now()->subDay(),
        'expires_at' => now()->addMonth(),
        'currency' => 'AED',
        'unit_price' => 100,
        'total_amount' => 100,
    ]);
    \App\Models\Entitlement::create([
        'uuid' => (string) Str::uuid(),
        'subscription_id' => $subscription->id,
        'user_id' => $user->id,
        'course_id' => $session->course_id,
        'status' => 'active',
        'granted_at' => now(),
        'expires_at' => now()->addMonth(),
    ]);

    $this->actingAs($user)
        ->post(route('learn.live-sessions.join', $session))
        ->assertRedirect($session->meeting_url);

    expect(\App\Models\SessionAttendance::where('user_id', $user->id)->where('live_session_id', $session->id)->count())->toBe(1);

    // Second join must not create a duplicate row.
    $this->actingAs($user)->post(route('learn.live-sessions.join', $session));

    expect(\App\Models\SessionAttendance::where('user_id', $user->id)->where('live_session_id', $session->id)->count())->toBe(1);
});

it('never exposes replay url to users without access', function () {
    $session = liveSessionFixture([
        'status' => LiveSessionStatus::Completed->value,
        'recording_url' => 'https://cdn.example/rec.mp4',
        'actual_start_at' => now()->subHour(),
        'actual_end_at' => now()->subMinutes(30),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('learn.live-sessions.show', $session))
        ->assertOk()
        ->assertDontSee('cdn.example/rec.mp4');
});