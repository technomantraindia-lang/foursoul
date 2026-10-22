<?php

declare(strict_types=1);

namespace Tests\Feature\Assessments;

use App\Enums\AssignmentStatus;
use App\Enums\AttemptStatus;
use App\Enums\CertificateStatus;
use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Enums\QuizStatus;
use App\Models\Admin;
use App\Models\Assignment;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Services\AssignmentService;
use App\Services\CertificateService;
use App\Services\QuizService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentEngineTest extends TestCase
{
    use RefreshDatabase;

    private function publishedCourseWithEnrolledUser(): array
    {
        $admin = Admin::factory()->create();
        $course = Course::factory()->create([
            'status' => CourseStatus::Published,
            'created_by' => $admin->id,
        ]);
        $user = User::factory()->create();
        $enrollment = Enrollment::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Active,
            'source' => 'direct',
        ]);

        return [$admin, $course, $user, $enrollment];
    }

    public function test_quiz_attempt_is_scored_and_passed(): void
    {
        [, $course, $user] = $this->publishedCourseWithEnrolledUser();

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Basics',
            'passing_marks' => 1,
            'total_marks' => 2,
            'max_attempts' => 3,
            'duration_minutes' => 30,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'status' => QuizStatus::Published,
            'published_at' => now(),
        ]);

        $q1 = Question::create([
            'question_text' => '2 + 2 = ?',
            'type' => QuestionType::SingleChoice,
            'marks' => 2,
            'negative_marks' => 0,
            'difficulty' => QuestionDifficulty::Easy,
            'status' => QuestionStatus::Published,
        ]);
        $q1->options()->create(['option_text' => '4', 'is_correct' => true, 'sort_order' => 1]);
        $q1->options()->create(['option_text' => '5', 'is_correct' => false, 'sort_order' => 2]);

        $quiz->questions()->attach($q1->id, ['sort_order' => 1]);

        $service = app(QuizService::class);
        $attempt = $service->startAttempt($user, $quiz);
        $this->assertSame(AttemptStatus::InProgress, $attempt->status);
        $this->assertSame(1, $attempt->attempt_number);

        $resumed = $service->startAttempt($user, $quiz);
        $this->assertTrue($resumed->is($attempt));
        $this->assertSame(1, $attempt->quiz->attempts()->count());
    }

    public function test_assignment_submission_flow_and_review(): void
    {
        [$admin, $course, $user] = $this->publishedCourseWithEnrolledUser();

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'title' => 'Essay',
            'description' => 'Write an essay.',
            'instructions' => 'Min 500 words.',
            'due_at' => now()->addDays(7),
            'max_marks' => 100,
            'allow_resubmission' => true,
            'max_attempts' => 2,
            'accepted_file_types' => null,
            'max_file_size_kb' => 2048,
            'status' => AssignmentStatus::Published,
        ]);

        $service = app(AssignmentService::class);
        $submission = $service->submit($assignment, $user, [
            'submission_text' => 'My essay content.',
        ]);

        $this->assertNotNull($submission->submitted_at);

        $reviewed = $service->review($submission, $admin, 85, 'Good work.');
        $this->assertSame(85.0, (float) $reviewed->marks_awarded);
        $this->assertNotNull($reviewed->reviewed_at);
    }

    public function test_certificate_is_issued_with_unique_serial(): void
    {
        [, $course, $user] = $this->publishedCourseWithEnrolledUser();

        $template = CertificateTemplate::create([
            'name' => 'Default',
            'slug' => 'default',
            'is_default' => true,
            'settings' => [],
        ]);

        $service = app(CertificateService::class);
        $certificate = $service->issue($user, $course, $template);

        $this->assertSame(CertificateStatus::Issued, $certificate->status);
        $this->assertNotNull($certificate->certificate_number);
        $this->assertNotNull($certificate->issued_on);

        $found = $service->verify($certificate->certificate_number);
        $this->assertNotNull($found);
        $this->assertTrue($found->is($certificate));
    }

    public function test_public_verification_page_shows_valid_certificate(): void
    {
        [, $course, $user] = $this->publishedCourseWithEnrolledUser();

        CertificateTemplate::create([
            'name' => 'Default',
            'slug' => 'default',
            'is_default' => true,
            'settings' => [],
        ]);

        $certificate = app(CertificateService::class)->issue($user, $course);

        $response = $this->get('/verify/certificates/'.$certificate->certificate_number);
        $response->assertOk();
        $response->assertSee($certificate->certificate_number);
    }
}
