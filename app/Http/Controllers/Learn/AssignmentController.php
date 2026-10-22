<?php

declare(strict_types=1);

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learn\SubmitAssignmentRequest;
use App\Models\Assignment;
use App\Models\Course;
use App\Services\AssignmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    public function __construct(private readonly AssignmentService $assignmentService)
    {
    }

    /**
     * Show an assignment with the user's submission state.
     */
    public function show(Course $course, Assignment $assignment)
    {
        abort_unless((int) $assignment->course_id === (int) $course->id, 404);
        Gate::authorize('submit', $assignment);

        $user = Auth::user();

        return view('learn.assignment', [
            'course' => $course,
            'assignment' => $assignment,
            'submission' => $this->assignmentService->latestSubmission($user, $assignment),
            'attemptsUsed' => $this->assignmentService->attemptsUsed($user, $assignment),
        ]);
    }

    /**
     * Submit (or resubmit) an assignment.
     */
    public function submit(SubmitAssignmentRequest $request, Course $course, Assignment $assignment)
    {
        abort_unless((int) $assignment->course_id === (int) $course->id, 404);
        Gate::authorize('submit', $assignment);

        $file = $request->file('file');

        if ($file !== null) {
            $this->assignmentService->validateFile($assignment, [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);
        }

        try {
            $this->assignmentService->submit($assignment, Auth::user(), [
                'submission_text' => (string) $request->input('content', ''),
                'file_path' => $file?->store('assignments', 'public'),
                'original_filename' => $file?->getClientOriginalName(),
                'file_size_bytes' => $file?->getSize(),
            ]);
        } catch (ValidationException $e) {
            return redirect()
                ->route('learn.assignment.show', [$course, $assignment])
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('learn.assignment.show', [$course, $assignment])
            ->with('success', 'Assignment submitted successfully.');
    }
}
