<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'uuid',
        'certificate_number',
        'verification_hash',
        'certificate_template_id',
        'user_id',
        'course_id',
        'enrollment_id',
        'status',
        'issued_on',
        'student_name_snapshot',
        'course_name_snapshot',
        'instructor_name_snapshot',
        'pdf_path',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'revoked_at' => 'datetime',
            'status' => \App\Enums\CertificateStatus::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }
}