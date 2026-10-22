<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CertificateStatus;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CertificateService
{
    /**
     * Issue a certificate for a user + course (idempotent).
     */
    public function issue(User $user, Course $course, ?CertificateTemplate $template = null): Certificate
    {
        return DB::transaction(function () use ($user, $course, $template): Certificate {
            $existing = Certificate::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($existing) {
                if ($existing->status === CertificateStatus::Revoked) {
                    throw ValidationException::withMessages([
                        'certificate' => 'A revoked certificate exists for this course.',
                    ]);
                }

                return $existing;
            }

            $enrollmentId = $user->enrollments()
                ->where('course_id', $course->id)
                ->value('id');

            $instructor = $course->author()->first();

            return Certificate::create([
                'uuid' => (string) Str::uuid(),
                'certificate_number' => $this->generateCertificateNumber(),
                'verification_hash' => hash('sha256', Str::uuid().(string) now()),
                'certificate_template_id' => $template?->id ?? CertificateTemplate::query()->where('is_default', true)->value('id'),
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enrollment_id' => $enrollmentId,
                'status' => CertificateStatus::Issued,
                'issued_on' => now()->toDateString(),
                'student_name_snapshot' => $user->name,
                'course_name_snapshot' => $course->title,
                'instructor_name_snapshot' => $instructor?->name,
            ]);
        });
    }

    /**
     * Verify a certificate by number. Hash is optional for the public
     * verification page; when provided both values must match.
     */
    public function verify(string $certificateNumber, ?string $verificationHash = null): ?Certificate
    {
        $query = Certificate::query()
            ->where('certificate_number', $certificateNumber)
            ->where('status', CertificateStatus::Issued);

        if ($verificationHash !== null && $verificationHash !== '') {
            $query->where('verification_hash', $verificationHash);
        }

        return $query->first();
    }

    /**
     * Revoke a certificate.
     */
    public function revoke(Certificate $certificate): Certificate
    {
        $certificate->update([
            'status' => CertificateStatus::Revoked,
            'revoked_at' => now(),
        ]);

        return $certificate;
    }

    private function generateCertificateNumber(): string
    {
        do {
            $number = 'FS-'.now()->format('Y').'-'.strtoupper(Str::random(8));
        } while (Certificate::query()->where('certificate_number', $number)->exists());

        return $number;
    }

    public function issueIfEligible(User $user, Course $course): ?Certificate
    {
        try {
            return $this->issue($user, $course);
        } catch (\Throwable) {
            return null;
        }
    }
}