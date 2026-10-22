<?php

declare(strict_types=1);

use App\Enums\CourseStatus;
use App\Enums\LessonType;
use App\Models\Admin;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makePublishedCourseWithLessons(): array
{
    $admin = Admin::factory()->create();

    $course = Course::factory()->for($admin, 'author')->create([
        'status' => CourseStatus::Published,
        'published_at' => now(),
    ]);

    $section = CourseSection::factory()->for($course)->create(['position' => 1]);

    $free = CourseLesson::factory()->for($section)->create([
        'type' => LessonType::Video,
        'video_url' => 'https://www.youtube.com/watch?v=abc123',
        'is_free_preview' => true,
        'position' => 1,
        'release_type' => 'immediate',
        'requires_previous_completion' => false,
        'completion_requirement' => 'manual',
    ]);

    $paid = CourseLesson::factory()->for($section)->create([
        'type' => LessonType::Video,
        'video_url' => 'https://www.youtube.com/watch?v=def456',
        'is_free_preview' => false,
        'position' => 2,
        'release_type' => 'immediate',
        'requires_previous_completion' => false,
        'completion_requirement' => 'manual',
    ]);

    return [$course, $free, $paid];
}

it('blocks the paid lesson player without access', function () {
    [$course, $free, $paid] = makePublishedCourseWithLessons();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('learn.lesson', [$course, $paid]))
        ->assertForbidden();
});

it('allows the paid lesson player with an active subscription', function () {
    [$course, $free, $paid] = makePublishedCourseWithLessons();
    $user = User::factory()->create();

    $mock = Mockery::mock(SubscriptionService::class);
    $mock->shouldReceive('userHasCourseAccess')->andReturnTrue();
    $this->app->instance(SubscriptionService::class, $mock);

    $this->actingAs($user)
        ->get(route('learn.lesson', [$course, $paid]))
        ->assertOk()
        ->assertSee($paid->title);
});

it('records progress and marks completion server-side', function () {
    [$course, $free, $paid] = makePublishedCourseWithLessons();
    $user = User::factory()->create();

    $mock = Mockery::mock(SubscriptionService::class);
    $mock->shouldReceive('userHasCourseAccess')->andReturnTrue();
    $this->app->instance(SubscriptionService::class, $mock);

    $this->actingAs($user)
        ->postJson(route('learn.lesson.progress', [$course, $paid]), [
            'position_seconds' => 42,
            'duration_seconds' => 300,
        ])
        ->assertOk()
        ->assertJsonPath('status', 'in_progress');

    $this->assertDatabaseHas('lesson_progress', [
        'user_id' => $user->id,
        'lesson_id' => $paid->id,
        'last_position_seconds' => 42,
    ]);

    $this->actingAs($user)
        ->postJson(route('learn.lesson.complete', [$course, $paid]))
        ->assertOk()
        ->assertJsonPath('status', 'completed');

    // Completion survives even if access later expires (no reset endpoint erases it).
    $this->assertDatabaseHas('lesson_progress', [
        'user_id' => $user->id,
        'lesson_id' => $paid->id,
        'status' => 'completed',
    ]);
});

it('rejects negative or absurd progress positions', function () {
    [$course, $free, $paid] = makePublishedCourseWithLessons();
    $user = User::factory()->create();

    $mock = Mockery::mock(SubscriptionService::class);
    $mock->shouldReceive('userHasCourseAccess')->andReturnTrue();
    $this->app->instance(SubscriptionService::class, $mock);

    $this->actingAs($user)
        ->postJson(route('learn.lesson.progress', [$course, $paid]), [
            'position_seconds' => -10,
        ])
        ->assertStatus(422);
});

it('shows the free preview publicly but never the paid lesson', function () {
    [$course, $free, $paid] = makePublishedCourseWithLessons();

    $this->get(route('courses.preview', [$course, $free]))->assertOk();
    $this->get(route('courses.preview', [$course, $paid]))->assertNotFound();
});

it('requires authentication for the learning dashboard', function () {
    $this->get(route('learn.index'))->assertRedirect(route('login'));
});