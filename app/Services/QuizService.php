<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttemptStatus;
use App\Enums\QuestionType;
use App\Enums\QuizStatus;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizService
{
    public function __construct(
        private readonly QuizEvaluationService $evaluationService,
    ) {
    }

    /**
     * Validate that the user has valid course access for this quiz.
     */
    public function validateAccess(User $user, Quiz $quiz): Enrollment
    {
        if (! $quiz->isPublished()) {
            throw ValidationException::withMessages([
                'quiz' => 'This quiz is not available.',
            ]);
        }

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->where('status', 'active')
            ->first();

        if ($enrollment === null) {
            throw ValidationException::withMessages([
                'quiz' => 'You do not have active access to this course.',
            ]);
        }

        return $enrollment;
    }

    /**
     * Start a new attempt or resume an in-progress one.
     */
    public function startOrResumeAttempt(User $user, Quiz $quiz): QuizAttempt
    {
        $enrollment = $this->validateAccess($user, $quiz);

        return DB::transaction(function () use ($user, $quiz, $enrollment): QuizAttempt {
            // Resume an existing in-progress attempt.
            $existing = QuizAttempt::query()
                ->where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->where('enrollment_id', $enrollment->id)
                ->whereIn('status', [AttemptStatus::InProgress])
                ->latest('id')
                ->first();

            if ($existing !== null) {
                if ($existing->isExpired()) {
                    $existing->update(['status' => AttemptStatus::Expired]);
                } else {
                    return $existing;
                }
            }

            // Prevent duplicate attempts beyond maximum.
            $usedAttempts = QuizAttempt::query()
                ->where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->where('enrollment_id', $enrollment->id)
                ->whereIn('status', [AttemptStatus::Submitted, AttemptStatus::Completed, AttemptStatus::Expired])
                ->count();

            if ($quiz->max_attempts > 0 && $usedAttempts >= $quiz->max_attempts) {
                throw ValidationException::withMessages([
                    'quiz' => 'You have used all available attempts for this quiz.',
                ]);
            }

            $questions = $this->resolveQuestions($quiz);

            if ($questions->isEmpty()) {
                throw ValidationException::withMessages([
                    'quiz' => 'This quiz has no questions yet.',
                ]);
            }

            /** @var QuizAttempt $attempt */
            $attempt = QuizAttempt::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'attempt_number' => $usedAttempts + 1,
                'status' => AttemptStatus::InProgress,
                'question_order' => $questions->pluck('id')->values()->all(),
                'started_at' => now(),
                'expires_at' => $quiz->duration_minutes > 0
                    ? now()->addMinutes($quiz->duration_minutes)
                    : null,
            ]);

            return $attempt;
        });
    }

    /**
     * Submit an attempt: persist answers, evaluate, score, lock.
     *
     * @param array<int, mixed> $answers question_id => answer payload
     */
    public function submitAttempt(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        if ($attempt->isLocked()) {
            throw ValidationException::withMessages([
                'quiz' => 'This attempt is already locked.',
            ]);
        }

        if ($attempt->isExpired()) {
            $attempt->update(['status' => AttemptStatus::Expired]);

            throw ValidationException::withMessages([
                'quiz' => 'This attempt has expired.',
            ]);
        }

        return DB::transaction(function () use ($attempt, $answers): QuizAttempt {
            $quiz = $attempt->quiz;
            $questionIds = collect($attempt->question_order ?? []);

            foreach ($questionIds as $questionId) {
                $given = $answers[$questionId] ?? null;

                QuizAnswer::updateOrCreate(
                    [
                        'quiz_attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                    ],
                    ['answer' => $this->normalizeAnswer($given)],
                );
            }

            $result = $this->evaluationService->evaluate($attempt);

            $submittedAt = now();
            $timeTaken = $attempt->started_at !== null
                ? max(0, $attempt->started_at->diffInSeconds($submittedAt))
                : 0;

            $needsReview = $result['needs_manual_review'];

            $attempt->update([
                'status' => $needsReview ? AttemptStatus::Submitted : AttemptStatus::Completed,
                'submitted_at' => $submittedAt,
                'completed_at' => $needsReview ? null : $submittedAt,
                'score' => $result['score'],
                'percentage' => $result['percentage'],
                'passed' => $result['passed'],
                'time_taken_seconds' => $timeTaken,
            ]);

            return $attempt->refresh();
        });
    }

    /**
     * Finalize a manually reviewed attempt (called after admin review).
     */
    public function finalizeAfterReview(QuizAttempt $attempt): QuizAttempt
    {
        $pending = QuizAnswer::query()
            ->where('quiz_attempt_id', $attempt->id)
            ->where('needs_manual_review', true)
            ->exists();

        if ($pending) {
            return $attempt;
        }

        $result = $this->evaluationService->evaluate($attempt);

        $attempt->update([
            'status' => AttemptStatus::Completed,
            'completed_at' => now(),
            'score' => $result['score'],
            'percentage' => $result['percentage'],
            'passed' => $result['passed'],
        ]);

        return $attempt->refresh();
    }

    private function resolveQuestions(Quiz $quiz)
    {
            $query = $quiz->questions()->orderBy('quiz_questions.sort_order');

        if ($quiz->random_question_count > 0) {
            $pool = $query->get();

            if ($pool->count() > $quiz->random_question_count) {
                return $pool->random($quiz->random_question_count)->values();
            }

            return $pool;
        }

        if ($quiz->shuffle_questions) {
            return $query->get()->shuffle()->values();
        }

        return $query->get();
    }

    private function normalizeAnswer(mixed $answer): ?array
    {
        if ($answer === null || $answer === '') {
            return null;
        }

        return is_array($answer) ? array_values($answer) : [$answer];
    }

    public function startAttempt(User $user, Quiz $quiz): QuizAttempt
    {
        return $this->startOrResumeAttempt($user, $quiz);
    }

    public function activeAttempt(User $user, Quiz $quiz): ?QuizAttempt
    {
        return QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', AttemptStatus::InProgress)
            ->latest('id')
            ->first();
    }

    public function canStartAttempt(User $user, Quiz $quiz): bool
    {
        try {
            $this->validateAccess($user, $quiz);
        } catch (ValidationException) {
            return false;
        }

        if ($this->activeAttempt($user, $quiz) !== null) {
            return true;
        }

        $used = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->count();

        return $quiz->max_attempts === null || $quiz->max_attempts === 0 || $used < $quiz->max_attempts;
    }

    public function courseRequirementsMet(Course $course, User $user): bool
    {
        return true;
    }
}