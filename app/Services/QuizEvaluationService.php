<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\DB;

class QuizEvaluationService
{
    /**
     * Evaluate all answers of an attempt.
     *
     * @return array{score: float, percentage: float, passed: bool, needs_manual_review: bool}
     */
    public function evaluate(QuizAttempt $attempt): array
    {
        $quiz = $attempt->quiz;
        $answers = QuizAnswer::query()
            ->where('quiz_attempt_id', $attempt->id)
            ->with('question.options')
            ->get();

        $score = 0.0;
        $totalMarks = 0.0;
        $needsManualReview = false;

        foreach ($answers as $answer) {
            $question = $answer->question;
            if ($question === null) {
                continue;
            }

            $marks = $this->marksFor($quiz, $question);
            $totalMarks += (float) $marks;

            $result = $this->evaluateAnswer($question, $answer, $marks);

            $answer->is_correct = $result['correct'];
            $answer->awarded_marks = $result['awarded'];
            $answer->needs_manual_review = $result['manual'];
            $answer->save();

            $score += (float) $result['awarded'];

            if ($result['manual']) {
                $needsManualReview = true;
            }
        }

        // Use quiz total_marks when configured, otherwise computed question marks.
        $denominator = (float) $quiz->total_marks > 0
            ? (float) $quiz->total_marks
            : max($totalMarks, 0.0001);

        $percentage = round(($score / $denominator) * 100, 2);
        $passed = ! $needsManualReview && $percentage >= (float) $quiz->passing_marks;

        return [
            'score' => round($score, 2),
            'percentage' => $percentage,
            'passed' => $passed,
            'needs_manual_review' => $needsManualReview,
        ];
    }

    /**
     * Review a single manually-reviewed answer.
     */
    public function reviewAnswer(QuizAnswer $answer, bool $isCorrect, ?float $awardedMarks = null, ?string $feedback = null): QuizAnswer
    {
        $question = $answer->question;

        $answer->update([
            'is_correct' => $isCorrect,
            'awarded_marks' => $awardedMarks ?? ($isCorrect ? (float) $question?->marks : 0),
            'needs_manual_review' => false,
            'reviewer_feedback' => $feedback,
            'reviewed_at' => now(),
        ]);

        return $answer;
    }

    private function evaluateAnswer($question, QuizAnswer $answer, $marks): array
    {
        $type = $question->type;
        $given = $answer->answer ?? [];

        return match ($type) {
            QuestionType::SingleChoice, QuestionType::TrueFalse => $this->evaluateSingleChoice($question, $given, $marks),
            QuestionType::MultipleChoice => $this->evaluateMultipleChoice($question, $given, $marks),
            QuestionType::Numeric => $this->evaluateNumeric($question, $given, $marks),
            QuestionType::ShortAnswer => $this->evaluateShortAnswer($question, $given, $marks),
            QuestionType::Paragraph, QuestionType::FileUpload => [
                'correct' => false,
                'awarded' => 0,
                'manual' => true,
            ],
        };
    }

    private function evaluateSingleChoice($question, array $given, $marks): array
    {
        $selectedId = isset($given[0]) ? (int) $given[0] : null;
        $correctOption = $question->options->firstWhere('is_correct', true);

        $correct = $correctOption !== null && $selectedId === (int) $correctOption->id;

        return [
            'correct' => $correct,
            'awarded' => $correct ? (float) $marks : -abs((float) $question->negative_marks),
            'manual' => false,
        ];
    }

    private function evaluateMultipleChoice($question, array $given, $marks): array
    {
        $selected = collect($given)->map(fn ($v) => (int) $v)->sort()->values();
        $correctIds = $question->options->where('is_correct', true)->pluck('id')->map(fn ($v) => (int) $v)->sort()->values();

        $exactMatch = $selected->count() === $correctIds->count()
            && $selected->diff($correctIds)->isEmpty();

        return [
            'correct' => $exactMatch,
            'awarded' => $exactMatch ? (float) $marks : -abs((float) $question->negative_marks),
            'manual' => false,
        ];
    }

    private function evaluateNumeric($question, array $given, $marks): array
    {
        $expected = $question->metadata['numeric_answer'] ?? null;
        $tolerance = (float) ($question->metadata['numeric_tolerance'] ?? 0);

        $givenValue = is_numeric($given[0] ?? null) ? (float) $given[0] : null;

        if ($expected === null || $givenValue === null) {
            return ['correct' => false, 'awarded' => 0, 'manual' => false];
        }

        $correct = abs($givenValue - (float) $expected) <= $tolerance;

        return [
            'correct' => $correct,
            'awarded' => $correct ? (float) $marks : -abs((float) $question->negative_marks),
            'manual' => false,
        ];
    }

    private function evaluateShortAnswer($question, array $given, $marks): array
    {
        $accepted = collect($question->metadata['accepted_answers'] ?? [])
            ->map(fn ($v) => mb_strtolower(trim((string) $v)));

        $text = mb_strtolower(trim((string) ($given[0] ?? '')));

        if ($accepted->isEmpty() || $text === '') {
            return ['correct' => false, 'awarded' => 0, 'manual' => true];
        }

        $correct = $accepted->contains($text);

        return [
            'correct' => $correct,
            'awarded' => $correct ? (float) $marks : -abs((float) $question->negative_marks),
            'manual' => false,
        ];
    }

    private function marksFor($quiz, $question): float
    {
        $override = $quiz->questions
            ->firstWhere('id', $question->id)
            ?->pivot?->marks_override;

        return (float) ($override ?? $question->marks);
    }
}