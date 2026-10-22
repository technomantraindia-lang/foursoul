<?php

declare(strict_types=1);

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class QuizController extends Controller
{
    public function __construct(private readonly QuizService $quizService)
    {
    }

    /**
     * Show the quiz intro / attempt state for a course quiz.
     */
    public function show(Course $course, Quiz $quiz)
    {
        abort_unless((int) $quiz->course_id === (int) $course->id, 404);
        Gate::authorize('take', $quiz);

        $user = Auth::user();
        $attempt = $this->quizService->activeAttempt($user, $quiz);
        $attemptsUsed = $quiz->attempts()->where('user_id', $user->id)->count();
        $canAttempt = $this->quizService->canStartAttempt($user, $quiz);

        return view('learn.quiz', [
            'course' => $course,
            'quiz' => $quiz->load(['questions.options']),
            'attempt' => $attempt,
            'attemptsUsed' => $attemptsUsed,
            'canAttempt' => $canAttempt,
        ]);
    }

    /**
     * Start (or resume) an attempt and render the taking view.
     */
    public function start(Course $course, Quiz $quiz)
    {
        abort_unless((int) $quiz->course_id === (int) $course->id, 404);
        Gate::authorize('take', $quiz);

        try {
            $attempt = $this->quizService->startAttempt(Auth::user(), $quiz);
        } catch (ValidationException $e) {
            return redirect()
                ->route('learn.course', $course)
                ->withErrors($e->errors());
        }

        return redirect()->route('learn.quiz.take', [$course, $quiz, $attempt]);
    }

    /**
     * Render the active attempt for answering.
     */
    public function take(Course $course, Quiz $quiz, string $attemptUuid)
    {
        abort_unless((int) $quiz->course_id === (int) $course->id, 404);
        Gate::authorize('take', $quiz);

        $attempt = $quiz->attempts()
            ->where('uuid', $attemptUuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_unless($attempt->status->isAnswerable(), 403, 'This attempt is no longer answerable.');

        return view('learn.quiz-take', [
            'course' => $course,
            'quiz' => $quiz->load(['questions.options']),
            'attempt' => $attempt,
        ]);
    }

    /**
     * Submit answers for evaluation.
     */
    public function submit(Request $request, Course $course, Quiz $quiz, string $attemptUuid)
    {
        abort_unless((int) $quiz->course_id === (int) $course->id, 404);
        Gate::authorize('take', $quiz);

        $attempt = $quiz->attempts()
            ->where('uuid', $attemptUuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_unless($attempt->status->isAnswerable(), 403, 'This attempt is no longer answerable.');

        $answers = (array) $request->input('answers', []);

        $attempt = $this->quizService->submitAttempt($attempt, $answers);

        if ($attempt->passed && $this->quizService->courseRequirementsMet($course, Auth::user())) {
            app(\App\Services\CertificateService::class)->issueIfEligible(Auth::user(), $course);
        }

        return redirect()
            ->route('learn.quiz.result', [$course, $quiz, $attempt])
            ->with('success', $attempt->passed ? 'You passed this quiz.' : 'Quiz submitted.');
    }

    /**
     * Show the result of a submitted attempt.
     */
    public function result(Course $course, Quiz $quiz, string $attemptUuid)
    {
        abort_unless((int) $quiz->course_id === (int) $course->id, 404);
        $attempt = $quiz->attempts()
            ->where('uuid', $attemptUuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('learn.quiz-result', [
            'course' => $course,
            'quiz' => $quiz,
            'attempt' => $attempt->load('answers.question'),
            'answers' => $attempt->answers,
        ]);
    }
}
