<?php

declare(strict_types=1);

namespace Tests\Feature\Courses;

use App\Enums\AdminRole;
use App\Enums\CourseStatus;
use App\Models\Admin;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth as AuthFacade;
use Tests\TestCase;

/**
 * Phase 2: course catalogue domain tests.
 *
 * Tests:
 * - Course lifecycle hooks: uuid/slug generation, unique slugs,
 *   soft-delete slug retirement, published_at stamping, authorship
 * - scopePublished catalogue visibility
 * - Section/lesson positions auto-increment within their parent scope
 * - scopeForCourse ordering
 * - Policies gate the catalogue behind RBAC permissions
 */
class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Course model behaviour
    // -------------------------------------------------------------------------

    public function test_creating_a_course_assigns_uuid_and_slug(): void
    {
        $course = Course::factory()->create(['title' => 'Foundations of Tarot']);

        $this->assertNotNull($course->uuid);
        $this->assertNotSame('', $course->uuid);
        $this->assertSame('foundations-of-tarot', $course->slug);
    }

    public function test_duplicate_titles_get_incremented_slugs(): void
    {
        $first = Course::factory()->create(['title' => 'Numerology Basics']);
        $second = Course::factory()->create(['title' => 'Numerology Basics']);

        $this->assertSame('numerology-basics', $first->slug);
        $this->assertSame('numerology-basics-2', $second->slug);
    }

    public function test_soft_deleted_courses_retire_their_slug(): void
    {
        $retired = Course::factory()->create(['title' => 'Retired Title']);
        $retired->delete();

        $replacement = Course::factory()->create(['title' => 'Retired Title']);

        // Slug uniqueness ignores soft-deleted rows, so the base slug is reusable.
        $this->assertSoftDeleted($retired);
        $this->assertSame('retired-title', $replacement->slug);
    }

    public function test_publishing_stamps_published_at_exactly_once(): void
    {
        $course = Course::factory()->create();

        $this->assertNull($course->published_at);

        $course->update(['status' => CourseStatus::Published]);
        $firstStamp = $course->fresh()->published_at;

        $this->assertNotNull($firstStamp);

        // Later saves never re-stamp the publication date.
        $course->update(['title' => 'Renamed after publish']);
        $course->refresh();

        $this->assertTrue($firstStamp->equalTo($course->published_at));
    }

    public function test_created_by_defaults_to_authenticated_admin(): void
    {
        $admin = Admin::factory()->create();
        AuthFacade::guard('admin')->login($admin);

        $course = Course::factory()->unauthored()->create();

        $this->assertSame($admin->getKey(), $course->created_by);
    }

    public function test_explicit_author_is_preserved(): void
    {
        $author = Admin::factory()->create();

        $course = Course::factory()->create(['created_by' => $author->getKey()]);

        $this->assertSame($author->getKey(), $course->created_by);
    }

    public function test_published_scope_returns_only_published_courses(): void
    {
        Course::factory()->count(2)->published()->create();
        Course::factory()->count(3)->create(); // drafts
        Course::factory()->archived()->create();

        $this->assertSame(2, Course::published()->count());

        Course::published()->each(function (Course $course): void {
            $this->assertSame(CourseStatus::Published, $course->status);
        });
    }

    // -------------------------------------------------------------------------
    // Section behaviour
    // -------------------------------------------------------------------------

    public function test_section_positions_auto_increment_within_their_course(): void
    {
        $courseA = Course::factory()->create();
        $courseB = Course::factory()->create();

        $a1 = CourseSection::factory()->create(['course_id' => $courseA->getKey(), 'position' => null]);
        $b1 = CourseSection::factory()->create(['course_id' => $courseB->getKey(), 'position' => null]);
        $a2 = CourseSection::factory()->create(['course_id' => $courseA->getKey(), 'position' => null]);

        $this->assertSame(1, $a1->position);
        $this->assertSame(1, $b1->position); // independent sequence per course
        $this->assertSame(2, $a2->position);
    }

    public function test_for_course_scope_filters_and_orders_by_position(): void
    {
        $course = Course::factory()->create();
        $other = Course::factory()->create();

        CourseSection::factory()->create(['course_id' => $course->getKey(), 'position' => 3]);
        CourseSection::factory()->create(['course_id' => $course->getKey(), 'position' => 1]);
        CourseSection::factory()->create(['course_id' => $other->getKey(), 'position' => 2]);

        $positions = CourseSection::forCourse($course->getKey())
            ->pluck('position')
            ->all();

        $this->assertSame([1, 3], $positions);
    }

    // -------------------------------------------------------------------------
    // Lesson behaviour
    // -------------------------------------------------------------------------

    public function test_lesson_positions_auto_increment_within_their_section(): void
    {
        $section = CourseSection::factory()->create();

        $first = CourseLesson::factory()->create([
            'course_section_id' => $section->getKey(),
            'position' => null,
        ]);
        $second = CourseLesson::factory()->create([
            'course_section_id' => $section->getKey(),
            'position' => null,
        ]);

        $this->assertSame(1, $first->position);
        $this->assertSame(2, $second->position);
    }

    public function test_free_preview_scope_filters_flagged_lessons(): void
    {
        $section = CourseSection::factory()->create();

        CourseLesson::factory()->create([
            'course_section_id' => $section->getKey(),
            'is_free_preview' => true,
        ]);
        CourseLesson::factory()->create([
            'course_section_id' => $section->getKey(),
            'is_free_preview' => false,
        ]);

        $this->assertSame(1, CourseLesson::freePreview()->count());
    }

    // -------------------------------------------------------------------------
    // Policy / RBAC integration
    // -------------------------------------------------------------------------

    public function test_content_admin_passes_course_catalogue_policies(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = Admin::factory()->create();
        $admin->assignRole(AdminRole::ContentAdmin->value);

        $course = Course::factory()->create();
        $section = CourseSection::factory()->create(['course_id' => $course->getKey()]);
        $lesson = CourseLesson::factory()->create(['course_section_id' => $section->getKey()]);

        $this->assertTrue($admin->can('viewAny', Course::class));
        $this->assertTrue($admin->can('view', $course));
        $this->assertTrue($admin->can('create', Course::class));
        $this->assertTrue($admin->can('update', $course));
        $this->assertTrue($admin->can('delete', $course));

        $this->assertTrue($admin->can('viewAny', CourseSection::class));
        $this->assertTrue($admin->can('update', $section));
        $this->assertTrue($admin->can('reorder', $section));

        $this->assertTrue($admin->can('viewAny', CourseLesson::class));
        $this->assertTrue($admin->can('update', $lesson));
        $this->assertTrue($admin->can('reorder', $lesson));
    }

    public function test_support_admin_cannot_access_course_catalogue(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = Admin::factory()->create();
        $admin->assignRole(AdminRole::SupportAdmin->value);

        $course = Course::factory()->create();
        $section = CourseSection::factory()->create(['course_id' => $course->getKey()]);
        $lesson = CourseLesson::factory()->create(['course_section_id' => $section->getKey()]);

        $this->assertFalse($admin->can('viewAny', Course::class));
        $this->assertFalse($admin->can('update', $course));
        $this->assertFalse($admin->can('update', $section));
        $this->assertFalse($admin->can('update', $lesson));
    }

    public function test_suspended_content_admin_is_denied_by_gate_before(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = Admin::factory()->suspended()->create();
        $admin->assignRole(AdminRole::ContentAdmin->value);

        $course = Course::factory()->create();

        $this->assertFalse($admin->can('viewAny', Course::class));
        $this->assertFalse($admin->can('update', $course));
    }
}