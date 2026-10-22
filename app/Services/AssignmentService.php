<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubmissionStatus;
use App\Models\Admin;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentService
{
    /**
     * Submit (or resubmit) an assignment for a user.
     *
     * @param  array{submission_text?: ?string, file_path?: ?string, original_filename?: ?string, file_size_bytes?: ?int}  $data
     */
    public function submit(Assignment $assignment, User $user, array $data): AssignmentSubmission
    {
        if ($assignment->status->value !== 'published') {
            throw ValidationException::withMessages([
                'assignment' => 'This assignment is not open for submissions.',
            ]);
        }

        $usedAttempts = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->count();

        $last = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->orderByDesc('attempt_number')
            ->first();

        // A graded submission blocks further attempts unless resubmission is allowed.
        if ($last && in_array($last->status, [SubmissionStatus::Graded, SubmissionStatus::Returned], true) && ! $assignment->allow_resubmission) {
            throw ValidationException::withMessages([
                'assignment' => 'You have already submitted this assignment.',
            ]);
        }

        if ($assignment->max_attempts > 0 && $usedAttempts >= $assignment->max_attempts) {
            throw ValidationException::withMessages([
                'assignment' => 'Maximum submission attempts reached.',
            ]);
        }

        return DB::transaction(function () use ($assignment, $user, $data, $usedAttempts): AssignmentSubmission {
            return AssignmentSubmission::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
                'enrollment_id' => $this->resolveEnrollmentId($assignment, $user),
                'attempt_number' => $usedAttempts + 1,
                'status' => SubmissionStatus::Submitted,
                'file_path' => $data['file_path'] ?? null,
                'original_filename' => $data['original_filename'] ?? null,
                'file_size_bytes' => $data['file_size_bytes'] ?? null,
                'submission_text' => $data['submission_text'] ?? null,
                'submitted_at' => now(),
            ]);
        });
    }

    /**
     * Validate an uploaded file against the assignment rules.
     *
     * @param  array{name?: string, size?: int}  $file
     */
    public function validateFile(Assignment $assignment, array $file): void
    {
        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

        $accepted = collect($assignment->accepted_file_types ?? [])
            ->map(fn ($t) => strtolower(trim((string) $t)));

        if ($accepted->isNotEmpty() && ! $accepted->contains($extension)) {
            throw ValidationException::withMessages([
                'file' => "File type '{$extension}' is not accepted. Allowed: ".$accepted->implode(', ').'.',
            ]);
        }

        $maxBytes = ((int) $assignment->max_file_size_kb) * 1024;

        if (($file['size'] ?? 0) > $maxBytes) {
            throw ValidationException::withMessages([
                'file' => 'File exceeds the maximum allowed size of '.$assignment->max_file_size_kb.' KB.',
            ]);
        }
    }

    /**
     * Teacher/admin review of a submission.
     */
    public function review(AssignmentSubmission $submission, Admin $reviewer, float $marksAwarded, ?string $feedback = null): AssignmentSubmission
    {
        $assignment = $submission->assignment;

        if ($marksAwarded < 0 || $marksAwarded > (float) $assignment->max_marks) {
            throw ValidationException::withMessages([
                'marks_awarded' => 'Marks must be between 0 and '.$assignment->max_marks.'.',
            ]);
        }

        $submission->update([
            'marks_awarded' => $marksAwarded,
            'feedback' => $feedback,
            'status' => SubmissionStatus::Graded,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        return $submission;
    }

    /**
     * Return a submission to the student for revision.
     */
    public function returnForRevision(AssignmentSubmission $submission, Admin $reviewer, string $feedback): AssignmentSubmission
    {
        $submission->update([
            'status' => SubmissionStatus::Returned,
            'feedback' => $feedback,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        return $submission;
    }

    /**
     * Whether the user has completed this assignment (graded with passing marks).
     */
    public function isCompleted(Assignment $assignment, User $user): bool
    {
        return AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->where('status', SubmissionStatus::Graded)
            ->exists();
    }

    public function latestSubmission(User $user, Assignment $assignment): ?AssignmentSubmission
    {
        return AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
    }

    public function attemptsUsed(User $user, Assignment $assignment): int
    {
        return AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->count();
    }

    private function resolveEnrollmentId(Assignment $assignment, User $user): ?int
    {
        return $user->enrollments()
            ->where('course_id', $assignment->course_id)
            ->value('id');
    }
}